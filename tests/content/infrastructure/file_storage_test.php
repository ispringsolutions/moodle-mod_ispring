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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../testcase/user_file_creator.php');

use mod_ispring\testcase\user_file_creator;

/**
 * Test file_storage class.
 *
 * @covers \mod_ispring\content\infrastructure\file_storage
 */
final class file_storage_test extends \advanced_testcase {
    private \file_storage $moodlefs;
    private file_storage $filestorage;

    protected function setUp(): void {
        $this->moodlefs = get_file_storage();

        $this->filestorage = new file_storage();
    }

    public function test_unzip_package_creates_files_in_given_storage(): void {
        $target = $this->prepare_to_unzip_package();
        $file = user_file_creator::create_from_path(__DIR__ . '/../../packages/stub.zip');

        $this->filestorage->unzip_package(
            $target->context_id,
            $target->item_id,
            $file->get_contextid(),
            $file->get_itemid(),
        );

        $files = $this->moodlefs->get_area_files(
            $target->context_id,
            'mod_ispring',
            'content',
            $target->item_id,
        );
        $this->assertCount(2, $files);

        $file = array_shift($files);
        $this->assertTrue($file->is_directory());
        $this->assertEquals('/', $file->get_filepath());

        $file = array_shift($files);
        $this->assertFalse($file->is_directory());
        $this->assertEquals('/', $file->get_filepath());
        $this->assertEquals('description.json', $file->get_filename());
    }

    public function test_unzip_package_throws_exception_if_no_files_are_provided(): void {
        global $USER;
        $target = $this->prepare_to_unzip_package();
        $usercontext = \context_user::instance($USER->id);

        $this->expectException(\RuntimeException::class);
        $this->filestorage->unzip_package(
            $target->context_id,
            $target->item_id,
            $usercontext->id,
            file_get_unused_draft_itemid(),
        );
    }

    public function test_unzip_package_throws_exception_if_file_is_not_valid_zip_archive(): void {
        $target = $this->prepare_to_unzip_package();
        $file = user_file_creator::create_from_string('invalid.zip', 'PK');

        $this->expectException(\RuntimeException::class);
        try {
            $this->filestorage->unzip_package(
                $target->context_id,
                $target->item_id,
                $file->get_contextid(),
                $file->get_itemid(),
            );
        } catch (\Throwable $e) {
            $this->resetDebugging();
            $this->assertTrue($this->moodlefs->is_area_empty(
                $target->context_id,
                'mod_ispring',
                'content',
                $target->item_id,
            ));
            throw $e;
        }
    }

    public function test_content_needs_updating_returns_false_if_both_fileareas_are_empty(): void {
        $targetcontext = \context_system::instance();
        $usercontext = $this->prepare_user_context();

        $this->assertFalse($this->filestorage->content_needs_updating(
            $targetcontext->id,
            $usercontext->id,
            file_get_unused_draft_itemid(),
        ));
    }

    public function test_content_needs_updating_returns_true_if_package_filearea_is_empty(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $targetcontext = \context_system::instance();
        $file = user_file_creator::create_from_string('empty', '');

        $this->assertTrue($this->filestorage->content_needs_updating(
            $targetcontext->id,
            $file->get_contextid(),
            $file->get_itemid(),
        ));
    }

    public function test_content_needs_updating_returns_false_if_user_filearea_is_empty(): void {
        $targetcontext = \context_system::instance();
        $usercontext = $this->prepare_user_context();
        $this->create_file_in_ispring_package_area($targetcontext->id);

        $this->assertFalse($this->filestorage->content_needs_updating(
            $targetcontext->id,
            $usercontext->id,
            file_get_unused_draft_itemid(),
        ));
    }

    public function test_content_needs_updating_returns_false_if_user_file_and_current_package_are_the_same(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $targetcontext = \context_system::instance();
        $this->create_file_in_ispring_package_area($targetcontext->id);
        $file = user_file_creator::create_from_path(__DIR__ . '/../../packages/stub.zip');

        $this->assertFalse($this->filestorage->content_needs_updating(
            $targetcontext->id,
            $file->get_contextid(),
            $file->get_itemid(),
        ));
    }

    public function test_content_needs_updating_returns_true_if_user_file_and_current_package_are_different(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $targetcontext = \context_system::instance();
        $this->create_file_in_ispring_package_area($targetcontext->id);
        $file = user_file_creator::create_from_string('empty', '');

        $this->assertTrue($this->filestorage->content_needs_updating(
            $targetcontext->id,
            $file->get_contextid(),
            $file->get_itemid(),
        ));
    }

    public function test_clear_ispring_content_area_removes_data_for_item_id_when_it_is_given(): void {
        $contextid = \context_system::instance()->id;
        $usercontextid = $this->prepare_user_context()->id;
        self::create_file_in_ispring_content_area($contextid, 1);
        self::create_file_in_ispring_content_area($contextid, 2);
        self::create_file_in_ispring_content_area($usercontextid, 1);

        $this->filestorage->clear_ispring_areas($contextid, 1);

        $this->assertTrue($this->moodlefs->is_area_empty($contextid, 'mod_ispring', 'content', 1));
        $this->assertFalse($this->moodlefs->is_area_empty($contextid, 'mod_ispring', 'content', 2));
        $this->assertFalse($this->moodlefs->is_area_empty($usercontextid, 'mod_ispring', 'content', 1));
    }

    public function test_clear_ispring_content_area_removes_data_for_all_item_ids_when_no_item_id_is_given(): void {
        $contextid = \context_system::instance()->id;
        $usercontextid = $this->prepare_user_context()->id;
        self::create_file_in_ispring_content_area($contextid, 1);
        self::create_file_in_ispring_content_area($contextid, 2);
        self::create_file_in_ispring_content_area($usercontextid, 1);

        $this->filestorage->clear_ispring_areas($contextid);

        $this->assertTrue($this->moodlefs->is_area_empty($contextid, 'mod_ispring', 'content', 1));
        $this->assertTrue($this->moodlefs->is_area_empty($contextid, 'mod_ispring', 'content', 2));
        $this->assertFalse($this->moodlefs->is_area_empty($usercontextid, 'mod_ispring', 'content', 1));
    }

    /**
     * Makes the necessary preparations to test {@see unzip_package} function
     * @return \stdClass Function arguments that are the same for all tests
     */
    private function prepare_to_unzip_package(): \stdClass {
        $this->resetAfterTest();
        $this->setAdminUser();

        $target = new \stdClass();
        $target->context_id = \context_system::instance()->id;
        $target->item_id = 1;
        return $target;
    }

    private function prepare_user_context(): \context {
        $this->resetAfterTest();
        $this->setAdminUser();

        global $USER;
        return \context_user::instance($USER->id);
    }

    private function create_file_in_ispring_content_area(int $contextid, int $itemid): void {
        $this->moodlefs->create_file_from_string(
            [
                'contextid' => $contextid,
                'component' => file_storage::COMPONENT_NAME,
                'filearea' => 'content',
                'itemid' => $itemid,
                'filename' => 'empty',
                'filepath' => '/',
            ],
            '',
        );
    }

    private function create_file_in_ispring_package_area(int $contextid): void {
        $this->moodlefs->create_file_from_pathname(
            [
                'contextid' => $contextid,
                'component' => file_storage::COMPONENT_NAME,
                'filearea' => file_storage::PACKAGE_FILEAREA,
                'itemid' => file_storage::PACKAGE_ITEM_ID,
                'filename' => 'empty',
                'filepath' => '/',
            ],
            __DIR__ . '/../../packages/stub.zip',
        );
    }
}
