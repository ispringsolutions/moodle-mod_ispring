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

namespace mod_ispring\mapper;

use mod_ispring\ispring_module\api\output\ispring_module_output;

class std_mapper
{
    public static function ispring_module_output_to_std_class(ispring_module_output $data): \stdClass
    {
        $description = $data->get_description();
        $result = new \stdClass();

        $result->id = $data->get_id();
        $result->course = $data->get_moodle_course_id();
        $result->name = $data->get_name();
        $result->grade = $data->get_grade();
        $result->grademethod = $data->get_grade_method();
        $result->intro = $description ? $description->get_text() : null;
        $result->introformat = $description ? $description->get_format() : 0;

        return $result;
    }
}