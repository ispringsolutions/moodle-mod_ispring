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

use mod_ispring\argparser\argparser;
use mod_ispring\common\app\available\availability_checker;
use mod_ispring\di_container;
use mod_ispring\event\course_module_viewed;
use mod_ispring\pages\view_page;

require_once('../../config.php');
$cmid = optional_param('id', '', PARAM_INT);

$argparser = new argparser($cmid, di_container::get_ispring_module_api());

$ispring = $argparser->get_ispring_module();

require_login($argparser->get_moodle_course(), true, $argparser->get_cm());

$modulecontext = context_module::instance($argparser->get_cm()->id);
$event = course_module_viewed::create([
    'objectid' => $ispring->get_id(),
    'context' => $modulecontext,
]);
$event->trigger();

$sessionapi = di_container::get_session_api();
$passingrequirementswereupdated = $sessionapi->passing_requirements_were_updated_for_user($ispring->get_id(), $USER->id);

$page = new view_page(
    $ispring,
    $sessionapi,
    $cmid,
    $USER->id,
    $passingrequirementswereupdated,
    availability_checker::module_available($ispring->get_id(), $modulecontext),
    '/mod/ispring/view.php',
    ['id' => $cmid],
);

$page->set_title($ispring->get_name());
$page->set_context($modulecontext);
$page->set_secondary_active_tab('modulepage');

echo $page->get_header();
echo $page->get_content();
echo $page->get_footer();
