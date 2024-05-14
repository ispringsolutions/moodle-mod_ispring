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

namespace mod_ispring\local\content\app\service;

use mod_ispring\local\common\infrastructure\transaction\db_transaction;
use mod_ispring\local\common\infrastructure\transaction\transaction_utils;
use mod_ispring\local\content\app\adapter\ispring_module_api_interface;
use mod_ispring\local\content\app\data\content_data;
use mod_ispring\local\content\app\model\content;
use mod_ispring\local\content\app\model\description_parser;
use mod_ispring\local\content\app\model\file_info;
use mod_ispring\local\content\app\query\content_query_service_interface;
use mod_ispring\local\content\app\query\model\content as content_query;
use mod_ispring\local\content\app\repository\content_repository_interface;

class content_service {
    private file_storage_interface $filestorage;
    private content_repository_interface $repository;
    private ispring_module_api_interface $ispringmoduleapi;
    private content_query_service_interface $queryservice;

    public function __construct(
        file_storage_interface $filestorage,
        content_repository_interface $repository,
        ispring_module_api_interface $ispringmoduleapi,
        content_query_service_interface $queryservice
    ) {
        $this->filestorage = $filestorage;
        $this->repository = $repository;
        $this->ispringmoduleapi = $ispringmoduleapi;
        $this->queryservice = $queryservice;
    }

    public function add_content(content_data $contentdata): int {
        return transaction_utils::do_in_transaction(
            db_transaction::class,
            function ($transaction) use ($contentdata) {
                $content = $this->queryservice->get_latest_version_content_by_ispring_module_id(
                    $contentdata->get_ispring_module_id(),
                );

                if (!$this->ispringmoduleapi->exists($contentdata->get_ispring_module_id())) {
                    throw new \RuntimeException("Error, ispring module does not exist");
                }

                $needsupdating = $this->filestorage->content_needs_updating(
                    $contentdata->get_context_id(),
                    $contentdata->get_user_context_id(),
                    $contentdata->get_file_id(),
                );
                if ($content && !$needsupdating) {
                    return $content->get_id();
                }

                $transaction->execute(
                    fn() => $this->filestorage->unzip_package(
                        $contentdata->get_context_id(),
                        $contentdata->get_file_id(),
                        $contentdata->get_user_context_id(),
                        $contentdata->get_file_id(),
                    ),
                    fn() => $this->filestorage->clear_ispring_areas(
                        $contentdata->get_context_id(),
                        $contentdata->get_file_id(),
                    ),
                );

                $descriptionfile = $this->filestorage->get_description_file(
                    $contentdata->get_context_id(),
                    $contentdata->get_file_id()
                );

                $version = self::get_new_version($content);

                $contentid = $transaction->execute(
                    fn() => $this->add_content_to_repository($contentdata, $descriptionfile, $version),
                    fn(int $id) => $this->repository->remove($id),
                );

                return $contentid;
            },
        );
    }

    public function remove(int $modulecontextid, int $contentid): void {
        $transaction = new db_transaction();
        try {
            $content = $this->queryservice->get_by_id($contentid);
            if (!$content) {
                throw new \RuntimeException('Content not found');
            }
            $this->repository->remove($contentid);
            $this->filestorage->clear_ispring_areas($modulecontextid, $content->get_file_id());

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    public function present_file(
        int $contextid,
        string $filearea,
        array $args,
        bool $forcedownload,
        array $options = []
    ): bool {
        return $this->filestorage->present_file($contextid, $filearea, $args, $forcedownload, $options);
    }

    private static function get_new_version(?content_query $content): int {
        return ($content ? $content->get_version() : 0) + 1;
    }

    private static function generate_file_info_from_entrypoint(string $entrypoint): file_info {
        $pathinfo = pathinfo($entrypoint);
        return new file_info(
            '/' . $pathinfo['dirname'],
            $pathinfo['filename'] . '.' . $pathinfo['extension'],
        );
    }

    private function add_content_to_repository(
        content_data $contentdata,
        \stored_file $descriptionfile,
        int $version
    ): int {
        $params = description_parser::parse($descriptionfile)->get_description_params();

        $contentpath = self::generate_file_info_from_entrypoint($params->get_entrypoint());

        $reportpath = null;
        if ($params->get_report_entrypoint() !== null) {
            $reportpath = self::generate_file_info_from_entrypoint(
                $params->get_report_entrypoint(),
            );
        }

        return $this->repository->add(new content(
            $contentdata->get_file_id(),
            $contentdata->get_ispring_module_id(),
            $params->get_creation_time(),
            $contentpath,
            $version,
            $reportpath,
        ));
    }
}
