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
class backup_ispring_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure(): backup_nested_element {
        // Define each element separately.
        $ispringmodule = new backup_nested_element('ispring', ['id'], [
            'course',
            'name',
            'intro',
            'introformat',
            'grade',
            'grademethod',
            'timeopen',
            'timeclose',
        ]);

        $content = new backup_nested_element('ispring_content', ['id'], [
            'ispring_id',
            'file_id',
            'path',
            'filename',
            'creation_time',
            'version',
            'report_path',
            'report_filename',
        ]);

        $session = new backup_nested_element('ispring_session', ['id'], [
            'user_id',
            'ispring_content_id',
            'status',
            'score',
            'begin_time',
            'attempt',
            'end_time',
            'duration',
            'persist_state',
            'persist_state_id',
            'max_score',
            'min_score',
            'passing_score',
            'detailed_report',
        ]);

        // Build the tree.
        $ispringmodule->add_child($content);
        $content->add_child($session);

        // Define sources.
        $ispringmodule->set_source_table('ispring', ['id' => backup::VAR_ACTIVITYID]);
        $content->set_source_table('ispring_content', ['ispring_id' => backup::VAR_PARENTID]);
        $session->set_source_table('ispring_session', ['ispring_content_id' => backup::VAR_PARENTID]);

        $session->annotate_ids('user', 'user_id');

        // Define file annotations.
        $ispringmodule->annotate_files('mod_ispring', 'content', null);

        return $this->prepare_activity_structure($ispringmodule);
    }
}
