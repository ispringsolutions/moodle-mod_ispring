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

namespace mod_ispring\ispring_module\app\service;

use mod_ispring\ispring_module\app\data\ispring_module_data;
use mod_ispring\ispring_module\app\repository\ispring_module_repository_interface;
use mod_ispring\ispring_module\domain\model\grading_options;

/**
 * Test ispring_module_service class.
 *
 * @covers \mod_ispring\ispring_module\app\service\ispring_module_service
 */
final class ispring_module_service_test extends \basic_testcase {
    /** @var mixed */
    private $ispringmodulerepositorymock;
    private ispring_module_service $ispringmoduleservice;

    protected function setUp(): void {
        $this->ispringmodulerepositorymock = $this->createMock(ispring_module_repository_interface::class);

        $this->ispringmoduleservice = new ispring_module_service($this->ispringmodulerepositorymock);
    }

    public function test_create_adds_module_to_repository(): void {
        $data = new ispring_module_data(
            'test_create',
            2,
            grading_options::FIRST,
            null,
            time(),
            time()
        );

        $this->ispringmodulerepositorymock->expects($this->exactly(0))->method('update');
        $this->ispringmodulerepositorymock->expects($this->exactly(0))->method('remove');
        $this->ispringmodulerepositorymock->expects($this->once())
            ->method('add')
            ->with($this->identicalTo($data))
            ->willReturn(1);

        $id = $this->ispringmoduleservice->create($data);

        $this->assertEquals(1, $id);
    }

    public function test_create_throws_exception_if_grade_option_is_not_valid(): void {
        $this->expectException(\RuntimeException::class);
        $this->ispringmoduleservice->create(
            new ispring_module_data('test_create', 2, 0, null, time(), time()),
        );
    }

    public function test_delete_removes_module_from_repository(): void {
        $ispringmoduleid = 1;

        $this->ispringmodulerepositorymock->expects($this->exactly(0))->method('add');
        $this->ispringmodulerepositorymock->expects($this->exactly(0))->method('update');
        $this->ispringmodulerepositorymock->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($ispringmoduleid));

        $this->ispringmoduleservice->delete($ispringmoduleid);
    }
}
