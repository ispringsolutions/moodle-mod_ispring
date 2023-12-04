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

use mod_ispring\common\infrastructure\capability_utils;
use mod_ispring\di_container;
use mod_ispring\pages\detailed_report_page;
require_once('../../config.php');

$session_id = required_param('session_id', PARAM_INT);
$session_api = di_container::get_session_api();
$content_api = di_container::get_content_api();

$detailed_report = $session_api->get_detailed_report($session_id);
if (!$detailed_report)
{
    throw new \moodle_exception('sessionnotfound', 'ispring');
}

$content = $content_api->get_by_id($detailed_report->get_content_id());
if (!$content)
{
    throw new \moodle_exception('contentnotfound', 'ispring');
}

list($course, $cm) = get_course_and_cm_from_instance($content->get_ispring_module_id(), 'ispring');

require_login($course->id, true, $cm);

$module_context = context_module::instance($cm->id);
if (!capability_utils::can_view_detailed_reports_for_user($module_context, $detailed_report->get_user_id()))
{
    throw new \moodle_exception('sessionnotfound', 'ispring');
}

$report_url = $content_api->get_report_url($module_context->id, $detailed_report->get_content_id());

if (!$report_url)
{
    throw new \moodle_exception('reportnotfound', 'ispring');
}
$page_url = '/mod/ispring/detailed_report.php';
$page_args = ['session_id' => $session_id];
$page = new detailed_report_page(
    $session_id,
    $report_url,
    new \moodle_url('/mod/ispring/report.php', ['id' => $cm->id]),
    $detailed_report->get_user_id(),
    $page_url,
    $page_args
);

$page->set_title(get_string('detailedreport', 'ispring'));
$page->set_secondary_active_tab('report');

$page->add_navbar(get_string('report', 'ispring'), '/mod/ispring/report.php', ['id' => $cm->id]);
$page->add_navbar(get_string('detailedreport', 'ispring'), $page_url, $page_args);

$page->set_context($module_context);
echo $page->get_header();
echo $page->get_content();
echo $page->get_footer();