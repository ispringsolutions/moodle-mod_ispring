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

namespace mod_ispring\content\api;

use mod_ispring\content\api\input\content_input;
use mod_ispring\content\api\mappers\content_mapper;
use mod_ispring\content\api\output\content_output;
use mod_ispring\content\api\output\entrypoint_info;
use mod_ispring\content\app\query\content_query_service_interface;
use mod_ispring\content\app\service\content_service;
use mod_ispring\content\app\service\file_storage_interface;

class content_api implements content_api_interface
{
    public function __construct(
        private readonly content_service $content_service,
        private readonly content_query_service_interface $content_query_service,
        private readonly file_storage_interface $file_storage,
    )
    {
    }

    public function add_content(content_input $content_input): int
    {
        return $this->content_service->add_content(
            content_mapper::get_content_data($content_input),
        );
    }

    public function remove(int $module_context_id, int $content_id): void
    {
        $this->content_service->remove($module_context_id, $content_id);
    }

    public function present_file(int $context_id, array $args): void
    {
        $this->content_service->present_file($context_id, $args);
    }

    public function get_latest_version_entrypoint_info(int $context_id, int $ispring_module_id): ?entrypoint_info
    {
        $content = $this->content_query_service->get_latest_version_content_by_ispring_module_id($ispring_module_id);
        if (!$content)
        {
            return null;
        }

        return new entrypoint_info($content->get_id(), $this->file_storage->generate_entrypoint_url(
            $context_id,
            $content->get_file_id(),
            $content->get_filepath(),
            $content->get_filename(),
        ));
    }

    public function get_latest_version_content_by_ispring_module_id(int $ispring_module_id): ?content_output
    {
        $content = $this->content_query_service->get_latest_version_content_by_ispring_module_id($ispring_module_id);

        return $content !== null ? content_mapper::get_content_output($content) : null;
    }

    public function exists(int $id): bool
    {
        return $this->content_query_service->exists($id);
    }

    public function get_by_id(int $content_id): ?content_output
    {
        $content = $this->content_query_service->get_by_id($content_id);

        return $content !== null
            ? content_mapper::get_content_output($content)
            : null;
    }

    public function get_report_url(int $context_id, int $content_id): ?string
    {
        $content = $this->content_query_service->get_by_id($content_id);
        if (!$content || !$content->get_report_path() || !$content->get_report_filename())
        {
            return null;
        }

        return $this->file_storage->generate_entrypoint_url(
            $context_id,
            $content->get_file_id(),
            $content->get_report_path(),
            $content->get_report_filename(),
        );
    }

    public function get_ids_by_ispring_module_id(int $ispring_module_id): array
    {
        return $this->content_query_service->get_ids_by_ispring_module_id($ispring_module_id);
    }
}