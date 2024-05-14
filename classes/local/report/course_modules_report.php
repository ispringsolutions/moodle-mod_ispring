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

namespace mod_ispring\local\report;

use core_reportbuilder\system_report;
use mod_ispring\local\report\entity\ispring_module;

class course_modules_report extends system_report {
    public const PARAM_MOODLE_COURSE_ID = 'moodle_course_id';

    protected function initialise(): void {
        $courseid = $this->get_parameter(self::PARAM_MOODLE_COURSE_ID, 0, PARAM_INT);

        $entity = new ispring_module();
        $entityalias = $entity->get_table_alias('ispring');
        $this->add_entity($entity);

        $this->set_main_table('ispring', $entityalias);

        $this->add_base_condition_simple("{$entityalias}.course", $courseid);
        $this->add_columns($courseid, $entity);
    }

    protected function can_view(): bool {
        return true;
    }

    private function add_columns(int $courseid, ispring_module $entity): void {
        $modinfo = get_fast_modinfo($courseid);
        $courseformat = $modinfo->get_course()->format;

        if (course_format_uses_sections($courseformat)) {
            $this->add_column(
                $entity->get_column(ispring_module::COLUMN_SECTION_NAME),
            )
                ->set_title(new \lang_string('sectionname', "format_{$courseformat}"));
        }

        $this->add_column($entity->get_column(ispring_module::COLUMN_NAME));
        $this->add_column($entity->get_column(ispring_module::COLUMN_DESCRIPTION));

        if (has_capability('mod/ispring:viewallreports', $this->get_context())) {
            $this->add_column($entity->get_column(ispring_module::COLUMN_REPORT));
        }
    }
}
