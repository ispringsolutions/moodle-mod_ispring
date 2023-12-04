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

namespace mod_ispring\session\api;

use mod_ispring\session\api\input\end_input;
use mod_ispring\session\api\input\update_input;
use mod_ispring\session\api\output\detailed_report_output;
use mod_ispring\session\api\output\session_output;

interface session_api_interface
{
    /**
     * Create new session for given content id and user id
     * @param int $content_id
     * @param int $user_id
     * @param string $status
     * @return int
     */
    public function add(int $content_id, int $user_id, string $status): int;

    /**
     * End session and save state
     * @param int $session_id
     * @param int $user_id
     * @param end_input $data
     * @return bool
     */
    public function end(int $session_id, int $user_id, end_input $data): bool;

    /**
     * Update session
     * @param int $session_id
     * @param int $user_id
     * @param update_input $data
     * @return bool
     */
    public function update(int $session_id, int $user_id, update_input $data): bool;

    /**
     * Get last user session by content id
     * @param int $content_id
     * @param int $user_id
     * @return session_output|null
     */
    public function get_last_by_content_id(int $content_id, int $user_id): ?session_output;

    /**
     * Return true if given ispring module has at least one session for specified user id
     * @param int $ispring_module_id
     * @param int $user_id
     * @return bool
     */
    public function ispring_module_has_sessions_with_user_id(int $ispring_module_id, int $user_id): bool;

    /**
     * Get grades for gradebook
     * @param int $id
     * @param int $user_id
     * @return array|null
     */
    public function get_grades_for_gradebook(int $id, int $user_id): ?array;

    /**
     * Get session record by id
     * @param int $session_id
     * @return session_output|null
     */
    public function get_by_id(int $session_id): ?session_output;

    /**
     * Delete session records by content id
     * @param int $content_id
     * @return bool
     */
    public function delete_by_content_id(int $content_id): bool;

    /**
     * Get detailed report for session
     * @param int $session_id
     * @return detailed_report_output|null
     */
    public function get_detailed_report(int $session_id): ?detailed_report_output;
}