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

namespace mod_ispring\local\content\app\service;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../../user_file_creator.php');

use mod_ispring\local\content\app\adapter\ispring_module_api_interface;
use mod_ispring\local\content\app\data\content_data;
use mod_ispring\local\content\app\model\content;
use mod_ispring\local\content\app\model\description;
use mod_ispring\local\content\app\model\file_info;
use mod_ispring\local\content\app\query\content_query_service_interface;
use mod_ispring\local\content\app\repository\content_repository_interface;
use mod_ispring\user_file_creator;

/**
 * Test content_service class.
 *
 * @covers \mod_ispring\local\content\app\service\content_service
 */
final class content_service_test extends \advanced_testcase {
    /** @var mixed */
    private $filestoragemock;
    /** @var mixed */
    private $contentrepositorymock;
    private content_service $contentservice;

    protected function setUp(): void {
        $this->filestoragemock = $this->createMock(file_storage_interface::class);
        $this->contentrepositorymock = $this->createMock(content_repository_interface::class);
        $ispringmoduleapistub = $this->createStub(ispring_module_api_interface::class);
        $contentqueryservicestub = $this->createStub(content_query_service_interface::class);

        $ispringmoduleapistub->method('exists')->willReturn(true);

        $this->contentservice = new content_service(
            $this->filestoragemock,
            $this->contentrepositorymock,
            $ispringmoduleapistub,
            $contentqueryservicestub,
        );
    }

    public function test_add_content_unzips_package_and_adds_content_to_repository(): void {
        $content = $this->create_stub_content();

        $this->filestoragemock->expects($this->exactly(0))->method('clear_ispring_areas');
        $this->filestoragemock->expects($this->once())
            ->method('unzip_package')
            ->with(
                $this->identicalTo($content->get_context_id()),
                $this->identicalTo($content->get_file_id()),
                $this->identicalTo($content->get_user_context_id()),
                $this->identicalTo($content->get_file_id()),
            );
        $this->filestoragemock->expects($this->once())
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

        $this->contentrepositorymock->expects($this->exactly(0))->method('remove');
        $this->contentrepositorymock->expects($this->once())
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

        $id = $this->contentservice->add_content($content);

        $this->assertEquals(3, $id);
    }

    public function test_add_content_removes_files_if_get_description_throws_exception(): void {
        $content = $this->create_stub_content();

        $this->filestoragemock->expects($this->once())
            ->method('get_description_file')
            ->willThrowException(new \RuntimeException('bad_description'));
        $this->filestoragemock->expects($this->once())
            ->method('clear_ispring_areas')
            ->with(
                $this->identicalTo($content->get_context_id()),
                $this->identicalTo($content->get_file_id()),
            );

        $this->expectException(\RuntimeException::class);
        $this->contentservice->add_content($content);
    }

    public function test_add_content_removes_files_if_description_is_not_valid(): void {
        $content = $this->create_stub_content();

        $this->filestoragemock->expects($this->once())
            ->method('get_description_file')
            ->willReturn(
                user_file_creator::create_from_string(description::FILENAME, '{}'),
            );
        $this->filestoragemock->expects($this->once())
            ->method('clear_ispring_areas')
            ->with(
                $this->identicalTo($content->get_context_id()),
                $this->identicalTo($content->get_file_id()),
            );

        $this->expectException(\RuntimeException::class);
        $this->contentservice->add_content($content);
    }

    private function create_stub_content(): content_data {
        $this->resetAfterTest();
        $this->setAdminUser();
        $file = user_file_creator::create_from_path(__DIR__ . '/../../../../packages/stub.zip');

        global $USER;
        $context = \context_system::instance();
        $usercontext = \context_user::instance($USER->id);

        return new content_data($file->get_itemid(), 2, $context->id, $usercontext->id);
    }
}
