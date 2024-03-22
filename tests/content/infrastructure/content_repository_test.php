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

/**
 * Test content_repository class.
 *
 * @covers \mod_ispring\content\infrastructure\content_repository
 */
final class content_repository_test extends \advanced_testcase {
    private content_repository $contentrepository;

    protected function setUp(): void {
        $this->contentrepository = new content_repository();
    }

    public function test_add_with_report_path(): void {
        $this->resetAfterTest();
        $content = new content(
            1,
            2,
            3,
            new file_info('/content_path', 'content.html'),
            6,
            new file_info('/report_path', 'report.html'),
        );

        $id = $this->contentrepository->add($content);

        $this->assert_db_record_equals_content_data($content, $id);
    }

    public function test_add_without_report_path(): void {
        $this->resetAfterTest();
        $content = new content(
            1,
            2,
            3,
            new file_info('/content_path', 'content.html'),
            6,
            null,
        );

        $id = $this->contentrepository->add($content);

        $this->assert_db_record_equals_content_data($content, $id);
    }

    public function test_remove(): void {
        global $DB;
        $this->resetAfterTest();

        $id = $DB->insert_record('ispring_content', [
            'filename' => '',
            'creation_time' => 0,
        ]);
        $this->assertTrue(self::content_exists($id));

        $this->contentrepository->remove($id);

        $this->assertFalse(self::content_exists($id));
    }

    private function assert_db_record_equals_content_data(content $content, int $contentid): void {
        global $DB;
        $record = $DB->get_record('ispring_content', ['id' => $contentid]);

        $this->assertEquals($content->get_file_id(), $record->file_id);
        $this->assertEquals($content->get_ispring_module_id(), $record->ispring_id);
        $this->assertEquals($content->get_creation_time(), $record->creation_time);
        $this->assertEquals($content->get_content_path()->get_path(), $record->path);
        $this->assertEquals($content->get_content_path()->get_filename(), $record->filename);
        $this->assertEquals($content->get_version(), $record->version);
        if ($fileinfo = $content->get_report_path()) {
            $this->assertEquals($fileinfo->get_path(), $record->report_path);
            $this->assertEquals($fileinfo->get_filename(), $record->report_filename);
        }
    }

    private static function content_exists(int $id): bool {
        global $DB;
        return $DB->record_exists('ispring_content', ['id' => $id]);
    }
}
