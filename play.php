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
use mod_ispring\local\pages\play_page;

require_once('../../config.php');

$courseid = optional_param('id', '', PARAM_INT);
$argparser = new argparser($courseid, di_container::get_ispring_module_api());

require_login($argparser->get_moodle_course(), true, $argparser->get_cm());

$ispring = $argparser->get_ispring_module();
$contentapi = di_container::get_content_api();

$entrypointinfo = $contentapi->get_latest_version_entrypoint_info(
    context_module::instance($argparser->get_cm()->id)->id,
    $ispring->get_id()
);

if (!$entrypointinfo) {
    throw new \invalid_state_exception('Error, ispring cm does not contain content');
}

$modulecontext = context_module::instance($argparser->get_cm()->id);

if (!availability_checker::module_available($ispring->get_id(), $modulecontext)) {
    throw new \moodle_exception('unavailabletime', 'ispring');
}

$page = new play_page(
    $entrypointinfo->get_content_id(),
    $entrypointinfo->get_entrypoint_url(),
    $CFG->wwwroot . '/mod/ispring/view.php?id=' . $courseid,
    '/mod/ispring/play.php',
    ['id' => $courseid]
);

$page->set_title($ispring->get_name());
$page->set_secondary_active_tab("modulepage");

echo $page->get_header();
echo $page->get_content();
echo $page->get_footer();
