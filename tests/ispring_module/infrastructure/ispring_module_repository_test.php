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

namespace mod_ispring\ispring_module\infrastructure;

use mod_ispring\ispring_module\app\data\ispring_module_data;
use mod_ispring\ispring_module\app\model\description;
use mod_ispring\ispring_module\domain\model\grading_options;

require_once(__DIR__ . '/../../testcase/ispring_testcase.php');

final class ispring_module_repository_test extends \mod_ispring\testcase\ispring_testcase
{
    private ispring_module_repository $ispring_repository;

    protected function setUp(): void
    {
        $this->ispring_repository = new ispring_module_repository();
    }

    public function test_add_with_description(): void
    {
        $moodle_course_id = $this->create_course();
        $ispring_module = new ispring_module_data(
            'test_add',
            $moodle_course_id,
            grading_options::AVERAGE,
            new description('test_add_description', FORMAT_PLAIN),
            time(),
            time() + 3,
        );

        $id = $this->ispring_repository->add($ispring_module);

        $this->assert_db_record_equals_module_data($ispring_module, $id);
    }

    public function test_add_without_description(): void
    {
        $moodle_course_id = $this->create_course();
        $ispring_module = new ispring_module_data(
            'test_add',
            $moodle_course_id,
            grading_options::AVERAGE,
            null,
            time(),
            time() + 3,
        );

        $id = $this->ispring_repository->add($ispring_module);

        $this->assert_db_record_equals_module_data($ispring_module, $id);
    }

    public function test_update(): void
    {
        $ispring_instance = $this->create_course_and_instance();
        $ispring_module = new ispring_module_data(
            'test_update',
            $ispring_instance->course,
            grading_options::AVERAGE,
            new description('test_update_description', FORMAT_PLAIN),
            time(),
            time() + 3,
        );

        $this->assertTrue($this->ispring_repository->update($ispring_instance->id, $ispring_module));

        $this->assert_db_record_equals_module_data($ispring_module, $ispring_instance->id);
    }

    public function test_remove(): void
    {
        global $DB;
        $this->resetAfterTest();

        $id = $DB->insert_record('ispring', [
            'name' => '',
        ]);
        $this->assertTrue(self::module_exists($id));

        $this->ispring_repository->remove($id);

        $this->assertFalse(self::module_exists($id));
    }

    private function assert_db_record_equals_module_data(ispring_module_data $data, int $ispring_id): void
    {
        global $DB;
        $ispring_instance = $DB->get_record('ispring', ['id' => $ispring_id]);

        $this->assertEquals($data->get_name(), $ispring_instance->name);
        $this->assertEquals($data->get_moodle_course_id(), $ispring_instance->course);
        $this->assertEquals($data->get_grade_method(), $ispring_instance->grademethod);
        if ($description = $data->get_description())
        {
            $this->assertEquals($description->get_text(), $ispring_instance->intro);
            $this->assertEquals($description->get_format(), $ispring_instance->introformat);
        }
    }

    private static function module_exists(int $id): bool
    {
        global $DB;
        return $DB->record_exists('ispring', ['id' => $id]);
    }
}