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

namespace mod_ispring\ispring_module\api;

use mod_ispring\ispring_module\api\input\create_or_update_ispring_module_input;
use mod_ispring\ispring_module\api\output\ispring_module_output;
use mod_ispring\ispring_module\api\mappers\ispring_module_mapper;
use mod_ispring\ispring_module\app\query\ispring_module_query_service_interface;
use mod_ispring\ispring_module\app\service\ispring_module_service;

class ispring_module_api implements ispring_module_api_interface
{
    private ispring_module_service $ispring_service;
    private ispring_module_query_service_interface $ispring_query_service;

    public function __construct(
        ispring_module_service $ispring_service,
        ispring_module_query_service_interface $ispring_query_service
    )
    {
        $this->ispring_service = $ispring_service;
        $this->ispring_query_service = $ispring_query_service;
    }

    public function create(create_or_update_ispring_module_input $create_ispring_input): int
    {
        return $this->ispring_service->create(
            ispring_module_mapper::get_data($create_ispring_input),
        );
    }

    public function exists(int $id): bool
    {
        return $this->ispring_query_service->exists($id);
    }

    public function update(int $instance, create_or_update_ispring_module_input $ispring_input): bool
    {
        return $this->ispring_service->update(
            $instance,
            ispring_module_mapper::get_data($ispring_input)
        );
    }

    public function delete(int $id): bool
    {
        return $this->ispring_service->delete($id);
    }

    public function get_by_id(int $id): ?ispring_module_output
    {
        $data = $this->ispring_query_service->get_by_id($id);
        return $data ? ispring_module_mapper::get_output($data) : null;
    }

    public function is_available(int $module_id): bool
    {
        $data = $this->ispring_query_service->get_by_id($module_id);

        if (!$data)
        {
            return false;
        }

        $now = time();
        $after_open = $data->get_time_open() ? $data->get_time_open() <= $now : true;
        $before_close = $data->get_time_close() ? $data->get_time_close() > $now : true;

        return $after_open && $before_close;
    }
}