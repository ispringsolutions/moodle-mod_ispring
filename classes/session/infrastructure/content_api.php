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

namespace mod_ispring\session\infrastructure;

use mod_ispring\content\api\content_api_interface;
use mod_ispring\session\app\adapter\content_api_interface as content_api_adapter_interface;

class content_api implements content_api_adapter_interface {
    private content_api_interface $api;

    public function __construct(
        content_api_interface $api
    ) {
        $this->api = $api;
    }

    public function get_ispring_module_id_by_content_id(int $id): int {
        $content = $this->api->get_by_id($id);

        return $content ? $content->get_ispring_module_id() : 0;
    }

    public function get_newest_content_id(int $ispringmoduleid): int {
        $content = $this->api->get_latest_version_content_by_ispring_module_id($ispringmoduleid);
        if ($content) {
            return $content->get_id();
        }

        return 0;
    }

    public function get_ids_by_ispring_module_id(int $ispringmoduleid): array {
        return $this->api->get_ids_by_ispring_module_id($ispringmoduleid);
    }
}
