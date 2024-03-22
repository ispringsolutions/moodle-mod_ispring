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

namespace mod_ispring\ispring_module\api\mappers;

use mod_ispring\ispring_module\api\input\create_or_update_ispring_module_input;
use mod_ispring\ispring_module\api\input\description_input;
use mod_ispring\ispring_module\api\output\description_output;
use mod_ispring\ispring_module\api\output\ispring_module_output;
use mod_ispring\ispring_module\app\data\ispring_module_data;
use mod_ispring\ispring_module\app\model\description;
use mod_ispring\ispring_module\app\query\model\ispring_module_model;

class ispring_module_mapper {
    public static function get_data(create_or_update_ispring_module_input $input): ispring_module_data {
        return new ispring_module_data(
            $input->get_name(),
            $input->get_moodle_course_id(),
            $input->get_grade_method(),
            self::get_description($input->get_description()),
            $input->get_time_open(),
            $input->get_time_close(),
        );
    }

    public static function get_output(ispring_module_model $data): ispring_module_output {
        return new ispring_module_output(
            $data->get_id(),
            $data->get_name(),
            $data->get_moodle_course_id(),
            $data->get_grade(),
            $data->get_grade_method(),
            self::get_description_output($data->get_description()),
            $data->get_time_open(),
            $data->get_time_close(),
        );
    }

    private static function get_description(?description_input $description): ?description {
        return $description
            ? new description($description->get_text(), $description->get_format())
            : null;
    }

    private static function get_description_output(?description $description): ?description_output {
        return $description
            ? new description_output($description->get_text(), $description->get_format())
            : null;
    }
}
