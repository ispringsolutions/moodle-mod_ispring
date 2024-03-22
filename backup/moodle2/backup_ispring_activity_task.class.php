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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/ispring/backup/moodle2/backup_ispring_stepslib.php');

class backup_ispring_activity_task extends backup_activity_task {
    protected function define_my_settings(): void {
        // No particular settings for this activity.
    }

    protected function define_my_steps(): void {
        $this->add_step(new backup_ispring_activity_structure_step('ispring_structure', 'ispring.xml'));
    }

    public static function encode_content_links($content): string {
        // No particular settings for this activity.
        return $content;
    }
}
