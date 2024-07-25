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

namespace mod_ispring\external;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../ispring_testcase.php');

use mod_ispring\local\common\app\exception\inaccessible_content_exception;
use mod_ispring\ispring_testcase;

/**
 * Test get_player_data class.
 *
 * @covers \mod_ispring\external\get_player_data
 */
final class get_player_data_test extends \advanced_testcase {
    public function test_execute_returns_session_data_for_given_content(): void {
        [$contentid, $instance] = $this->create_content_and_instance();
        self::create_and_set_enrolled_user($instance->course);
        ispring_testcase::create_mock_session([
            'ispring_content_id' => $contentid,
            'persist_state_id' => 'ps_id',
            'persist_state' => 'ps',
            'suspend_data' => 'sd',
        ]);

        $result = get_player_data::execute($contentid);

        self::assertEquals('ps_id', $result['persist_state_id']);
        self::assertEquals('ps', $result['persist_state']);
        self::assertEquals('sd', $result['suspend_data']);
    }

    public function test_execute_returns_data_from_session_with_max_attempt(): void {
        [$contentid, $instance] = $this->create_content_and_instance();
        self::create_and_set_enrolled_user($instance->course);
        ispring_testcase::create_mock_session([
            'ispring_content_id' => $contentid,
            'attempt' => 1,
            'persist_state_id' => 'ps_id',
            'persist_state' => 'ps',
            'suspend_data' => 'sd',
        ]);
        ispring_testcase::create_mock_session([
            'ispring_content_id' => $contentid,
            'attempt' => 2,
            'suspend_data' => 'sd2',
        ]);

        $result = get_player_data::execute($contentid);

        self::assertNull($result['persist_state_id']);
        self::assertNull($result['persist_state']);
        self::assertEquals('sd2', $result['suspend_data']);
    }

    public function test_execute_returns_nulls_if_user_can_preview_content(): void {
        [$contentid] = $this->create_content_and_instance();
        ispring_testcase::create_mock_session([
            'ispring_content_id' => $contentid,
            'persist_state_id' => 'ps_id',
            'persist_state' => 'ps',
            'suspend_data' => 'sd',
        ]);

        $result = get_player_data::execute($contentid);

        self::assertNull($result['persist_state_id']);
        self::assertNull($result['persist_state']);
        self::assertNull($result['suspend_data']);
    }

    public function test_execute_returns_nulls_if_content_has_no_sessions(): void {
        [$contentid, $instance] = $this->create_content_and_instance();
        self::create_and_set_enrolled_user($instance->course);

        $result = get_player_data::execute($contentid);

        self::assertNull($result['persist_state_id']);
        self::assertNull($result['persist_state']);
        self::assertNull($result['suspend_data']);
    }

    public function test_execute_ignores_sessions_with_non_matching_user_ids(): void {
        [$contentid, $instance] = $this->create_content_and_instance();
        self::create_and_set_enrolled_user($instance->course);
        ispring_testcase::create_mock_session([
            'user_id' => self::getDataGenerator()->create_user()->id,
            'ispring_content_id' => $contentid,
            'persist_state_id' => 'ps_id',
            'persist_state' => 'ps',
            'suspend_data' => 'sd',
        ]);

        $result = get_player_data::execute($contentid);

        self::assertNull($result['persist_state_id']);
        self::assertNull($result['persist_state']);
        self::assertNull($result['suspend_data']);
    }

    public function test_execute_ignores_sessions_with_non_matching_content_ids(): void {
        [$contentid, $instance] = $this->create_content_and_instance();
        self::create_and_set_enrolled_user($instance->course);
        ispring_testcase::create_mock_session([
            'ispring_content_id' => $contentid + 1,
            'persist_state_id' => 'ps_id',
            'persist_state' => 'ps',
            'suspend_data' => 'sd',
        ]);

        $result = get_player_data::execute($contentid);

        self::assertNull($result['persist_state_id']);
        self::assertNull($result['persist_state']);
        self::assertNull($result['suspend_data']);
    }

    public function test_execute_throws_exception_if_invoker_cannot_access_content(): void {
        [$contentid] = $this->create_content_and_instance();
        self::setUser(self::getDataGenerator()->create_user());

        self::expectException(inaccessible_content_exception::class);
        get_player_data::execute($contentid);
    }

    public function test_execute_throws_exception_if_content_does_not_exist(): void {
        self::expectException(inaccessible_content_exception::class);
        get_player_data::execute(1);
    }

    private function create_content_and_instance(): array {
        $instance = ispring_testcase::create_course_and_instance($this);

        global $DB;
        $content = $DB->get_record('ispring_content', ['ispring_id' => $instance->id], 'id');
        return [$content->id, $instance];
    }

    private static function create_and_set_enrolled_user(int $courseid): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $courseid);
        self::setUser($user);
    }
}
