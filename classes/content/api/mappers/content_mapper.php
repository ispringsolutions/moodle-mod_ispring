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

namespace mod_ispring\content\api\mappers;

use mod_ispring\content\api\input\content_input;
use mod_ispring\content\api\output\content_output;
use mod_ispring\content\app\data\content_data;
use mod_ispring\content\app\query\model\content;

class content_mapper
{
    public static function get_content_data(content_input $content_input): content_data
    {
        return new content_data(
            $content_input->get_file_id(),
            $content_input->get_ispring_module_id(),
            $content_input->get_context_id(),
            $content_input->get_user_context_id(),
        );
    }

    public static function get_content_output(content $content_info_data): content_output
    {
        return new content_output(
            $content_info_data->get_id(),
            $content_info_data->get_file_id(),
            $content_info_data->get_ispring_module_id(),
            $content_info_data->get_creation_time(),
            $content_info_data->get_filename(),
            $content_info_data->get_filepath(),
            $content_info_data->get_version(),
        );
    }
}