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
 * @copyright   2023 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ispring\session\infrastructure;

use mod_ispring\session\app\model\session;
use mod_ispring\session\app\repository\session_repository_interface;

class session_repository implements session_repository_interface
{
    private readonly \moodle_database $database;

    public function __construct()
    {
        global $DB;
        $this->database = $DB;
    }

    public function add(session $data): int
    {
        $session = self::session_to_std_class($data);

        $transaction = $this->database->start_delegated_transaction();
        try
        {
            $id = $this->database->insert_record('ispring_session', $session);
            $transaction->allow_commit();
            return $id;
        }
        catch (\Exception $e)
        {
            $transaction->rollback($e);
            throw new \RuntimeException('Cannot add session to database');
        }
    }

    public function update(int $id, session $data): bool
    {
        $session = self::session_to_std_class($data);
        $session->id = $id;

        $transaction = $this->database->start_delegated_transaction();
        try
        {
            $result = $this->database->update_record('ispring_session', $session);
            $transaction->allow_commit();
            return $result;
        }
        catch (\Exception $e)
        {
            $transaction->rollback($e);
            throw new \RuntimeException('Cannot update session');
        }
    }

    public function delete_by_content_id(int $content_id): bool
    {
        $transaction = $this->database->start_delegated_transaction();
        try
        {
            $result = $this->database->delete_records('ispring_session', ['ispring_content_id' => $content_id]);
            $transaction->allow_commit();
            return $result;
        }
        catch (\Exception $e)
        {
            $transaction->rollback($e);
            throw new \RuntimeException('Cannot delete session');
        }
    }

    private static function session_to_std_class(session $session): \stdClass
    {
        $result = new \stdClass();

        $result->ispring_content_id = $session->get_content_id();
        $result->score = $session->get_score();
        $result->status = $session->get_status();
        $result->begin_time = $session->get_begin_time();
        $result->end_time = $session->get_end_time();
        $result->duration = $session->get_duration();
        $result->user_id = $session->get_user_id();
        $result->attempt = $session->get_attempt();
        $result->persist_state_id = $session->get_persist_state_id();
        $result->persist_state = $session->get_persist_state();
        $result->max_score = $session->get_max_score();
        $result->min_score = $session->get_min_score();
        $result->passing_score = $session->get_passing_score();
        $result->detailed_report = $session->get_detailed_report();
        $result->player_id = $session->get_player_id();

        return $result;
    }
}