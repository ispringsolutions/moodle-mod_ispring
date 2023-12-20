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

namespace mod_ispring\session\app\query;

use mod_ispring\session\app\query\model\session;

interface session_query_service_interface
{
    /**
     * Get last user session by content id
     * @param int $content_id
     * @param int $user_id
     * @return session|null
     */
    public function get_last_by_content_id(int $content_id, int $user_id): ?session;

    /**
     * Get last user session by ispring module id
     * @param int $ispring_module_id
     * @param int $user_id
     * @return session|null
     */
    public function get_last_by_ispring_module_id(int $ispring_module_id, int $user_id): ?session;

    /**
     * Return true if given ispring module has at least one session for specified user id
     * @param int $ispring_module_id
     * @param int $user_id
     * @return bool
     */
    public function ispring_module_has_sessions_with_user_id(int $ispring_module_id, int $user_id): bool;

    /**
     * Get session id
     * @param int $session_id
     * @return session|null
     */
    public function get(int $session_id): ?session;

    /**
     * @param int $ispring_module_id
     * @param int $content_id
     * @param int $user_id
     * @return array
     */
    public function get_grades_for_gradebook(int $ispring_module_id, int $content_id, int $user_id): array;

    /**
     * @param int $session_id
     * @return bool
     */
    public function exist(int $session_id): bool;

    /**
     * Returns true if passing requirements were updated
     * @param array $content_ids
     * @return bool
     */
    public function passing_requirements_were_updated(array $content_ids): bool;

    /**
     * Returns true if passing requirements were updated for given user
     * @param array $content_ids
     * @param int $user_id
     * @return bool
     */
    public function passing_requirements_were_updated_for_user(array $content_ids, int $user_id): bool;
}