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

namespace mod_ispring\local\ispring_module\infrastructure;

use mod_ispring\local\ispring_module\app\data\ispring_module_data;
use mod_ispring\local\ispring_module\app\repository\ispring_module_repository_interface;

class ispring_module_repository implements ispring_module_repository_interface {
    private \moodle_database $database;

    public function __construct() {
        global $DB;
        $this->database = $DB;
    }

    public function add(ispring_module_data $data): int {
        $ispring = self::ispring_module_data_to_std_class($data);
        $ispring->grade = 0;

        $transaction = $this->database->start_delegated_transaction();
        try {
            $id = $this->database->insert_record('ispring', $ispring);
            $transaction->allow_commit();
            return $id;
        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw new \RuntimeException('Cannot add ispring to database');
        }
    }

    public function update(int $id, ispring_module_data $data): bool {
        $ispring = self::ispring_module_data_to_std_class($data);
        $ispring->id = $id;

        $transaction = $this->database->start_delegated_transaction();
        try {
            $result = $this->database->update_record('ispring', $ispring);
            $transaction->allow_commit();
            return $result;
        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw new \RuntimeException('Cannot update ispring instance in database');
        }
    }

    public function remove(int $id): bool {
        $transaction = $this->database->start_delegated_transaction();
        try {
            $result = $this->database->delete_records('ispring', ['id' => $id]);
            $transaction->allow_commit();
            return $result;
        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw new \RuntimeException('Cannot remove ispring instance from database');
        }
    }

    private static function ispring_module_data_to_std_class(ispring_module_data $data): \stdClass {
        $result = new \stdClass();

        $result->name = $data->get_name();
        $result->course = $data->get_moodle_course_id();
        $result->grademethod = $data->get_grade_method();
        $result->timeopen = $data->get_time_open();
        $result->timeclose = $data->get_time_close();

        if ($description = $data->get_description()) {
            $result->intro = $description->get_text();
            $result->introformat = $description->get_format();
        }

        return $result;
    }
}
