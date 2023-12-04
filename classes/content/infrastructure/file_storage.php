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

namespace mod_ispring\content\infrastructure;

use mod_ispring\content\app\model\description;
use mod_ispring\content\app\service\file_storage_interface;
use moodle_url;
use stored_file;

class file_storage implements file_storage_interface
{
    private const COMPONENT_NAME = 'mod_ispring';
    private const FILEAREA = 'content';
    private const USER_COMPONENT_NAME = 'user';
    private const USER_FILEAREA = 'draft';
    private readonly \file_storage $file_storage;

    public function __construct()
    {
        $this->file_storage = get_file_storage();
    }

    public function unzip_package(
        int $target_context_id,
        int $target_item_id,
        int $user_context_id,
        int $user_item_id,
    ): void
    {
        $files = $this->file_storage->get_area_files(
            $user_context_id,
            self::USER_COMPONENT_NAME,
            self::USER_FILEAREA,
            $user_item_id,
            'id',
            false
        );

        $file = reset($files);
        if (!$file)
        {
            throw new \RuntimeException('No zip file');
        }

        try
        {
            $zip_content = $file->extract_to_storage(
                get_file_packer('application/zip'),
                $target_context_id,
                self::COMPONENT_NAME,
                self::FILEAREA,
                $target_item_id,
                '.'
            );

            if (!$zip_content)
            {
                throw new \RuntimeException('Cannot unzip file');
            }
        }
        catch (\Throwable $e)
        {
            $this->clear_ispring_content_area($target_context_id, $target_item_id);
            throw $e;
        }
    }

    public function get_description_file(int $context_id, int $item_id, string $filename = description::FILENAME): stored_file
    {
        $file = $this->file_storage->get_file(
            $context_id,
            self::COMPONENT_NAME,
            self::FILEAREA,
            $item_id,
            '/',
            $filename
        );

        if (!$file)
        {
            throw new \RuntimeException('No description file');
        }

        return $file;
    }

    public function present_file(int $context_id, array $args): void
    {
        $relative_path = implode('/', $args);
        $full_path = '/'
            . implode('/',
                [
                    $context_id,
                    file_storage::COMPONENT_NAME,
                    file_storage::FILEAREA,
                    $relative_path
                ]);

        $file = $this->file_storage->get_file_by_hash(sha1($full_path));
        if (!$file)
        {
            send_file_not_found();
        }

        send_stored_file($file);
    }

    public function generate_entrypoint_url(int $context_id, int $file_id, string $filepath, string $filename): string
    {
        return moodle_url::make_pluginfile_url(
            $context_id,
            self::COMPONENT_NAME,
            self::FILEAREA,
            $file_id,
            $filepath . '/',
            $filename,
            false
        )->out();
    }

    public function user_draft_area_is_empty(int $user_context_id, int $user_item_id): bool
    {
        return $this->file_storage->is_area_empty(
            $user_context_id,
            self::USER_COMPONENT_NAME,
            self::USER_FILEAREA,
            $user_item_id
        );
    }

    public function clear_ispring_content_area(int $context_id, int|false $item_id = false): bool
    {
        return $this->file_storage->delete_area_files($context_id, self::COMPONENT_NAME, self::FILEAREA, $item_id);
    }
}