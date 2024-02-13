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

use mod_ispring\ispring_module\api\ispring_module_api_interface;
use mod_ispring\content\app\adapter\ispring_module_api_interface as api_adapter_interface;

class ispring_module_api implements api_adapter_interface
{
    private ispring_module_api_interface $api;

    public function __construct(ispring_module_api_interface $api)
    {
        $this->api = $api;
    }

    public function exists(int $id): bool
    {
        return $this->api->exists($id);
    }
}