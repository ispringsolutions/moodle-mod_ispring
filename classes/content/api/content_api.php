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
use mod_ispring\content\api\mappers\content_mapper;
use mod_ispring\content\api\output\content_output;
use mod_ispring\content\api\output\entrypoint_info;
use mod_ispring\content\app\query\content_query_service_interface;
use mod_ispring\content\app\service\content_service;
use mod_ispring\content\app\service\file_storage_interface;

class content_api implements content_api_interface {
    private content_service $service;
    private content_query_service_interface $queryservice;
    private file_storage_interface $filestorage;

    public function __construct(
        content_service $service,
        content_query_service_interface $queryservice,
        file_storage_interface $filestorage
    ) {
        $this->service = $service;
        $this->queryservice = $queryservice;
        $this->filestorage = $filestorage;
    }

    public function add_content(content_input $input): int {
        return $this->service->add_content(
            content_mapper::get_content_data($input),
        );
    }

    public function remove(int $modulecontextid, int $contentid): void {
        $this->service->remove($modulecontextid, $contentid);
    }

    public function present_file(
        int $contextid,
        string $filearea,
        array $args,
        bool $forcedownload,
        array $options = []
    ): bool {
        return $this->service->present_file($contextid, $filearea, $args, $forcedownload, $options);
    }

    public function get_latest_version_entrypoint_info(int $contextid, int $ispringmoduleid): ?entrypoint_info {
        $content = $this->queryservice->get_latest_version_content_by_ispring_module_id($ispringmoduleid);
        if (!$content) {
            return null;
        }

        return new entrypoint_info($content->get_id(), $this->filestorage->generate_entrypoint_url(
            $contextid,
            $content->get_file_id(),
            $content->get_filepath(),
            $content->get_filename(),
        ));
    }

    public function get_latest_version_content_by_ispring_module_id(int $ispringmoduleid): ?content_output {
        $content = $this->queryservice->get_latest_version_content_by_ispring_module_id($ispringmoduleid);

        return $content !== null ? content_mapper::get_content_output($content) : null;
    }

    public function exists(int $id): bool {
        return $this->queryservice->exists($id);
    }

    public function get_by_id(int $contentid): ?content_output {
        $content = $this->queryservice->get_by_id($contentid);

        return $content !== null
            ? content_mapper::get_content_output($content)
            : null;
    }

    public function get_report_url(int $contextid, int $contentid): ?string {
        $content = $this->queryservice->get_by_id($contentid);
        if (!$content || !$content->get_report_path() || !$content->get_report_filename()) {
            return null;
        }

        return $this->filestorage->generate_entrypoint_url(
            $contextid,
            $content->get_file_id(),
            $content->get_report_path(),
            $content->get_report_filename(),
        );
    }

    public function get_ids_by_ispring_module_id(int $ispringmoduleid): array {
        return $this->queryservice->get_ids_by_ispring_module_id($ispringmoduleid);
    }
}
