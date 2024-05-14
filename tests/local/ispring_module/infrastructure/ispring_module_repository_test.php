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

defined('MOODLE_INTERNAL') || die();

use mod_ispring\local\ispring_module\app\data\ispring_module_data;
use mod_ispring\local\ispring_module\app\model\description;
use mod_ispring\local\ispring_module\domain\model\grading_options;

require_once(__DIR__ . '/../../../ispring_testcase.php');

/**
 * Test ispring_module_repository class.
 *
 * @covers \mod_ispring\local\ispring_module\infrastructure\ispring_module_repository
 */
final class ispring_module_repository_test extends \mod_ispring\ispring_testcase {
    private ispring_module_repository $ispringrepository;

    protected function setUp(): void {
        $this->ispringrepository = new ispring_module_repository();
    }

    public function test_add_with_description(): void {
        $courseid = $this->create_course();
        $data = new ispring_module_data(
            'test_add',
            $courseid,
            grading_options::AVERAGE,
            new description('test_add_description', FORMAT_PLAIN),
            time(),
            time() + 3,
        );

        $id = $this->ispringrepository->add($data);

        $this->assert_db_record_equals_module_data($data, $id);
    }

    public function test_add_without_description(): void {
        $courseid = $this->create_course();
        $data = new ispring_module_data(
            'test_add',
            $courseid,
            grading_options::AVERAGE,
            null,
            time(),
            time() + 3,
        );

        $id = $this->ispringrepository->add($data);

        $this->assert_db_record_equals_module_data($data, $id);
    }

    public function test_update(): void {
        $instance = $this->create_course_and_instance();
        $data = new ispring_module_data(
            'test_update',
            $instance->course,
            grading_options::AVERAGE,
            new description('test_update_description', FORMAT_PLAIN),
            time(),
            time() + 3,
        );

        $this->assertTrue($this->ispringrepository->update($instance->id, $data));

        $this->assert_db_record_equals_module_data($data, $instance->id);
    }

    public function test_remove(): void {
        global $DB;
        $this->resetAfterTest();

        $id = $DB->insert_record('ispring', [
            'name' => '',
        ]);
        $this->assertTrue(self::module_exists($id));

        $this->ispringrepository->remove($id);

        $this->assertFalse(self::module_exists($id));
    }

    private function assert_db_record_equals_module_data(ispring_module_data $data, int $ispringid): void {
        global $DB;
        $instance = $DB->get_record('ispring', ['id' => $ispringid]);

        $this->assertEquals($data->get_name(), $instance->name);
        $this->assertEquals($data->get_moodle_course_id(), $instance->course);
        $this->assertEquals($data->get_grade_method(), $instance->grademethod);
        if ($description = $data->get_description()) {
            $this->assertEquals($description->get_text(), $instance->intro);
            $this->assertEquals($description->get_format(), $instance->introformat);
        }
    }

    private static function module_exists(int $id): bool {
        global $DB;
        return $DB->record_exists('ispring', ['id' => $id]);
    }
}
