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

namespace mod_ispring\local\session\app\repository;

use mod_ispring\local\session\app\model\session;

interface session_repository_interface {
    /**
     * Add user session to database
     * @param session $data
     * @return int
     */
    public function add(session $data): int;

    /**
     * Update session
     * @param int $id
     * @param session $data
     * @return bool
     */
    public function update(int $id, session $data): bool;

    /**
     * Set suspend data for given session id
     * @param int $id
     * @param string|null $suspenddata
     */
    public function set_suspend_data(int $id, ?string $suspenddata): void;

    /**
     * Delete sessions by content id
     * @param int $contentid
     * @return bool
     */
    public function delete_by_content_id(int $contentid): bool;
}
