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

namespace mod_ispring\local\common\infrastructure;

use mod_ispring\local\common\app\exception\inaccessible_content_exception;
use mod_ispring\local\content\api\content_api_interface;

class context_utils {
    public static function get_module_context(content_api_interface $contentapi, int $contentid): \context_module {
        $content = $contentapi->get_by_id($contentid);
        if (!$content) {
            throw new inaccessible_content_exception();
        }
        [, $cm] = get_course_and_cm_from_instance($content->get_ispring_module_id(), 'ispring');
        return \context_module::instance($cm->id);
    }
}
