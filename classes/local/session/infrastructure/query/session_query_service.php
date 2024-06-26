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

namespace mod_ispring\local\session\infrastructure\query;

use mod_ispring\local\ispring_module\domain\model\grading_options;
use mod_ispring\local\session\app\adapter\ispring_module_api_interface;
use mod_ispring\local\session\app\query\model\session;
use mod_ispring\local\session\app\query\session_query_service_interface;
use mod_ispring\local\session\domain\model\session_state;

class session_query_service implements session_query_service_interface {
    private \moodle_database $database;
    private ispring_module_api_interface $ispringmoduleapi;

    public function __construct(
        ispring_module_api_interface $ispringmoduleapi
    ) {
        global $DB;
        $this->database = $DB;
        $this->ispringmoduleapi = $ispringmoduleapi;
    }

    public function get_last_by_content_id(int $contentid, int $userid): ?session {
        try {
            $sessions = $this->database->get_records(
                'ispring_session',
                ['ispring_content_id' => $contentid, 'user_id' => $userid],
                'attempt desc',
                '*',
                0,
                1
            );

            if (count($sessions) === 0) {
                return null;
            }

            $session = array_values($sessions)[0];

            return $this->get_session($session);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function get_last_by_ispring_module_id(int $ispringmoduleid, int $userid): ?session {
        try {
            $sessions = $this->database->get_records_sql("
                SELECT iss.*
                  FROM {ispring_content} isc
                  JOIN {ispring_session} iss ON isc.id = iss.ispring_content_id
                 WHERE isc.ispring_id = :ispring_module_id
                       AND iss.user_id = :user_id
              ORDER BY iss.attempt DESC",
                [
                    'ispring_module_id' => $ispringmoduleid,
                    'user_id' => $userid,
                ],
                0,
                1,
            );

            $session = reset($sessions);
            return $session !== false
                ? $this->get_session($session)
                : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function ispring_module_has_sessions_with_user_id(int $ispringmoduleid, int $userid): bool {
        try {
            $sessionexists = $this->database->record_exists_sql("
                SELECT isc.id
                  FROM {ispring_content} isc
                  JOIN {ispring_session} iss ON isc.id = iss.ispring_content_id
                 WHERE isc.ispring_id = :ispring_module_id
                       AND iss.user_id = :user_id",
                [
                    'ispring_module_id' => $ispringmoduleid,
                    'user_id' => $userid,
                ]);
            return $sessionexists;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function get(int $sessionid): ?session {
        try {
            $session = $this->database->get_record('ispring_session', ['id' => $sessionid]);
            if (!$session) {
                return null;
            }

            return $this->get_session($session);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function get_grades_for_gradebook(int $ispringmoduleid, int $contentid, int $userid): array {
        $grademethod = $this->ispringmoduleapi->get_grade_method($ispringmoduleid);

        switch ($grademethod) {
            case grading_options::HIGHEST:
                return $this->get_highest_grade($contentid, $userid);
            case grading_options::AVERAGE:
                return $this->get_average_grade($contentid, $userid);
            case grading_options::FIRST:
                return $this->get_first_grade($contentid, $userid);
            case grading_options::LAST:
                return $this->get_last_grade($contentid, $userid);
            default:
                debugging('Unexpected grading option');
                return [];
        }
    }

    public function exist(int $sessionid): bool {
        return $this->database->record_exists('ispring_session', ['id' => $sessionid]);
    }

    public function passing_requirements_were_updated(array $contentids): bool {
        if (empty($contentids)) {
            return false;
        }

        $params = $contentids;
        $params[] = session_state::INCOMPLETE;
        $records = $this->database->get_records_sql("
            SELECT ROW_NUMBER() OVER () AS i, COUNT(max_score), COUNT(min_score)
              FROM {ispring_session}
             WHERE ispring_content_id IN (" . self::generate_sql_params_template(count($contentids)) . ")
                   AND status != ?
          GROUP BY max_score, min_score;",
            $params
        );
        return count($records) > 1;
    }

    public function passing_requirements_were_updated_for_user(array $contentids, int $userid): bool {
        if (empty($contentids)) {
            return false;
        }

        $requirements = $this->get_current_requirements($contentids);

        $userrequirements = $this->get_current_requirements($contentids, $userid);

        return $requirements && $userrequirements && $requirements != $userrequirements;
    }

    private function get_highest_grade(int $contentid, int $userid): array {
        $params = [$contentid];
        $usercondition = '';
        if ($userid) {
            $usercondition = "AND user_id = $userid";
        }
        $statuscondition = "AND status != '" . session_state::INCOMPLETE . "'";

        $result = $this->database->get_records_sql("
            SELECT c.user_id AS userid,
                   max(c.score) AS rawgrade,
                   min(c.end_time) AS dategraded
              FROM {ispring_session} c
              JOIN (SELECT ispring_content_id,
                           user_id,
                           max(score) AS score
                      FROM {ispring_session}
                     WHERE ispring_content_id = ?
                           $statuscondition
                           $usercondition
                  GROUP BY ispring_content_id, user_id) x
             USING (ispring_content_id, user_id, score)
          GROUP BY userid",
            $params
        );

        return $result;
    }

    private function get_average_grade(int $contentid, int $userid): array {
        $params = [$contentid];
        $usercondition = '';
        if ($userid) {
            $usercondition = "AND user_id = $userid";
        }
        $statuscondition = "AND status != '" . session_state::INCOMPLETE . "'";

        $result = $this->database->get_records_sql("
            SELECT user_id AS userid,
                   avg(score) AS rawgrade
              FROM {ispring_session}
             WHERE ispring_content_id = ?
                   $statuscondition
                   $usercondition
          GROUP BY user_id;",
            $params
        );

        return $result;
    }

    private function get_first_grade(int $contentid, int $userid): array {
        $params = [$contentid];
        $usercondition = '';
        if ($userid) {
            $usercondition = "AND user_id = $userid";
        }
        $statuscondition = "AND status != '" . session_state::INCOMPLETE . "'";

        $result = $this->database->get_records_sql("
            SELECT c.user_id AS userid,
                   c.score AS rawgrade,
                   c.end_time AS dategraded
              FROM {ispring_session} c
              JOIN (SELECT ispring_content_id,
                           user_id,
                           min(attempt) AS attempt
                      FROM {ispring_session}
                     WHERE ispring_content_id = ?
                           $statuscondition
                           $usercondition
                  GROUP BY ispring_content_id, user_id) x
             USING (ispring_content_id, user_id, attempt);",
            $params
        );

        return $result;
    }

    private function get_last_grade(int $contentid, int $userid): array {
        $params = [$contentid];
        $usercondition = '';
        if ($userid) {
            $usercondition = "AND user_id = $userid";
        }
        $statuscondition = "AND status != '" . session_state::INCOMPLETE . "'";

        $result = $this->database->get_records_sql("
            SELECT c.user_id AS userid,
                   c.score AS rawgrade,
                   c.end_time AS dategraded
              FROM {ispring_session} c
              JOIN (SELECT ispring_content_id,
                           user_id,
                           max(attempt) AS attempt
                      FROM {ispring_session}
                     WHERE ispring_content_id = ?
                           $statuscondition
                           $usercondition
                  GROUP BY ispring_content_id, user_id) x
             USING (ispring_content_id, user_id, attempt);",
            $params
        );

        return $result;
    }

    private static function get_session(\stdClass $data): session {
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
            $data->suspend_data,
        );
    }

    private function get_current_requirements(array $contentids, ?int $userid = null): ?\stdClass {
        try {
            $usercondition = '';
            if ($userid) {
                $usercondition = ' AND user_id = ' . $userid;
            }
            $params = $contentids;
            $params[] = session_state::INCOMPLETE;
            $requirements = $this->database->get_records_sql("
                SELECT max_score, min_score
                  FROM {ispring_session}
                 WHERE ispring_content_id IN (" . self::generate_sql_params_template(count($contentids)) . ")
                       AND status != ?
                       $usercondition
              ORDER BY id DESC",
                $params,
                0,
                1
            );
            $requirement = reset($requirements);

            if (!$requirement) {
                return null;
            }

            return $requirement;
        } catch (\Exception $e) {
            return null;
        }
    }

    private static function generate_sql_params_template(int $count): string {
        if ($count <= 0) {
            return '';
        }

        return "?" . str_repeat(', ?', $count - 1);
    }
}
