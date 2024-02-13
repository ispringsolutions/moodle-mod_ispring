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

namespace mod_ispring\content\infrastructure;

use mod_ispring\content\app\model\content;
use mod_ispring\content\app\model\file_info;

final class content_repository_test extends \advanced_testcase
{
    private content_repository $content_repository;

    protected function setUp(): void
    {
        $this->content_repository = new content_repository();
    }

    public function test_add_with_report_path(): void
    {
        $this->resetAfterTest();
        $content = new content(
            1,
            2,
            3,
            new file_info('/content_path', 'content.html'),
            6,
            new file_info('/report_path', 'report.html'),
        );

        $id = $this->content_repository->add($content);

        $this->assert_db_record_equals_content_data($content, $id);
    }

    public function test_add_without_report_path(): void
    {
        $this->resetAfterTest();
        $content = new content(
            1,
            2,
            3,
            new file_info('/content_path', 'content.html'),
            6,
            null,
        );

        $id = $this->content_repository->add($content);

        $this->assert_db_record_equals_content_data($content, $id);
    }

    public function test_remove(): void
    {
        global $DB;
        $this->resetAfterTest();

        $id = $DB->insert_record('ispring_content', [
            'filename' => '',
            'creation_time' => 0,
        ]);
        $this->assertTrue(self::content_exists($id));

        $this->content_repository->remove($id);

        $this->assertFalse(self::content_exists($id));
    }

    private function assert_db_record_equals_content_data(content $content, int $content_id): void
    {
        global $DB;
        $content_record = $DB->get_record('ispring_content', ['id' => $content_id]);

        $this->assertEquals($content->get_file_id(), $content_record->file_id);
        $this->assertEquals($content->get_ispring_module_id(), $content_record->ispring_id);
        $this->assertEquals($content->get_creation_time(), $content_record->creation_time);
        $this->assertEquals($content->get_content_path()->get_path(), $content_record->path);
        $this->assertEquals($content->get_content_path()->get_filename(), $content_record->filename);
        $this->assertEquals($content->get_version(), $content_record->version);
        if ($report_path = $content->get_report_path())
        {
            $this->assertEquals($report_path->get_path(), $content_record->report_path);
            $this->assertEquals($report_path->get_filename(), $content_record->report_filename);
        }
    }

    private static function content_exists(int $id): bool
    {
        global $DB;
        return $DB->record_exists('ispring_content', ['id' => $id]);
    }
}