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

namespace mod_ispring\content\infrastructure;

use mod_ispring\content\app\model\description;
use mod_ispring\content\app\service\file_storage_interface;
use moodle_url;
use stored_file;

class file_storage implements file_storage_interface {
    public const PACKAGE_FILEAREA = 'package';
    public const COMPONENT_NAME = 'mod_ispring';
    public const PACKAGE_ITEM_ID = 0;
    private const CONTENT_FILEAREA = 'content';
    private const USER_COMPONENT_NAME = 'user';
    private const USER_FILEAREA = 'draft';
    private \file_storage $filestorage;

    public function __construct() {
        $this->filestorage = get_file_storage();
    }

    public function unzip_package(
        int $targetcontextid,
        int $targetitemid,
        int $usercontextid,
        int $useritemid
    ): void {
        $file = $this->get_first_file_in_area(
            $usercontextid,
            self::USER_COMPONENT_NAME,
            self::USER_FILEAREA,
            $useritemid,
        );

        if (!$file) {
            throw new \RuntimeException('No zip file');
        }

        try {
            $zip = $file->extract_to_storage(
                get_file_packer('application/zip'),
                $targetcontextid,
                self::COMPONENT_NAME,
                self::CONTENT_FILEAREA,
                $targetitemid,
                '.'
            );

            if (!$zip) {
                throw new \RuntimeException('Cannot unzip file');
            }

            $this->save_zip_package($targetcontextid, $useritemid);
        } catch (\Throwable $e) {
            $this->clear_ispring_areas($targetcontextid, $targetitemid);
            throw $e;
        }
    }

    public function get_description_file(int $contextid, int $itemid, string $filename = description::FILENAME): stored_file {
        $file = $this->filestorage->get_file(
            $contextid,
            self::COMPONENT_NAME,
            self::CONTENT_FILEAREA,
            $itemid,
            '/',
            $filename
        );

        if (!$file) {
            throw new \RuntimeException('No description file');
        }

        return $file;
    }

    public function present_file(
        int $contextid,
        string $filearea,
        array $args,
        bool $forcedownload,
        array $options = []
    ): bool {
        if ($filearea != self::PACKAGE_FILEAREA && $filearea != self::CONTENT_FILEAREA) {
            send_file_not_found();
        }

        $relativepath = implode('/', $args);
        $fullpath = '/'
            . implode('/',
                [
                    $contextid,
                    self::COMPONENT_NAME,
                    $filearea,
                    $relativepath,
                ]);

        $file = $this->filestorage->get_file_by_hash(sha1($fullpath));
        if (!$file) {
            send_file_not_found();
        }

        send_stored_file($file);
        return true;
    }

    public function generate_entrypoint_url(int $contextid, int $fileid, string $filepath, string $filename): string {
        return moodle_url::make_pluginfile_url(
            $contextid,
            self::COMPONENT_NAME,
            self::CONTENT_FILEAREA,
            $fileid,
            $filepath . '/',
            $filename,
            false
        )->out();
    }

    public function content_needs_updating(int $targetcontextid, int $usercontextid, int $useritemid): bool {
        $userpackage = $this->get_first_file_in_area(
            $usercontextid,
            self::USER_COMPONENT_NAME,
            self::USER_FILEAREA,
            $useritemid,
        );
        if (!$userpackage) {
            return false;
        }

        $currentpackage = $this->get_first_file_in_area(
            $targetcontextid,
            self::COMPONENT_NAME,
            self::PACKAGE_FILEAREA,
            self::PACKAGE_ITEM_ID,
        );
        if (!$currentpackage) {
            return true;
        }
        return $currentpackage->get_contenthash() != $userpackage->get_contenthash();
    }

    public function clear_ispring_areas(int $contextid, $itemid = false): bool {
        return $this->filestorage->delete_area_files(
                $contextid,
                self::COMPONENT_NAME,
                self::CONTENT_FILEAREA,
                $itemid)
            && $this->filestorage->delete_area_files(
                $contextid,
                self::COMPONENT_NAME,
                self::PACKAGE_FILEAREA,
                self::PACKAGE_ITEM_ID);
    }

    private function get_first_file_in_area(
        int $contextid,
        string $component,
        string $filearea,
        int $itemid
    ) {
        $files = $this->filestorage->get_area_files($contextid, $component, $filearea, $itemid, 'id', false);
        return reset($files);
    }

    private function save_zip_package(int $contextid, int $draftitemid): void {
        file_save_draft_area_files(
            $draftitemid,
            $contextid,
            self::COMPONENT_NAME,
            self::PACKAGE_FILEAREA,
            self::PACKAGE_ITEM_ID,
            [
                'subdirs' => 0,
                'maxfiles' => 1,
            ]
        );
    }
}
