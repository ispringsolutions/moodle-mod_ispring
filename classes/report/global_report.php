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
 * @copyright   2023 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ispring\report;

use core_reportbuilder\local\entities\user;
use core_reportbuilder\system_report;
use mod_ispring\report\entity\content;
use mod_ispring\report\entity\session;

class global_report extends system_report
{
    public const PARAM_ISPRING_MODULE_ID = 'ispring_id';
    public const PARAM_PAGE_URL = 'page_url';

    protected function initialise(): void
    {
        $entity_main = new session($this->get_parameter(self::PARAM_PAGE_URL, '', PARAM_TEXT));

        $entity_main_alias = $entity_main->get_table_alias('ispring_session');

        $this->set_main_table('ispring_session', $entity_main_alias);
        $this->add_entity($entity_main);

        $this->add_base_fields("{$entity_main_alias}.id");

        $entity_user = new user();
        $entity_user_alias = $entity_user->get_table_alias('user');
        $this->add_entity($entity_user->add_join(
            "LEFT JOIN {user} {$entity_user_alias} ON {$entity_user_alias}.id = {$entity_main_alias}.user_id"
        ));

        $entity_content = new content();
        $entity_content_alias = $entity_content->get_table_alias('ispring_content');
        $this->add_entity($entity_content->add_join(
            "LEFT JOIN {ispring_content} {$entity_content_alias} ON {$entity_content_alias}.id = {$entity_main_alias}.ispring_content_id"
        ));

        $ispring_module_id = $this->get_parameter(self::PARAM_ISPRING_MODULE_ID, 0, PARAM_INT);
        $this->add_base_condition_simple("{$entity_content_alias}.ispring_id", $ispring_module_id);

        $this->add_columns();
        $this->add_filters();

        $this->set_downloadable(true, get_string('globalreport', 'ispring'));
    }

    protected function can_view(): bool
    {
        return has_capability('mod/ispring:viewallreports', $this->get_context());
    }

    /**
     * Adds the columns we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    private function add_columns(): void
    {
        $columns = [
            'user:fullnamewithpicturelink',
            'session:review',
            'session:attempt',
            'session:status',
            'content:version',
            'session:begin_time',
            'session:end_time',
            'session:duration',
            'session:max_score',
            'session:score',
        ];

        $this->add_columns_from_entities($columns);

        if ($column = $this->get_column('user:fullnamewithpicturelink'))
        {
            $column->set_title(new \lang_string('user', 'admin'));
        }
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    private function add_filters(): void
    {
        $filters = [
            'user:fullname',
            'session:attempt',
            'content:version',
            'session:status',
            'session:score',
        ];

        $this->add_filters_from_entities($filters);
    }
}