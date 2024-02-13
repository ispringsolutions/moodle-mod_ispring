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

namespace mod_ispring\content\app\service;

require_once(__DIR__ . '/../../../testcase/user_file_creator.php');

use mod_ispring\content\app\adapter\ispring_module_api_interface;
use mod_ispring\content\app\data\content_data;
use mod_ispring\content\app\model\content;
use mod_ispring\content\app\model\description;
use mod_ispring\content\app\model\file_info;
use mod_ispring\content\app\query\content_query_service_interface;
use mod_ispring\content\app\repository\content_repository_interface;
use mod_ispring\testcase\user_file_creator;

final class content_service_test extends \advanced_testcase
{
    /** @var mixed */
    private $file_storage_mock;
    /** @var mixed */
    private $content_repository_mock;
    private content_service $content_service;

    protected function setUp(): void
    {
        $this->file_storage_mock = $this->createMock(file_storage_interface::class);
        $this->content_repository_mock = $this->createMock(content_repository_interface::class);
        $ispring_module_api_stub = $this->createStub(ispring_module_api_interface::class);
        $content_query_service_stub = $this->createStub(content_query_service_interface::class);

        $ispring_module_api_stub->method('exists')->willReturn(true);

        $this->content_service = new content_service(
            $this->file_storage_mock,
            $this->content_repository_mock,
            $ispring_module_api_stub,
            $content_query_service_stub,
        );
    }

    public function test_add_content_unzips_package_and_adds_content_to_repository(): void
    {
        $content = $this->create_stub_content();

        $this->file_storage_mock->expects($this->exactly(0))->method('clear_ispring_areas');
        $this->file_storage_mock->expects($this->once())
            ->method('unzip_package')
            ->with(
                $this->identicalTo($content->get_context_id()),
                $this->identicalTo($content->get_file_id()),
                $this->identicalTo($content->get_user_context_id()),
                $this->identicalTo($content->get_file_id()),
            );
        $this->file_storage_mock->expects($this->once())
            ->method('get_description_file')
            ->with(
                $this->identicalTo($content->get_context_id()),
                $this->identicalTo($content->get_file_id()),
                $this->identicalTo(description::FILENAME),
            )
            ->willReturn(user_file_creator::create_from_string(
                description::FILENAME,
                '{"course_name":"Stub","description":"Stub description",'
                . '"params":{"entrypoint":"index.html","creation_time":42}}',
            ));

        $this->content_repository_mock->expects($this->exactly(0))->method('remove');
        $this->content_repository_mock->expects($this->once())
            ->method('add')
            ->with($this->equalTo(new content(
                $content->get_file_id(),
                $content->get_ispring_module_id(),
                42,
                new file_info('/.', 'index.html'),
                1,
                null,
            )))
            ->willReturn(3);

        $id = $this->content_service->add_content($content);

        $this->assertEquals(3, $id);
    }

    public function test_add_content_removes_files_if_get_description_throws_exception(): void
    {
        $content = $this->create_stub_content();

        $this->file_storage_mock->expects($this->once())
            ->method('get_description_file')
            ->willThrowException(new \RuntimeException('bad_description'));
        $this->file_storage_mock->expects($this->once())
            ->method('clear_ispring_areas')
            ->with(
                $this->identicalTo($content->get_context_id()),
                $this->identicalTo($content->get_file_id()),
            );

        $this->expectException(\RuntimeException::class);
        $this->content_service->add_content($content);
    }

    public function test_add_content_removes_files_if_description_is_not_valid(): void
    {
        $content = $this->create_stub_content();

        $this->file_storage_mock->expects($this->once())
            ->method('get_description_file')
            ->willReturn(
                user_file_creator::create_from_string(description::FILENAME, '{}'),
            );
        $this->file_storage_mock->expects($this->once())
            ->method('clear_ispring_areas')
            ->with(
                $this->identicalTo($content->get_context_id()),
                $this->identicalTo($content->get_file_id()),
            );

        $this->expectException(\RuntimeException::class);
        $this->content_service->add_content($content);
    }

    private function create_stub_content(): content_data
    {
        $this->resetAfterTest();
        $this->setAdminUser();
        $file = user_file_creator::create_from_path(__DIR__ . '/../../../packages/stub.zip');

        global $USER;
        $context = \context_system::instance();
        $user_context = \context_user::instance($USER->id);

        return new content_data($file->get_itemid(), 2, $context->id, $user_context->id);
    }
}