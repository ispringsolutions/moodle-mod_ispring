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
class restore_ispring_activity_structure_step extends restore_activity_structure_step {
    protected function define_structure() {
        $paths = [
            new restore_path_element('ispring', '/activity/ispring'),
            new restore_path_element('ispring_content', '/activity/ispring/ispring_content'),
            new restore_path_element('ispring_session', '/activity/ispring/ispring_content/ispring_session'),
        ];

        return $this->prepare_activity_structure($paths);
    }

    protected function process_ispring(array $data): void {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        $id = $DB->insert_record('ispring', $data);

        $this->apply_activity_instance($id);
    }

    protected function process_ispring_content(array $data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->ispring_id = $this->get_new_parentid('ispring');

        $newid = $DB->insert_record('ispring_content', $data);
        $this->set_mapping('ispring_content', $oldid, $newid);
    }

    protected function process_ispring_session(array $data): void {
        global $DB;

        $data = (object)$data;
        $data->ispring_content_id = $this->get_new_parentid('ispring_content');

        $DB->insert_record('ispring_session', $data);
    }

    protected function after_execute(): void {
        $this->add_related_files('mod_ispring', 'content', null);
    }
}
