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

require(__DIR__ . '/../../config.php');

// Course module ID.
$id = required_param('id', PARAM_INT);

$argparser = new argparser($id, \mod_ispring\di_container::get_ispring_module_api());

require_login($argparser->get_moodle_course(), true, $argparser->get_cm());

if (has_capability('mod/ispring:viewallreports', context_module::instance($argparser->get_cm()->id))) {
    redirect(new moodle_url('/mod/ispring/report.php', ['id' => $id]));
}
redirect(new moodle_url('/mod/ispring/view.php', ['id' => $id]));
