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


use mod_ispring\argparser\argparser;
use mod_ispring\di_container;
use mod_ispring\pages\report_page;

require_once('../../config.php');
$cm_id = optional_param('id', '', PARAM_INT);

$argparser = new argparser($cm_id, di_container::get_ispring_module_api());

require_login($argparser->get_moodle_course(), true, $argparser->get_cm());

$module_context = context_module::instance($argparser->get_cm()->id);
require_capability('mod/ispring:viewallreports', $module_context);

$ispring_module_id = $argparser->get_ispring_module()->get_id();
$passing_requirements_were_updated = di_container::get_session_api()->passing_requirements_were_updated($ispring_module_id);

$page = new report_page(
    $ispring_module_id,
    $passing_requirements_were_updated,
    '/mod/ispring/report.php',
    ['id' => $cm_id]
);

$page->set_title(get_string('report', 'ispring'));
$page->set_secondary_active_tab('report');

$page->set_context($module_context);
echo $page->get_header();
echo $page->get_content();
echo $page->get_footer();