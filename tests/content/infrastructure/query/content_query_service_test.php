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
 * @copyright   2023 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ispring\content\infrastructure\query;

use mod_ispring\content\app\query\model\content;

final class content_query_service_test extends \advanced_testcase
{
    private readonly content_query_service $content_query_service;

    protected function setUp(): void
    {
        $this->content_query_service = new content_query_service();
    }

    public function test_get_latest_version_content_returns_null_for_nonexistent_module_id(): void
    {
        $content = $this->content_query_service->get_latest_version_content_by_ispring_module_id(1);

        $this->assertNull($content);
    }

    public function test_get_latest_version_content_returns_content(): void
    {
        $ispring_module_id = 1;
        $expected_content = $this->create_mock_content($ispring_module_id, 1);

        $content = $this->content_query_service->get_latest_version_content_by_ispring_module_id($ispring_module_id);

        $this->assert_content_record_equals_content_model($expected_content, $content);
    }

    public function test_get_latest_version_content_returns_content_with_greatest_version(): void
    {
        $ispring_module_id = 1;
        $this->create_mock_content($ispring_module_id, 1);
        $this->create_mock_content($ispring_module_id, 2);

        $content = $this->content_query_service->get_latest_version_content_by_ispring_module_id($ispring_module_id);

        $this->assertEquals(2, $content->get_version());
    }

    public function test_exists_returns_false_for_nonexistent_id(): void
    {
        $this->assertFalse($this->content_query_service->exists(1));
    }

    public function test_exists_returns_true_for_existent_id(): void
    {
        $content = $this->create_mock_content(1, 1);

        $this->assertTrue($this->content_query_service->exists($content->id));
    }

    public function test_get_by_id_returns_null_for_nonexistent_id(): void
    {
        $this->assertNull($this->content_query_service->get_by_id(1));
    }

    public function test_get_by_id_returns_content_for_valid_id(): void
    {
        $expected_content = $this->create_mock_content(1, 1);

        $content = $this->content_query_service->get_by_id($expected_content->id);

        $this->assert_content_record_equals_content_model($expected_content, $content);
    }

    public function test_get_ids_returns_arrays_with_values(): void
    {
        $expected[] = $this->create_mock_content(1, 1)->id;
        $expected[] = $this->create_mock_content(1, 2)->id;
        $expected[] = $this->create_mock_content(1, 3)->id;
        $this->create_mock_content(2, 1);
        $this->create_mock_content(2, 2);

        $ids = $this->content_query_service->get_ids_by_ispring_module_id(1);

        $this->assertEquals($expected, $ids);
    }

    public function test_get_ids_returns_empty_array(): void
    {
        $this->create_mock_content(1, 1);
        $this->create_mock_content(1, 2);
        $this->create_mock_content(1, 3);
        $this->create_mock_content(2, 1);
        $this->create_mock_content(2, 2);

        $ids = $this->content_query_service->get_ids_by_ispring_module_id(3);

        $this->assertEquals([], $ids);
    }

    private function create_mock_content(int $ispring_module_id, int $version): \stdClass
    {
        global $DB;
        $this->resetAfterTest(true);

        $content = new \stdClass();
        $content->ispring_id = $ispring_module_id;
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

    private function assert_content_record_equals_content_model(\stdClass $record, content $model): void
    {
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