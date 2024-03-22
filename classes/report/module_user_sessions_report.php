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
use mod_ispring\common\infrastructure\capability_utils;
use mod_ispring\report\entity\session;

class module_user_sessions_report extends system_report {
    public const PARAM_ISPRING_MODULE_ID = 'ispring_module_id';
    public const PARAM_USER_ID = 'user_id';
    public const PARAM_PAGE_URL = 'page_url';

    protected function initialise(): void {
        $ispringmoduleid = $this->get_parameter(self::PARAM_ISPRING_MODULE_ID, 0, PARAM_INT);
        $userid = $this->get_parameter(self::PARAM_USER_ID, 0, PARAM_INT);
        $pageurl = $this->get_parameter(self::PARAM_PAGE_URL, '', PARAM_TEXT);

        $showdetailedreports = capability_utils::can_view_detailed_reports_for_user($this->get_context(), $userid);

        $entity = new session($pageurl);
        $entityalias = $entity->get_table_alias('ispring_session');
        $this->add_entity($entity);

        $this->set_main_table('ispring_session', $entityalias);
        $this->add_join("JOIN {ispring_content} isc ON {$entityalias}.ispring_content_id = isc.id");

        $this->add_base_condition_simple("isc.ispring_id", $ispringmoduleid);
        $this->add_base_condition_simple("{$entityalias}.user_id", $userid);
        $this->add_columns($showdetailedreports);
        $this->set_initial_sort_column('session:attempt', SORT_ASC);
    }

    protected function can_view(): bool {
        return has_capability('mod/ispring:view', $this->get_context());
    }

    private function add_columns(bool $showdetailedreports): void {
        $columns = [
            'session:attempt',
            'session:end_time',
            'session:status',
            'session:max_score',
            'session:score',
        ];
        if ($showdetailedreports) {
            $columns[] = 'session:review';
        }
        $this->add_columns_from_entities($columns);
    }
}
