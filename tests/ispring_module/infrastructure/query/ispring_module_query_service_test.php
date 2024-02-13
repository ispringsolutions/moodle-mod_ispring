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

namespace mod_ispring\ispring_module\infrastructure\query;

require_once(__DIR__ . '/../../../testcase/ispring_testcase.php');

final class ispring_module_query_service_test extends \mod_ispring\testcase\ispring_testcase
{
    private ispring_module_query_service $ispring_query_service;

    protected function setUp(): void
    {
        $this->ispring_query_service = new ispring_module_query_service();
    }

    public function test_exists_returns_false_for_nonexistent_id(): void
    {
        $this->assertFalse($this->ispring_query_service->exists(1));
    }

    public function test_exists_returns_true_for_existent_id(): void
    {
        $ispring_instance = $this->create_course_and_instance();

        $this->assertTrue($this->ispring_query_service->exists($ispring_instance->id));
    }

    public function test_get_by_id_returns_null_for_nonexistent_id(): void
    {
        $this->assertNull($this->ispring_query_service->get_by_id(1));
    }

    public function test_get_by_id_returns_module_for_valid_id(): void
    {
        $ispring_instance = $this->create_course_and_instance();

        $ispring_module = $this->ispring_query_service->get_by_id($ispring_instance->id);

        $this->assertEquals($ispring_instance->id, $ispring_module->get_id());
        $this->assertEquals($ispring_instance->name, $ispring_module->get_name());
        $this->assertEquals($ispring_instance->course, $ispring_module->get_moodle_course_id());
        $this->assertEquals($ispring_instance->grade, $ispring_module->get_grade());
        $this->assertEquals($ispring_instance->grademethod, $ispring_module->get_grade_method());
        $this->assertEquals($ispring_instance->intro, $ispring_module->get_description()->get_text());
        $this->assertEquals($ispring_instance->introformat, $ispring_module->get_description()->get_format());
    }
}