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

namespace mod_ispring\content\app\service;

use mod_ispring\common\infrastructure\transaction\db_transaction;
use mod_ispring\content\app\adapter\ispring_module_api_interface;
use mod_ispring\content\app\data\content_data;
use mod_ispring\content\app\model\content;
use mod_ispring\content\app\model\description_parser;
use mod_ispring\content\app\model\file_info;
use mod_ispring\content\app\query\content_query_service_interface;
use mod_ispring\content\app\query\model\content as content_query;
use mod_ispring\content\app\repository\content_repository_interface;

class content_service
{
    public function __construct(
        private readonly file_storage_interface $file_storage,
        private readonly content_repository_interface $content_repository,
        private readonly ispring_module_api_interface $ispring_module_api,
        private readonly content_query_service_interface $content_query_service,
    )
    {
    }

    public function add_content(content_data $content_data): int
    {
        $transaction = new db_transaction();
        try
        {
            $content = $this->content_query_service->get_latest_version_content_by_ispring_module_id(
                $content_data->get_ispring_module_id(),
            );

            if (!$this->ispring_module_api->exists($content_data->get_ispring_module_id()))
            {
                throw new \RuntimeException("Error, ispring module does not exist");
            }

            $new_content_was_uploaded = !$this->file_storage->user_draft_area_is_empty(
                $content_data->get_user_context_id(),
                $content_data->get_file_id(),
            );
            // when we update ispring without any changes of content, Moodle creates new dumb file id
            if ($content && !$new_content_was_uploaded)
            {
                $transaction->commit();
                return $content->get_id();
            }

            $transaction->execute(
                fn() => $this->file_storage->unzip_package(
                    $content_data->get_context_id(),
                    $content_data->get_file_id(),
                    $content_data->get_user_context_id(),
                    $content_data->get_file_id(),
                ),
                fn() => $this->file_storage->clear_ispring_content_area(
                    $content_data->get_context_id(),
                    $content_data->get_file_id(),
                ),
            );

            $description_file = $this->file_storage->get_description_file(
                $content_data->get_context_id(),
                $content_data->get_file_id()
            );

            $version = self::get_new_version($content);

            $content_id = $transaction->execute(
                fn() => $this->add_content_to_repository($content_data, $description_file, $version),
                fn(int $id) => $this->content_repository->remove($id),
            );

            $transaction->commit();
            return $content_id;
        }
        catch (\Throwable $e)
        {
            $transaction->rollback($e);
            throw $e;
        }
    }

    public function remove(int $module_context_id, int $content_id): void
    {
        $transaction = new db_transaction();
        try
        {
            $content = $this->content_query_service->get_by_id($content_id);
            if (!$content)
            {
                throw new \RuntimeException('Content not found');
            }
            $this->content_repository->remove($content_id);
            $this->file_storage->clear_ispring_content_area($module_context_id, $content->get_file_id());

            $transaction->commit();
        }
        catch (\Throwable $e)
        {
            $transaction->rollback($e);
            throw $e;
        }
    }

    public function present_file(int $context_id, array $args): void
    {
        $this->file_storage->present_file($context_id, $args);
    }

    private static function get_new_version(?content_query $content): int
    {
        return ($content ? $content->get_version() : 0) + 1;
    }

    private static function generate_file_info_from_entrypoint(string $entrypoint): file_info
    {
        $path_info = pathinfo($entrypoint);
        return new file_info(
            '/' . $path_info['dirname'],
            $path_info['filename'] . '.' . $path_info['extension'],
        );
    }

    private function add_content_to_repository(
        content_data $content_data,
        \stored_file $description_file,
        int $version,
    ): int
    {
        $description_params = description_parser::parse($description_file)->get_description_params();

        $content_path = self::generate_file_info_from_entrypoint($description_params->get_entrypoint());

        $report_path = null;
        if ($description_params->get_report_entrypoint() !== null)
        {
            $report_path = self::generate_file_info_from_entrypoint(
                $description_params->get_report_entrypoint(),
            );
        }

        return $this->content_repository->add(new content(
            $content_data->get_file_id(),
            $content_data->get_ispring_module_id(),
            $description_params->get_creation_time(),
            $content_path,
            $version,
            $report_path,
        ));
    }
}