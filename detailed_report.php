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

use mod_ispring\local\common\infrastructure\capability_utils;
use mod_ispring\local\di_container;
use mod_ispring\local\pages\detailed_report_page;

require_once('../../config.php');

$sessionid = required_param('session_id', PARAM_INT);
$returnurl = optional_param('return_url', '', PARAM_LOCALURL);

$sessionapi = di_container::get_session_api();
$contentapi = di_container::get_content_api();

$report = $sessionapi->get_detailed_report($sessionid);
if (!$report) {
    throw new \moodle_exception('sessionnotfound', 'ispring');
}

$content = $contentapi->get_by_id($report->get_content_id());
if (!$content) {
    throw new \moodle_exception('contentnotfound', 'ispring');
}

[$course, $cm] = get_course_and_cm_from_instance($content->get_ispring_module_id(), 'ispring');

require_login($course->id, true, $cm);

$modulecontext = context_module::instance($cm->id);
if (!capability_utils::can_view_detailed_reports_for_user($modulecontext, $report->get_user_id())) {
    throw new \moodle_exception('sessionnotfound', 'ispring');
}

$reporturl = $contentapi->get_report_url($modulecontext->id, $report->get_content_id());
if (!$reporturl) {
    throw new \moodle_exception('reportnotfound', 'ispring');
}

$canviewgeneralreport = has_capability('mod/ispring:viewallreports', $modulecontext);
if (!$returnurl) {
    $returnurl = $canviewgeneralreport ? "report.php?id={$cm->id}" : "view.php?id={$cm->id}";
}

$pageurl = '/mod/ispring/detailed_report.php';
$pageargs = ['session_id' => $sessionid];
$page = new detailed_report_page(
    $sessionid,
    $reporturl,
    $returnurl,
    $report->get_user_id(),
    $pageurl,
    $pageargs
);

$page->set_title(get_string('detailedreport', 'ispring'));
$page->set_secondary_active_tab('report');

if ($canviewgeneralreport) {
    $page->add_navbar(get_string('report', 'ispring'), '/mod/ispring/report.php', ['id' => $cm->id]);
}
$page->add_navbar(get_string('detailedreport', 'ispring'), $pageurl, $pageargs);

$page->set_context($modulecontext);
echo $page->get_header();
echo $page->get_content();
echo $page->get_footer();
