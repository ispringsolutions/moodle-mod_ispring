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

namespace mod_ispring\content\app\service;

use mod_ispring\content\app\model\description;
use stored_file;

interface file_storage_interface
{
    /**
     * Before adding content to db it should be unzipped and stored to filestorage in different filearea
     * @param int $target_context_id
     * @param int $target_item_id
     * @param int $user_context_id
     * @param int $user_item_id
     * @return void
     */
    public function unzip_package(
        int $target_context_id,
        int $target_item_id,
        int $user_context_id,
        int $user_item_id
    ): void;

    /**
     * Get description file
     * @param int $context_id
     * @param int $item_id
     * @param string $filename
     * @return stored_file
     */
    public function get_description_file(int $context_id, int $item_id, string $filename = description::FILENAME): stored_file;

    /**
     * Prepare file-content for user to play or view
     * @param int $context_id
     * @param string $filearea
     * @param array $args
     * @param bool $force_download
     * @param array $options
     * @return bool
     */
    public function present_file(
        int $context_id,
        string $filearea,
        array $args,
        bool $force_download,
        array $options = []
    ): bool;

    /**
     * Generate moodle url for specified file
     * @param int $context_id
     * @param int $file_id
     * @param string $filepath
     * @param string $filename
     * @return string
     */
    public function generate_entrypoint_url(int $context_id, int $file_id, string $filepath, string $filename): string;

    /**
     * Check whether module content needs to be updated
     *
     * @param int $target_context_id
     * @param int $user_context_id
     * @param int $user_item_id
     * @return bool
     */
    public function content_needs_updating(int $target_context_id, int $user_context_id, int $user_item_id): bool;

    /**
     * Remove all files matching given context and item ids from ispring areas
     *
     * @param int $context_id
     * @param int|false $item_id If not specified, files with any item_id are removed
     * @return bool true
     */
    public function clear_ispring_areas(int $context_id, $item_id = false): bool;
}