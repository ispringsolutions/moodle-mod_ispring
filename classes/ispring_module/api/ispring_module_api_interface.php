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

namespace mod_ispring\ispring_module\api;

use mod_ispring\ispring_module\api\input\create_or_update_ispring_module_input;
use mod_ispring\ispring_module\api\output\ispring_module_output;

interface ispring_module_api_interface
{
    /**
     * @param create_or_update_ispring_module_input $create_ispring_input
     * @return int
     */
    public function create(create_or_update_ispring_module_input $create_ispring_input): int;

    /**
     * @param int $instance
     * @param create_or_update_ispring_module_input $ispring_input
     * @return bool
     */
    public function update(int $instance, create_or_update_ispring_module_input $ispring_input): bool;

    /**
     * @param int $id
     * @return bool true
     */
    public function delete(int $id): bool;

    /**
     * Get activity module record by id
     *
     * @param int $id
     * @return ispring_module_output|null
     */
    public function get_by_id(int $id): ?ispring_module_output;

    /**
     * Check activity module record existence
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * Check module availability
     *
     * @param int $module_id
     * @return bool
     */
    public function is_available(int $module_id): bool;
}