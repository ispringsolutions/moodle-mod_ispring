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

namespace mod_ispring\report;

use core_reportbuilder\system_report;
use mod_ispring\report\entity\ispring_module;

class course_modules_report extends system_report
{
    public const PARAM_MOODLE_COURSE_ID = 'moodle_course_id';

    protected function initialise(): void
    {
        $moodle_course_id = $this->get_parameter(self::PARAM_MOODLE_COURSE_ID, 0, PARAM_INT);

        $entity_ispring = new ispring_module();
        $entity_ispring_alias = $entity_ispring->get_table_alias('ispring');
        $this->add_entity($entity_ispring);

        $this->set_main_table('ispring', $entity_ispring_alias);

        $this->add_base_condition_simple("{$entity_ispring_alias}.course", $moodle_course_id);
        $this->add_columns($moodle_course_id, $entity_ispring);
    }

    protected function can_view(): bool
    {
        return true;
    }

    private function add_columns(int $moodle_course_id, ispring_module $entity_ispring): void
    {
        $mod_info = get_fast_modinfo($moodle_course_id);
        $course_format = $mod_info->get_course()->format;

        if (course_format_uses_sections($course_format))
        {
            $this->add_column(
                $entity_ispring->get_column(ispring_module::COLUMN_SECTION_NAME),
            )
                ->set_title(new \lang_string('sectionname', "format_{$course_format}"));
        }

        $this->add_column($entity_ispring->get_column(ispring_module::COLUMN_NAME));
        $this->add_column($entity_ispring->get_column(ispring_module::COLUMN_DESCRIPTION));

        if (has_capability('mod/ispring:viewallreports', $this->get_context()))
        {
            $this->add_column($entity_ispring->get_column(ispring_module::COLUMN_REPORT));
        }
    }
}