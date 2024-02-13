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

require_once(__DIR__ . '/../../testcase/user_file_creator.php');

use mod_ispring\testcase\user_file_creator;

final class file_storage_test extends \advanced_testcase
{
    private \file_storage $moodle_fs;
    private file_storage $file_storage;

    protected function setUp(): void
    {
        $this->moodle_fs = get_file_storage();

        $this->file_storage = new file_storage();
    }

    public function test_unzip_package_creates_files_in_given_storage(): void
    {
        $target = $this->prepare_to_unzip_package();
        $file = user_file_creator::create_from_path(__DIR__ . '/../../packages/stub.zip');

        $this->file_storage->unzip_package(
            $target->context_id,
            $target->item_id,
            $file->get_contextid(),
            $file->get_itemid(),
        );

        $files = $this->moodle_fs->get_area_files(
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

    public function test_unzip_package_throws_exception_if_no_files_are_provided(): void
    {
        global $USER;
        $target = $this->prepare_to_unzip_package();
        $user_context = \context_user::instance($USER->id);

        $this->expectException(\RuntimeException::class);
        $this->file_storage->unzip_package(
            $target->context_id,
            $target->item_id,
            $user_context->id,
            file_get_unused_draft_itemid(),
        );
    }

    public function test_unzip_package_throws_exception_if_file_is_not_valid_zip_archive(): void
    {
        $target = $this->prepare_to_unzip_package();
        $file = user_file_creator::create_from_string('invalid.zip', 'PK');

        $this->expectException(\RuntimeException::class);
        try
        {
            $this->file_storage->unzip_package(
                $target->context_id,
                $target->item_id,
                $file->get_contextid(),
                $file->get_itemid(),
            );
        }
        catch (\Throwable $e)
        {
            $this->resetDebugging();
            $this->assertTrue($this->moodle_fs->is_area_empty(
                $target->context_id,
                'mod_ispring',
                'content',
                $target->item_id,
            ));
            throw $e;
        }
    }

    public function test_content_needs_updating_returns_false_if_both_fileareas_are_empty(): void
    {
        $target_context = \context_system::instance();
        $user_context = $this->prepare_user_context();

        $this->assertFalse($this->file_storage->content_needs_updating(
            $target_context->id,
            $user_context->id,
            file_get_unused_draft_itemid(),
        ));
    }

    public function test_content_needs_updating_returns_true_if_package_filearea_is_empty(): void
    {
        $this->resetAfterTest();
        $this->setAdminUser();
        $target_context = \context_system::instance();
        $file = user_file_creator::create_from_string('empty', '');

        $this->assertTrue($this->file_storage->content_needs_updating(
            $target_context->id,
            $file->get_contextid(),
            $file->get_itemid(),
        ));
    }

    public function test_content_needs_updating_returns_false_if_user_filearea_is_empty(): void
    {
        $target_context = \context_system::instance();
        $user_context = $this->prepare_user_context();
        $this->create_file_in_ispring_package_area($target_context->id);

        $this->assertFalse($this->file_storage->content_needs_updating(
            $target_context->id,
            $user_context->id,
            file_get_unused_draft_itemid(),
        ));
    }

    public function test_content_needs_updating_returns_false_if_user_file_and_current_package_are_the_same(): void
    {
        $this->resetAfterTest();
        $this->setAdminUser();
        $target_context = \context_system::instance();
        $this->create_file_in_ispring_package_area($target_context->id);
        $file = user_file_creator::create_from_path(__DIR__ . '/../../packages/stub.zip');

        $this->assertFalse($this->file_storage->content_needs_updating(
            $target_context->id,
            $file->get_contextid(),
            $file->get_itemid(),
        ));
    }

    public function test_content_needs_updating_returns_true_if_user_file_and_current_package_are_different(): void
    {
        $this->resetAfterTest();
        $this->setAdminUser();
        $target_context = \context_system::instance();
        $this->create_file_in_ispring_package_area($target_context->id);
        $file = user_file_creator::create_from_string('empty', '');

        $this->assertTrue($this->file_storage->content_needs_updating(
            $target_context->id,
            $file->get_contextid(),
            $file->get_itemid(),
        ));
    }

    public function test_clear_ispring_content_area_removes_data_for_item_id_when_it_is_given(): void
    {
        $context_id = \context_system::instance()->id;
        $user_context_id = $this->prepare_user_context()->id;
        self::create_file_in_ispring_content_area($context_id, 1);
        self::create_file_in_ispring_content_area($context_id, 2);
        self::create_file_in_ispring_content_area($user_context_id, 1);

        $this->file_storage->clear_ispring_areas($context_id, 1);

        $this->assertTrue($this->moodle_fs->is_area_empty($context_id, 'mod_ispring', 'content', 1));
        $this->assertFalse($this->moodle_fs->is_area_empty($context_id, 'mod_ispring', 'content', 2));
        $this->assertFalse($this->moodle_fs->is_area_empty($user_context_id, 'mod_ispring', 'content', 1));
    }

    public function test_clear_ispring_content_area_removes_data_for_all_item_ids_when_no_item_id_is_given(): void
    {
        $context_id = \context_system::instance()->id;
        $user_context_id = $this->prepare_user_context()->id;
        self::create_file_in_ispring_content_area($context_id, 1);
        self::create_file_in_ispring_content_area($context_id, 2);
        self::create_file_in_ispring_content_area($user_context_id, 1);

        $this->file_storage->clear_ispring_areas($context_id);

        $this->assertTrue($this->moodle_fs->is_area_empty($context_id, 'mod_ispring', 'content', 1));
        $this->assertTrue($this->moodle_fs->is_area_empty($context_id, 'mod_ispring', 'content', 2));
        $this->assertFalse($this->moodle_fs->is_area_empty($user_context_id, 'mod_ispring', 'content', 1));
    }

    /**
     * Makes the necessary preparations to test {@see unzip_package} function
     * @return \stdClass Function arguments that are the same for all tests
     */
    private function prepare_to_unzip_package(): \stdClass
    {
        $this->resetAfterTest();
        $this->setAdminUser();

        $target = new \stdClass();
        $target->context_id = \context_system::instance()->id;
        $target->item_id = 1;
        return $target;
    }

    private function prepare_user_context(): \context
    {
        $this->resetAfterTest();
        $this->setAdminUser();

        global $USER;
        return \context_user::instance($USER->id);
    }

    private function create_file_in_ispring_content_area(int $context_id, int $item_id): void
    {
        $this->moodle_fs->create_file_from_string(
            [
                'contextid' => $context_id,
                'component' => file_storage::COMPONENT_NAME,
                'filearea' => 'content',
                'itemid' => $item_id,
                'filename' => 'empty',
                'filepath' => '/',
            ],
            '',
        );
    }

    private function create_file_in_ispring_package_area(int $context_id): void
    {
        $this->moodle_fs->create_file_from_pathname(
            [
                'contextid' => $context_id,
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