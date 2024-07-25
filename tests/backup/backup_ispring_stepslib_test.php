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

namespace mod_ispring\backup;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_stepslib.php');
require_once(__DIR__ . '/../../backup/moodle2/backup_ispring_stepslib.php');

use backup_ispring_activity_structure_step;
use backup_nested_element;

/**
 * Test backup_ispring_activity_structure_step class.
 *
 * @covers \backup_ispring_activity_structure_step
 */
final class backup_ispring_stepslib_test extends \basic_testcase {
    public function test_define_ispring_structure_exports_all_fields_from_ispring_tables(): void {
        $structure = backup_ispring_activity_structure_step::define_ispring_structure();

        $this->assertEquals('ispring', $structure->get_source_table());
        $this->assert_table_columns_match_backup_structure('ispring', $structure);

        $contentstructure = $structure->get_child('ispring_content');
        $this->assertEquals('ispring_content', $contentstructure->get_source_table());
        $this->assert_table_columns_match_backup_structure('ispring_content', $contentstructure);

        $sessionstructure = $contentstructure->get_child('ispring_session');
        $this->assertEquals('ispring_session', $sessionstructure->get_source_table());
        $this->assert_table_columns_match_backup_structure('ispring_session', $sessionstructure, ['player_id']);
    }

    private function assert_table_columns_match_backup_structure(
        string $table,
        backup_nested_element $structure,
        array $ignorablefields = []
    ): void {
        global $DB;
        $expected = $DB->get_columns($table);

        $actual = $structure->get_attributes()
            + $structure->get_final_elements()
            + array_fill_keys($ignorablefields, null);

        $this->assertEqualsCanonicalizing(array_keys($expected), array_keys($actual));
    }
}
