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

namespace mod_ispring\content\infrastructure\query;

use mod_ispring\content\app\query\model\content;

/**
 * Test content_query_service class.
 *
 * @covers \mod_ispring\content\infrastructure\query\content_query_service
 */
final class content_query_service_test extends \advanced_testcase {
    private content_query_service $contentqueryservice;

    protected function setUp(): void {
        $this->contentqueryservice = new content_query_service();
    }

    public function test_get_latest_version_content_returns_null_for_nonexistent_module_id(): void {
        $content = $this->contentqueryservice->get_latest_version_content_by_ispring_module_id(1);

        $this->assertNull($content);
    }

    public function test_get_latest_version_content_returns_content(): void {
        $ispringmoduleid = 1;
        $expected = $this->create_mock_content($ispringmoduleid, 1);

        $content = $this->contentqueryservice->get_latest_version_content_by_ispring_module_id($ispringmoduleid);

        $this->assert_content_record_equals_content_model($expected, $content);
    }

    public function test_get_latest_version_content_returns_content_with_greatest_version(): void {
        $ispringmoduleid = 1;
        $this->create_mock_content($ispringmoduleid, 1);
        $this->create_mock_content($ispringmoduleid, 2);

        $content = $this->contentqueryservice->get_latest_version_content_by_ispring_module_id($ispringmoduleid);

        $this->assertEquals(2, $content->get_version());
    }

    public function test_exists_returns_false_for_nonexistent_id(): void {
        $this->assertFalse($this->contentqueryservice->exists(1));
    }

    public function test_exists_returns_true_for_existent_id(): void {
        $content = $this->create_mock_content(1, 1);

        $this->assertTrue($this->contentqueryservice->exists($content->id));
    }

    public function test_get_by_id_returns_null_for_nonexistent_id(): void {
        $this->assertNull($this->contentqueryservice->get_by_id(1));
    }

    public function test_get_by_id_returns_content_for_valid_id(): void {
        $expected = $this->create_mock_content(1, 1);

        $content = $this->contentqueryservice->get_by_id($expected->id);

        $this->assert_content_record_equals_content_model($expected, $content);
    }

    public function test_get_ids_returns_arrays_with_values(): void {
        $expected[] = $this->create_mock_content(1, 1)->id;
        $expected[] = $this->create_mock_content(1, 2)->id;
        $expected[] = $this->create_mock_content(1, 3)->id;
        $this->create_mock_content(2, 1);
        $this->create_mock_content(2, 2);

        $ids = $this->contentqueryservice->get_ids_by_ispring_module_id(1);

        $this->assertEquals($expected, $ids);
    }

    public function test_get_ids_returns_empty_array(): void {
        $this->create_mock_content(1, 1);
        $this->create_mock_content(1, 2);
        $this->create_mock_content(1, 3);
        $this->create_mock_content(2, 1);
        $this->create_mock_content(2, 2);

        $ids = $this->contentqueryservice->get_ids_by_ispring_module_id(3);

        $this->assertEquals([], $ids);
    }

    private function create_mock_content(int $ispringmoduleid, int $version): \stdClass {
        global $DB;
        $this->resetAfterTest(true);

        $content = new \stdClass();
        $content->ispring_id = $ispringmoduleid;
        $content->file_id = 3;
        $content->creation_time = 0;
        $content->filename = 'index.html';
        $content->filepath = '/';
        $content->version = $version;
        $content->report_path = '/report';
        $content->report_filename = 'report.html';

        $content->id = $DB->insert_record('ispring_content', $content);
        return $content;
    }

    private function assert_content_record_equals_content_model(\stdClass $record, content $model): void {
        $this->assertEquals($record->id, $model->get_id());
        $this->assertEquals($record->file_id, $model->get_file_id());
        $this->assertEquals($record->ispring_id, $model->get_ispring_module_id());
        $this->assertEquals($record->creation_time, $model->get_creation_time());
        $this->assertEquals($record->filename, $model->get_filename());
        $this->assertEquals($record->filepath, $model->get_filepath());
        $this->assertEquals($record->version, $model->get_version());
        $this->assertEquals($record->report_path, $model->get_report_path());
        $this->assertEquals($record->report_filename, $model->get_report_filename());
    }
}
