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


use mod_ispring\local\argparser\argparser;
use mod_ispring\local\common\app\available\availability_checker;
use mod_ispring\local\di_container;
use mod_ispring\local\pages\report_page;

require_once('../../config.php');
$cmid = optional_param('id', '', PARAM_INT);

$argparser = new argparser($cmid, di_container::get_ispring_module_api());

require_login($argparser->get_moodle_course(), true, $argparser->get_cm());

$modulecontext = context_module::instance($argparser->get_cm()->id);
require_capability('mod/ispring:viewallreports', $modulecontext);

$ispringmoduleid = $argparser->get_ispring_module()->get_id();

if (!availability_checker::module_available($ispringmoduleid, $modulecontext)) {
    throw new \moodle_exception('unavailabletime', 'ispring');
}

$passingrequirementswereupdated = di_container::get_session_api()->passing_requirements_were_updated($ispringmoduleid);

$page = new report_page(
    $ispringmoduleid,
    $passingrequirementswereupdated,
    '/mod/ispring/report.php',
    ['id' => $cmid]
);

$page->set_title(get_string('report', 'ispring'));
$page->set_secondary_active_tab('report');

$page->set_context($modulecontext);
echo $page->get_header();
echo $page->get_content();
echo $page->get_footer();
