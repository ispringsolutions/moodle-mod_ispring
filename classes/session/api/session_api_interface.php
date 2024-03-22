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

namespace mod_ispring\session\api;

use mod_ispring\session\api\input\end_input;
use mod_ispring\session\api\input\update_input;
use mod_ispring\session\api\output\detailed_report_output;
use mod_ispring\session\api\output\session_output;

interface session_api_interface {
    /**
     * Create new session for given content id and user id
     * @param int $contentid
     * @param int $userid
     * @param string $status
     * @param string $playerid
     * @param bool $sessionrestored
     * @return int
     */
    public function add(int $contentid, int $userid, string $status, string $playerid, bool $sessionrestored): int;

    /**
     * End session and save state
     * @param int $sessionid
     * @param int $userid
     * @param end_input $data
     * @return bool
     */
    public function end(int $sessionid, int $userid, end_input $data): bool;

    /**
     * Update session
     * @param int $sessionid
     * @param int $userid
     * @param update_input $data
     * @return bool
     */
    public function update(int $sessionid, int $userid, update_input $data): bool;

    /**
     * Get last user session by content id
     * @param int $contentid
     * @param int $userid
     * @return session_output|null
     */
    public function get_last_by_content_id(int $contentid, int $userid): ?session_output;

    /**
     * Return true if given ispring module has at least one session for specified user id
     * @param int $ispringmoduleid
     * @param int $userid
     * @return bool
     */
    public function ispring_module_has_sessions_with_user_id(int $ispringmoduleid, int $userid): bool;

    /**
     * Get grades for gradebook
     * @param int $id
     * @param int $userid
     * @return array|null
     */
    public function get_grades_for_gradebook(int $id, int $userid): ?array;

    /**
     * Get session record by id
     * @param int $sessionid
     * @return session_output|null
     */
    public function get_by_id(int $sessionid): ?session_output;

    /**
     * Delete session records by content id
     * @param int $contentid
     * @return bool
     */
    public function delete_by_content_id(int $contentid): bool;

    /**
     * Get detailed report for session
     * @param int $sessionid
     * @return detailed_report_output|null
     */
    public function get_detailed_report(int $sessionid): ?detailed_report_output;

    /**
     * Returns true if passing requirements were updated
     * @param int $ispringmoduleid
     * @return bool
     */
    public function passing_requirements_were_updated(int $ispringmoduleid): bool;

    /**
     * Returns true if passing requirements were updated for given user
     * @param int $ispringmoduleid
     * @param int $userid
     * @return bool
     */
    public function passing_requirements_were_updated_for_user(int $ispringmoduleid, int $userid): bool;
}
