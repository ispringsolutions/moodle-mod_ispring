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

namespace mod_ispring\local\report\entity;

use core_reportbuilder\local\report\column;
use html_writer;
use lang_string;
use mod_ispring\local\report\entity\base as base_entity;
use stdClass;

class ispring_module extends base_entity {
    public const COLUMN_SECTION_NAME = 'section_name';
    public const COLUMN_NAME = 'name';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_REPORT = 'report';

    protected function get_default_table_aliases(): array {
        return [
            'ispring' => 'ism',
        ];
    }

    protected function get_default_entity_title(): lang_string {
        return new lang_string('entityispring', 'ispring');
    }

    protected function get_all_columns(): array {
        $alias = $this->get_table_alias('ispring');

        $columns[] = (new column(
            self::COLUMN_SECTION_NAME,
            new lang_string('sectionname'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.id", 'id')
            ->add_field("{$alias}.course", 'course')
            ->add_callback(static function ($value, stdClass $row): string {
                if (!$cm = self::get_fast_cm_info($row->course, $row->id)) {
                    return '';
                }
                return get_section_name($cm->get_course(), $cm->sectionnum);
            });

        $columns[] = (new column(
            self::COLUMN_NAME,
            new lang_string('name'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.id", 'id')
            ->add_field("{$alias}.course", 'course')
            ->add_field("{$alias}.name", 'name')
            ->add_callback(static function ($value, stdClass $row): string {
                if (!$cm = self::get_fast_cm_info($row->course, $row->id)) {
                    return $row->name;
                }
                return html_writer::link($cm->url, $row->name);
            });

        $columns[] = (new column(
            self::COLUMN_DESCRIPTION,
            new lang_string('moduledescription', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.id", 'id')
            ->add_field("{$alias}.course", 'course')
            ->add_field("{$alias}.intro", 'intro')
            ->add_field("{$alias}.introformat", 'introformat')
            ->add_callback(static function ($value, stdClass $row): string {
                if (!$cm = self::get_fast_cm_info($row->course, $row->id)) {
                    return '';
                }
                return format_module_intro('ispring', $row, $cm->id);
            });

        $columns[] = (new column(
            self::COLUMN_REPORT,
            new lang_string('report', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.id", 'id')
            ->add_field("{$alias}.course", 'course')
            ->add_callback(static function ($value, stdClass $row): string {
                if (!$cm = self::get_fast_cm_info($row->course, $row->id)) {
                    return '';
                }
                if (!has_capability('mod/ispring:viewallreports', $cm->context)) {
                    return '';
                }
                $url = new \moodle_url('/mod/ispring/report.php', ['id' => $cm->id]);
                return html_writer::link($url, get_string('reportlink', 'ispring'));
            });

        return $columns;
    }

    protected function get_all_filters(): array {
        return [];
    }

    private static function get_fast_cm_info(int $courseid, int $ispringmoduleid): ?\cm_info {
        $modinfo = get_fast_modinfo($courseid);
        return $modinfo->get_instances_of('ispring')[$ispringmoduleid] ?? null;
    }
}
