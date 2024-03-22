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

namespace mod_ispring\session\app\query;

use mod_ispring\session\app\query\model\session;

interface session_query_service_interface {
    /**
     * Get last user session by content id
     * @param int $contentid
     * @param int $userid
     * @return session|null
     */
    public function get_last_by_content_id(int $contentid, int $userid): ?session;

    /**
     * Get last user session by ispring module id
     * @param int $ispringmoduleid
     * @param int $userid
     * @return session|null
     */
    public function get_last_by_ispring_module_id(int $ispringmoduleid, int $userid): ?session;

    /**
     * Return true if given ispring module has at least one session for specified user id
     * @param int $ispringmoduleid
     * @param int $userid
     * @return bool
     */
    public function ispring_module_has_sessions_with_user_id(int $ispringmoduleid, int $userid): bool;

    /**
     * Get session id
     * @param int $sessionid
     * @return session|null
     */
    public function get(int $sessionid): ?session;

    /**
     * @param int $ispringmoduleid
     * @param int $contentid
     * @param int $userid
     * @return array
     */
    public function get_grades_for_gradebook(int $ispringmoduleid, int $contentid, int $userid): array;

    /**
     * @param int $sessionid
     * @return bool
     */
    public function exist(int $sessionid): bool;

    /**
     * Returns true if passing requirements were updated
     * @param array $contentids
     * @return bool
     */
    public function passing_requirements_were_updated(array $contentids): bool;

    /**
     * Returns true if passing requirements were updated for given user
     * @param array $contentids
     * @param int $userid
     * @return bool
     */
    public function passing_requirements_were_updated_for_user(array $contentids, int $userid): bool;
}
