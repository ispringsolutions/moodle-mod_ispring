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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../../user_file_creator.php');

use mod_ispring\local\content\app\exception\invalid_description_exception;
use mod_ispring\local\content\app\exception\unsupported_content_exception;
use mod_ispring\user_file_creator;

/**
 * Test description_parser class.
 *
 * @covers \mod_ispring\content\app\model\description_parser
 */
final class description_parser_test extends \advanced_testcase {
    public function test_parse_can_parse_valid_v1_description(): void {
        // This test makes sure backward compatibility is not broken
        // DO NOT MODIFY unless you know what you are doing.
        $file = $this->create_description_file(
            '{"course_name":"Stub","description":"Stub description",'
            . '"params":{"entrypoint":"index.html","creation_time":42,"version":1}}',
        );

        $this->assertIsObject(description_parser::parse($file));
    }

    public function test_parse_throws_exception_if_description_is_not_valid_json(): void {
        $file = $this->create_description_file('');

        $this->expectException(invalid_description_exception::class);
        description_parser::parse($file);
    }

    public function test_parse_throws_exception_if_description_is_invalid(): void {
        $file = $this->create_description_file('{}');

        $this->expectException(invalid_description_exception::class);
        description_parser::parse($file);
    }

    public function test_parse_throws_exception_if_version_is_too_new(): void {
        $file = $this->create_description_file(
            '{"course_name":"Stub","description":"Stub description",'
            . '"params":{"entrypoint":"index.html","creation_time":42,"version":2}}'
        );

        $this->expectException(unsupported_content_exception::class);
        description_parser::parse($file);
    }

    private function create_description_file(string $content): \stored_file {
        $this->resetAfterTest();
        $this->setAdminUser();
        return user_file_creator::create_from_string('description.json', $content);
    }
}
