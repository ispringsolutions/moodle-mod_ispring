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

namespace mod_ispring\local\content\app\query;

use mod_ispring\local\content\app\query\model\content;

interface content_query_service_interface {
    /**
     * Get last uploaded content to ispring module
     * @param int $ispringmoduleid
     * @return content|null
     */
    public function get_latest_version_content_by_ispring_module_id(int $ispringmoduleid): ?content;

    /**
     * Get content by content id
     * @param int $contentid
     * @return content|null
     */
    public function get_by_id(int $contentid): ?content;

    /**
     * Check ispring record existing
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * Get all content ids for given module id
     * @param int $ispringmoduleid
     * @return int[]
     */
    public function get_ids_by_ispring_module_id(int $ispringmoduleid): array;
}
