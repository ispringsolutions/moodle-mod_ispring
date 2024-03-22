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

namespace mod_ispring\ispring_module\app\repository;

use mod_ispring\ispring_module\app\data\ispring_module_data;

interface ispring_module_repository_interface {
    /**
     * Add ispring cm to database
     * @param ispring_module_data $data
     * @return int
     */
    public function add(ispring_module_data $data): int;

    /**
     * Update ispring cm in database
     * @param int $id
     * @param ispring_module_data $data
     * @return bool true
     */
    public function update(int $id, ispring_module_data $data): bool;

    /**
     * Remove ispring cm from database
     * @param int $id
     * @return bool true
     */
    public function remove(int $id): bool;
}
