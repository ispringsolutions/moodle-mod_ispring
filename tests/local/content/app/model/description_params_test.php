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
 * Test description_params class.
 *
 * @covers \mod_ispring\local\content\app\model\description_params
 */
final class description_params_test extends \basic_testcase {
    public function test_create_can_parse_valid_v1_params(): void {
        // This test makes sure backward compatibility is not broken
        // DO NOT MODIFY unless you know what you are doing.
        $input = [
            'entrypoint' => 'res/index.html',
            'creation_time' => 1100613,
            'version' => 1,
        ];

        $output = description_params::create($input);

        $this->assertEquals('res/index.html', $output->get_entrypoint());
        $this->assertEquals(1100613, $output->get_creation_time());
        $this->assertEquals(1, $output->get_version());
        $this->assertNull($output->get_report_entrypoint());
    }

    public function test_create_can_parse_v1_optional_parameters(): void {
        // This test makes sure backward compatibility is not broken
        // DO NOT MODIFY unless you know what you are doing.
        $input = [
            'entrypoint' => 'res/index.html',
            'creation_time' => 1100613,
            'version' => 1,
            'report_entrypoint' => 'report/index.html',
        ];

        $output = description_params::create($input);

        $this->assertEquals('report/index.html', $output->get_report_entrypoint());
    }

    public function test_create_returns_null_when_entrypoint_is_missing(): void {
        $input = [
            'creation_time' => 1100613,
            'version' => 1,
        ];

        $this->assertNull(description_params::create($input));
    }

    public function test_create_returns_null_when_creation_time_is_missing(): void {
        $input = [
            'entrypoint' => 'res/index.html',
            'version' => 1,
        ];

        $this->assertNull(description_params::create($input));
    }

    public function test_create_returns_null_when_version_is_missing(): void {
        $input = [
            'entrypoint' => 'res/index.html',
            'creation_time' => 1100613,
        ];

        $this->assertNull(description_params::create($input));
    }
}
