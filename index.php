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

use mod_ispring\pages\index_page;

require_once('../../config.php');

$moodle_course_id = required_param('id', PARAM_INT);
$course = get_course($moodle_course_id);

require_login($course);

$page = new index_page($moodle_course_id, '/mod/ispring/index.php', ['id' => $moodle_course_id]);
$page->set_title(get_string('coursemoduleslisttitle', 'ispring', $course->shortname));
$page->set_heading($course->fullname);
$page->set_page_layout('incourse');

echo $page->get_header();
echo $page->get_content();
echo $page->get_footer();