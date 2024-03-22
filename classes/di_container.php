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

namespace mod_ispring;

use mod_ispring\content\api\content_api;
use mod_ispring\content\api\content_api_interface;
use mod_ispring\content\app\service\content_service;
use mod_ispring\content\infrastructure\content_repository;
use mod_ispring\content\infrastructure\file_storage;
use mod_ispring\content\infrastructure\query\content_query_service;
use mod_ispring\ispring_module\api\ispring_module_api;
use mod_ispring\ispring_module\api\ispring_module_api_interface;
use mod_ispring\ispring_module\app\service\ispring_module_service;
use mod_ispring\ispring_module\infrastructure\ispring_module_repository;
use mod_ispring\ispring_module\infrastructure\query\ispring_module_query_service;
use mod_ispring\session\api\session_api;
use mod_ispring\session\api\session_api_interface;
use mod_ispring\session\app\service\session_service;
use mod_ispring\session\infrastructure\query\session_query_service;
use mod_ispring\session\infrastructure\session_repository;

class di_container {
    public static function get_ispring_module_api(): ispring_module_api_interface {
        $repository = new ispring_module_repository();
        return new ispring_module_api(
            new ispring_module_service(
                $repository
            ),
            new ispring_module_query_service(
                $repository
            )
        );
    }

    public static function get_content_api(): content_api_interface {
        $repository = new content_repository();
        $filestorage = new file_storage();
        $ispringadapter = new \mod_ispring\content\infrastructure\ispring_module_api(self::get_ispring_module_api());
        $contentqueryservice = new content_query_service();

        return new content_api(
            new content_service(
                $filestorage,
                $repository,
                $ispringadapter,
                $contentqueryservice
            ),
            $contentqueryservice,
            $filestorage
        );
    }

    public static function get_session_api(): session_api_interface {
        $repository = new session_repository();
        $contentadapter = new \mod_ispring\session\infrastructure\content_api(self::get_content_api());
        $ispringadapter = new \mod_ispring\session\infrastructure\ispring_module_api(self::get_ispring_module_api());
        $sessionqueryservice = new session_query_service($ispringadapter);

        return new session_api(
            new session_service(
                $repository,
                $contentadapter,
                $sessionqueryservice
            ),
            $sessionqueryservice,
            $contentadapter
        );
    }
}
