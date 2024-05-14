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

namespace mod_ispring\local\ispring_module\api;

use mod_ispring\local\ispring_module\api\input\create_or_update_ispring_module_input;
use mod_ispring\local\ispring_module\api\mappers\ispring_module_mapper;
use mod_ispring\local\ispring_module\api\output\ispring_module_output;
use mod_ispring\local\ispring_module\app\query\ispring_module_query_service_interface;
use mod_ispring\local\ispring_module\app\service\ispring_module_service;

class ispring_module_api implements ispring_module_api_interface {
    private ispring_module_service $service;
    private ispring_module_query_service_interface $queryservice;

    public function __construct(
        ispring_module_service $service,
        ispring_module_query_service_interface $queryservice
    ) {
        $this->service = $service;
        $this->queryservice = $queryservice;
    }

    public function create(create_or_update_ispring_module_input $input): int {
        return $this->service->create(
            ispring_module_mapper::get_data($input),
        );
    }

    public function exists(int $id): bool {
        return $this->queryservice->exists($id);
    }

    public function update(int $instance, create_or_update_ispring_module_input $input): bool {
        return $this->service->update(
            $instance,
            ispring_module_mapper::get_data($input)
        );
    }

    public function delete(int $id): bool {
        return $this->service->delete($id);
    }

    public function get_by_id(int $id): ?ispring_module_output {
        $data = $this->queryservice->get_by_id($id);
        return $data ? ispring_module_mapper::get_output($data) : null;
    }

    public function is_available(int $moduleid): bool {
        $data = $this->queryservice->get_by_id($moduleid);

        if (!$data) {
            return false;
        }

        $now = time();
        $afteropen = $data->get_time_open() ? $data->get_time_open() <= $now : true;
        $beforeclose = $data->get_time_close() ? $data->get_time_close() > $now : true;

        return $afteropen && $beforeclose;
    }
}
