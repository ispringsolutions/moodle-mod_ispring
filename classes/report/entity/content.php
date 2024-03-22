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

namespace mod_ispring\report\entity;

use mod_ispring\report\entity\base as base_entity;
use core_reportbuilder\local\filters\number as number_filter;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;

class content extends base_entity {
    protected function get_default_table_aliases(): array {
        return [
            'ispring_content' => 'isc',
        ];
    }

    protected function get_default_entity_title(): lang_string {
        return new lang_string('entitycontent', 'ispring');
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     * @throws \coding_exception
     */
    protected function get_all_columns(): array {
        $alias = $this->get_table_alias('ispring_content');
        $columns = [];

        $columns[] = (new column(
            'version',
            new lang_string('version', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_field("{$alias}.version");

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function get_all_filters(): array {
        $alias = $this->get_table_alias('ispring_content');

        $filters[] = (new filter(
            number_filter::class,
            'version',
            new lang_string('version', 'ispring'),
            $this->get_entity_name(),
            "{$alias}.version"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }
}
