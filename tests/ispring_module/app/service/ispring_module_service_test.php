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

namespace mod_ispring\ispring_module\app\service;

use mod_ispring\ispring_module\app\data\ispring_module_data;
use mod_ispring\ispring_module\app\repository\ispring_module_repository_interface;
use mod_ispring\ispring_module\domain\model\grading_options;

final class ispring_module_service_test extends \basic_testcase
{
    private readonly mixed $ispring_module_repository_mock;
    private readonly ispring_module_service $ispring_module_service;

    protected function setUp(): void
    {
        $this->ispring_module_repository_mock = $this->createMock(ispring_module_repository_interface::class);

        $this->ispring_module_service = new ispring_module_service($this->ispring_module_repository_mock);
    }

    public function test_create_adds_module_to_repository(): void
    {
        $ispring_module = new ispring_module_data('test_create', 2, grading_options::FIRST->value, null);

        $this->ispring_module_repository_mock->expects($this->exactly(0))->method('update');
        $this->ispring_module_repository_mock->expects($this->exactly(0))->method('remove');
        $this->ispring_module_repository_mock->expects($this->once())
            ->method('add')
            ->with($this->identicalTo($ispring_module))
            ->willReturn(1);

        $id = $this->ispring_module_service->create($ispring_module);

        $this->assertEquals(1, $id);
    }

    public function test_create_throws_exception_if_grade_option_is_not_valid(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->ispring_module_service->create(
            new ispring_module_data('test_create', 2, 0, null),
        );
    }

    public function test_delete_removes_module_from_repository(): void
    {
        $ispring_module_id = 1;

        $this->ispring_module_repository_mock->expects($this->exactly(0))->method('add');
        $this->ispring_module_repository_mock->expects($this->exactly(0))->method('update');
        $this->ispring_module_repository_mock->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($ispring_module_id));

        $this->ispring_module_service->delete($ispring_module_id);
    }
}