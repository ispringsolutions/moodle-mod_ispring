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

namespace mod_ispring\content\api;

use mod_ispring\content\api\input\content_input;
use mod_ispring\content\api\output\content_output;
use mod_ispring\content\api\output\entrypoint_info;

interface content_api_interface
{
    /**
     * Add files to storage and database
     * @param content_input $content_input
     * @return int
     */
    public function add_content(content_input $content_input): int;

    /**
     * Remove record from database and files from storage
     * @param int $module_context_id
     * @param int $content_id
     */
    public function remove(int $module_context_id, int $content_id): void;

    /**
     * Prepare files to send to user
     * @param int $context_id
     * @param string $filearea
     * @param array $args
     * @param bool $force_download
     * @param array $options
     * @return void
     */
    public function present_file(
        int $context_id,
        string $filearea,
        array $args,
        bool $force_download,
        array $options = []
    ): bool;

    /**
     * Get url for the new ispring content entry point
     * @param int $context_id
     * @param int $ispring_module_id
     * @return entrypoint_info|null
     */
    public function get_latest_version_entrypoint_info(int $context_id, int $ispring_module_id): ?entrypoint_info;

    /**
     * Get the latest version content by ispring module id
     * @param int $ispring_module_id
     * @return content_output|null
     */
    public function get_latest_version_content_by_ispring_module_id(int $ispring_module_id): ?content_output;

    /**
     * Get content by content id
     * @param int $content_id
     * @return content_output|null
     */
    public function get_by_id(int $content_id): ?content_output;

    /**
     * Get content ids by ispring module id
     * @param int $ispring_module_id
     * @return int[]
     */
    public function get_ids_by_ispring_module_id(int $ispring_module_id): array;

    /**
     * Check for content availability
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * Get detailed report url
     * @param int $context_id
     * @param int $content_id
     * @return string|null
     */
    public function get_report_url(int $context_id, int $content_id): ?string;
}