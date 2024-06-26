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

namespace mod_ispring\local\content\app\model;

/**
 * Test description class.
 *
 * @covers \mod_ispring\local\content\app\model\description
 */
final class description_test extends \basic_testcase {
    private const PARAMS = [
        'entrypoint' => 'res/index.html',
        'creation_time' => 1100613,
        'version' => 1,
    ];

    public function test_create_can_parse_valid_v1_description(): void {
        // This test makes sure backward compatibility is not broken
        // DO NOT MODIFY unless you know what you are doing.
        $input = [
            'course_name' => 'Stub',
            'description' => 'Stub description',
            'params' => self::PARAMS,
        ];

        $output = description::create($input);

        $this->assertEquals('Stub', $output->get_content_name());
        $this->assertEquals('Stub description', $output->get_description());
        $this->assertIsObject($output->get_description_params());
    }

    public function test_create_returns_null_when_course_name_is_missing(): void {
        $input = [
            'description' => 'Stub description',
            'params' => self::PARAMS,
        ];

        $this->assertNull(description::create($input));
    }

    public function test_create_returns_null_when_description_is_missing(): void {
        $input = [
            'course_name' => 'Stub',
            'params' => self::PARAMS,
        ];

        $this->assertNull(description::create($input));
    }

    public function test_create_returns_null_when_params_are_missing(): void {
        $input = [
            'course_name' => 'Stub',
            'description' => 'Stub description',
        ];

        $this->assertNull(description::create($input));
    }

    public function test_create_returns_null_when_params_are_invalid(): void {
        $input = [
            'course_name' => 'Stub',
            'description' => 'Stub description',
            'params' => [],
        ];

        $this->assertNull(description::create($input));
    }
}
