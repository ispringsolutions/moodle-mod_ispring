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

namespace mod_ispring;

use advanced_testcase;
use mod_ispring\local\session\domain\model\session_state;

class ispring_testcase {
    public static function create_course(advanced_testcase $testcase): \stdClass {
        $testcase->resetAfterTest();
        return advanced_testcase::getDataGenerator()->create_course();
    }

    public static function create_course_and_instance(advanced_testcase $testcase, array $formdata = null): \stdClass {
        $testcase->setAdminUser();
        $formdata['course'] = self::create_course($testcase);
        return advanced_testcase::getDataGenerator()->create_module('ispring', $formdata);
    }

    public static function create_mock_session($session = []): int {
        global $DB, $USER;

        $session = (array)$session + [
                'user_id' => $USER->id,
                'ispring_content_id' => 4,
                'status' => session_state::INCOMPLETE,
                'score' => 20,
                'begin_time' => 5,
            ];

        return $DB->insert_record('ispring_session', $session);
    }
}
