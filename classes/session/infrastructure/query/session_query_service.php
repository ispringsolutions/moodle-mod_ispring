<?php

// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 *
 * @package     mod_ispring
 * @copyright   2024 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ispring\session\infrastructure\query;

use mod_ispring\session\app\adapter\ispring_module_api_interface;
use mod_ispring\session\app\query\model\session;
use mod_ispring\session\app\query\session_query_service_interface;
use mod_ispring\ispring_module\domain\model\grading_options;
use mod_ispring\session\domain\model\session_state;

class session_query_service implements session_query_service_interface
{
    private \moodle_database $database;
    private ispring_module_api_interface $ispring_module_api;

    public function __construct(
        ispring_module_api_interface $ispring_module_api
    )
    {
        global $DB;
        $this->database = $DB;
        $this->ispring_module_api = $ispring_module_api;
    }

    public function get_last_by_content_id(int $content_id, int $user_id): ?session
    {
        try
        {
            $sessions = $this->database->get_records(
                'ispring_session',
                ['ispring_content_id' => $content_id, 'user_id' => $user_id],
                'attempt desc',
                '*',
                0,
                1
            );

            if (count($sessions) === 0)
            {
                return null;
            }

            $session = array_values($sessions)[0];

            return $this->get_session($session);
        } catch (\Exception $e)
        {
            return null;
        }
    }

    public function get_last_by_ispring_module_id(int $ispring_module_id, int $user_id): ?session
    {
        try
        {
            $sessions = $this->database->get_records_sql('
                SELECT iss.*
                FROM {ispring_content} isc
                JOIN {ispring_session} iss ON isc.id = iss.ispring_content_id
                WHERE isc.ispring_id = :ispring_module_id AND iss.user_id = :user_id
                ORDER BY iss.attempt DESC',
                [
                    'ispring_module_id' => $ispring_module_id,
                    'user_id' => $user_id,
                ],
                0,
                1,
            );

            $session = reset($sessions);
            return $session !== false
                ? $this->get_session($session)
                : null;
        } catch (\Exception $e)
        {
            return null;
        }
    }

    public function ispring_module_has_sessions_with_user_id(int $ispring_module_id, int $user_id): bool
    {
        try
        {
            $sessions = $this->database->get_records_sql('
                SELECT NULL
                FROM {ispring_content} isc
                JOIN {ispring_session} iss ON isc.id = iss.ispring_content_id
                WHERE isc.ispring_id = :ispring_module_id AND iss.user_id = :user_id',
                [
                    'ispring_module_id' => $ispring_module_id,
                    'user_id' => $user_id,
                ],
                0,
                1,
            );
            return count($sessions) > 0;
        } catch (\Exception $e)
        {
            return false;
        }
    }

    public function get(int $session_id): ?session
    {
        try
        {
            $session = $this->database->get_record('ispring_session', ['id' => $session_id]);
            if (!$session)
            {
                return null;
            }

            return $this->get_session($session);
        } catch (\Exception $e)
        {
            return null;
        }
    }

    public function get_grades_for_gradebook(int $ispring_module_id, int $content_id, int $user_id): array
    {
        $grade_method = $this->ispring_module_api->get_grade_method($ispring_module_id);

        switch ($grade_method)
        {
            case grading_options::HIGHEST:
                return $this->get_highest_grade($content_id, $user_id);
            case grading_options::AVERAGE:
                return $this->get_average_grade($content_id, $user_id);
            case grading_options::FIRST:
                return $this->get_first_grade($content_id, $user_id);
            case grading_options::LAST:
                return $this->get_last_grade($content_id, $user_id);
            default:
                debugging('Unexpected grading option');
                return [];
        }
    }

    public function exist(int $session_id): bool
    {
        return $this->database->record_exists('ispring_session', ['id' => $session_id]);
    }

    public function passing_requirements_were_updated(array $content_ids): bool
    {
        if (empty($content_ids))
        {
            return false;
        }

        $params = $content_ids;
        $params[] = session_state::INCOMPLETE;
        $records = $this->database->get_records_sql("
            SELECT ROW_NUMBER() OVER () AS i, COUNT(max_score), COUNT(min_score)
            FROM {ispring_session}
                WHERE ispring_content_id IN (" . self::generate_sql_params_template(count($content_ids)) . ")
                AND status != ?
            GROUP BY max_score, min_score;",
            $params
        );
        return count($records) > 1;
    }

    public function passing_requirements_were_updated_for_user(array $content_ids, int $user_id): bool
    {
        if (empty($content_ids))
        {
            return false;
        }

        $current_requirements = $this->get_current_requirements($content_ids);

        $current_user_requirements = $this->get_current_requirements($content_ids, $user_id);

        return $current_requirements && $current_user_requirements && $current_requirements != $current_user_requirements;
    }

    private function get_highest_grade(int $content_id, int $user_id): array
    {
        $params = [$content_id];
        $user_condition = '';
        if ($user_id)
        {
            $user_condition = "AND user_id = $user_id";
        }
        $status_condition = "AND status != '" . session_state::INCOMPLETE . "'";

        $result = $this->database->get_records_sql("
            SELECT
                c.user_id AS userid,
                max(c.score) AS rawgrade,
                min(c.end_time) AS dategraded
            FROM {ispring_session} c
            JOIN (
                SELECT
                    ispring_content_id,
                    user_id,
                    max(score) AS score
                FROM {ispring_session}
                WHERE ispring_content_id = ?
                $status_condition
                $user_condition
                GROUP BY ispring_content_id, user_id
            ) x USING (ispring_content_id, user_id, score)
            GROUP BY userid",
            $params
        );

        return $result;
    }

    private function get_average_grade(int $content_id, int $user_id): array
    {
        $params = [$content_id];
        $user_condition = '';
        if ($user_id)
        {
            $user_condition = "AND user_id = $user_id";
        }
        $status_condition = "AND status != '" . session_state::INCOMPLETE . "'";

        $result = $this->database->get_records_sql("
            SELECT
                user_id AS userid,
                avg(score) AS rawgrade
            FROM {ispring_session}
            WHERE
                ispring_content_id = ?
            $status_condition
            $user_condition
            GROUP BY user_id;",
            $params
        );

        return $result;
    }

    private function get_first_grade(int $content_id, int $user_id): array
    {
        $params = [$content_id];
        $user_condition = '';
        if ($user_id)
        {
            $user_condition = "AND user_id = $user_id";
        }
        $status_condition = "AND status != '" . session_state::INCOMPLETE . "'";

        $result = $this->database->get_records_sql("
            SELECT
                c.user_id AS userid,
                c.score AS rawgrade,
                c.end_time AS dategraded
            FROM {ispring_session} c
            JOIN (
                SELECT
                    ispring_content_id,
                    user_id,
                    min(attempt) AS attempt
                FROM {ispring_session}
                WHERE ispring_content_id = ?
                $status_condition
                $user_condition
                GROUP BY ispring_content_id, user_id
            ) x USING (ispring_content_id, user_id, attempt);",
            $params
        );

        return $result;
    }

    private function get_last_grade(int $content_id, int $user_id): array
    {
        $params = [$content_id];
        $user_condition = '';
        if ($user_id)
        {
            $user_condition = "AND user_id = $user_id";
        }
        $status_condition = "AND status != '" . session_state::INCOMPLETE . "'";
        $result = $this->database->get_records_sql("
            SELECT 
                c.user_id AS userid,
                c.score AS rawgrade,
                c.end_time AS dategraded
            FROM {ispring_session} c 
            JOIN (
                SELECT 
                    ispring_content_id, 
                    user_id, 
                    max(attempt) AS attempt 
                FROM {ispring_session} 
                WHERE ispring_content_id = ?
                $status_condition
                $user_condition
                GROUP BY ispring_content_id, user_id
            ) x USING (ispring_content_id, user_id, attempt);",
            $params
        );

        return $result;
    }

    private static function get_session(\stdClass $data): session
    {
        return new session(
            $data->id,
            $data->ispring_content_id,
            $data->score,
            $data->status,
            $data->begin_time,
            $data->end_time,
            $data->duration,
            $data->user_id,
            $data->attempt,
            $data->persist_state_id,
            $data->persist_state,
            $data->max_score,
            $data->min_score,
            $data->passing_score,
            $data->detailed_report,
            $data->player_id ?? '',
        );
    }

    private function get_current_requirements(array $content_ids, ?int $user_id = null): ?\stdClass
    {
        try
        {
            $user_condition = '';
            if ($user_id)
            {
                $user_condition = ' AND user_id = ' . $user_id;
            }
            $params = $content_ids;
            $params[] = session_state::INCOMPLETE;
            $requirements = $this->database->get_records_sql("
                SELECT max_score, min_score
                FROM {ispring_session}
                    WHERE ispring_content_id IN (" . self::generate_sql_params_template(count($content_ids)) . ") 
                    AND status != ?
                    $user_condition
                ORDER BY id DESC",
                $params,
                0,
                1
            );
            $requirement = reset($requirements);

            if (!$requirement)
            {
                return null;
            }

            return $requirement;
        } catch (\Exception $e)
        {
            return null;
        }
    }

    private static function generate_sql_params_template(int $count): string
    {
        if ($count <= 0)
        {
            return '';
        }

        return "?" . str_repeat(', ?', $count - 1);
    }
}