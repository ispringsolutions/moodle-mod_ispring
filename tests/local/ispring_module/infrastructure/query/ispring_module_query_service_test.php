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

namespace mod_ispring\local\ispring_module\infrastructure\query;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../../ispring_testcase.php');

use mod_ispring\ispring_testcase;

/**
 * Test ispring_module_query_service class.
 *
 * @covers \mod_ispring\local\ispring_module\infrastructure\query\ispring_module_query_service
 */
final class ispring_module_query_service_test extends \advanced_testcase {
    private ispring_module_query_service $ispringqueryservice;

    protected function setUp(): void {
        $this->ispringqueryservice = new ispring_module_query_service();
    }

    public function test_exists_returns_false_for_nonexistent_id(): void {
        $this->assertFalse($this->ispringqueryservice->exists(1));
    }

    public function test_exists_returns_true_for_existent_id(): void {
        $instance = ispring_testcase::create_course_and_instance($this);

        $this->assertTrue($this->ispringqueryservice->exists($instance->id));
    }

    public function test_get_by_id_returns_null_for_nonexistent_id(): void {
        $this->assertNull($this->ispringqueryservice->get_by_id(1));
    }

    public function test_get_by_id_returns_module_for_valid_id(): void {
        $instance = ispring_testcase::create_course_and_instance($this);

        $model = $this->ispringqueryservice->get_by_id($instance->id);

        $this->assertEquals($instance->id, $model->get_id());
        $this->assertEquals($instance->name, $model->get_name());
        $this->assertEquals($instance->course, $model->get_moodle_course_id());
        $this->assertEquals($instance->grade, $model->get_grade());
        $this->assertEquals($instance->grademethod, $model->get_grade_method());
        $this->assertEquals($instance->intro, $model->get_description()->get_text());
        $this->assertEquals($instance->introformat, $model->get_description()->get_format());
    }
}
