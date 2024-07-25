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

use mod_ispring\local\content\api\content_api_interface;
use mod_ispring\local\di_container;
use mod_ispring\local\common\app\exception\inaccessible_content_exception;
use mod_ispring\local\common\app\exception\inaccessible_session_exception;
use mod_ispring\ispring_testcase;

/**
 * Test end_session class.
 *
 * @covers \mod_ispring\external\end_session
 */
final class end_session_test extends \advanced_testcase {
    private const STARTUP_PLAYER_ID = '1234';
    private const CONFLICTING_PLAYER_ID = '4321';

    private static ?content_api_interface $contentapi = null;

    private int $sessionid;
    private ?\stdClass $session = null;

    public static function setUpBeforeClass(): void {
        self::$contentapi = di_container::get_content_api();
    }

    public static function tearDownAfterClass(): void {
        self::$contentapi = null;
    }

    protected function tearDown(): void {
        self::assert_db_record_matches_session($this->session, $this->sessionid);
    }

    public function test_execute_throws_exception_if_session_does_not_belong_to_invoker(): void {
        $this->create_content_and_start_session();
        self::setUser(self::getDataGenerator()->create_user());

        self::expectException(inaccessible_session_exception::class);
        end_session::execute($this->sessionid, self::make_state(self::STARTUP_PLAYER_ID));
    }

    public function test_execute_throws_exception_if_session_does_not_exist(): void {
        $this->sessionid = 1;

        self::expectException(inaccessible_session_exception::class);
        end_session::execute($this->sessionid, self::make_state(self::STARTUP_PLAYER_ID));
    }

    public function test_execute_throws_exception_if_content_does_not_exist(): void {
        $this->start_mock_session();

        self::expectException(inaccessible_content_exception::class);
        end_session::execute($this->sessionid, self::make_state(self::STARTUP_PLAYER_ID));
    }

    public function test_execute_throws_exception_if_state_is_invalid(): void {
        $this->create_content_and_start_session();

        self::expectException(\invalid_parameter_exception::class);
        end_session::execute($this->sessionid, '');
    }

    public function test_execute_generates_warning_if_player_id_has_changed(): void {
        $this->create_content_and_start_session();

        $result = end_session::execute($this->sessionid, self::make_state(self::CONFLICTING_PLAYER_ID));

        $warnings = $result['warning'];
        self::assertCount(1, $warnings);
        self::assertEquals(external_base::ERROR_CODE_INVALID_PLAYER_ID, $warnings[0]['warningcode']);
    }

    public function test_execute_makes_no_changes_if_session_is_review_session(): void {
        $this->sessionid = external_base::REVIEW_SESSION_ID;

        $result = end_session::execute($this->sessionid, '');

        self::assertEmpty($result['warning']);
    }

    public function test_execute_updates_session_if_current_user_and_state_are_valid(): void {
        // This test makes sure backward compatibility is not broken
        // DO NOT MODIFY unless you know what you are doing.
        $this->create_content_and_start_session();

        $result = end_session::execute($this->sessionid, self::make_state(self::STARTUP_PLAYER_ID));

        self::assertEmpty($result['warning']);

        $this->session->status = 'incomplete';
        $this->session->has_end_time = true;
        $this->session->duration = 10;
        $this->session->persist_state_id = '_id';
        $this->session->persist_state = '"_ps"';
        $this->session->max_score = 105;
        $this->session->min_score = 35;
        $this->session->passing_score = 65;
        $this->session->score = 85;
        $this->session->detailed_report = '"_report"';
    }

    private static function make_state(string $playerid): string {
        return '{"duration":10,"id":"_id","persistState":"_ps","status":"incomplete","playerId":"' . $playerid . '",'
            . '"maxScore":105,"minScore":35,"passingScore":65,"score":85,"detailedReport":"_report"}';
    }

    private function create_content_and_start_session(): void {
        $instance = ispring_testcase::create_course_and_instance($this);
        $content = self::$contentapi->get_latest_version_content_by_ispring_module_id($instance->id);
        $this->start_mock_session($content->get_id());
    }

    private function start_mock_session(int $ispringcontentid = 4): void {
        global $DB;
        $this->resetAfterTest();

        $this->sessionid = ispring_testcase::create_mock_session([
            'ispring_content_id' => $ispringcontentid,
            'status' => '',
            'score' => 0,
            'duration' => 0,
            'persist_state' => '',
            'persist_state_id' => '',
            'max_score' => 0,
            'min_score' => 0,
            'passing_score' => 0,
            'detailed_report' => '',
            'player_id' => self::STARTUP_PLAYER_ID,
        ]);

        $this->session = $DB->get_record('ispring_session', ['id' => $this->sessionid]);
    }

    private static function assert_db_record_matches_session(?\stdClass $session, int $sessionid): void {
        global $DB;
        $record = $DB->get_record('ispring_session', ['id' => $sessionid]);

        if (!$session) {
            self::assertFalse($record);
            return;
        }

        self::assertCount(17, (array)$record);
        self::assertEquals($session->id, $record->id);
        self::assertEquals($session->user_id, $record->user_id);
        self::assertEquals($session->ispring_content_id, $record->ispring_content_id);
        self::assertEquals($session->status, $record->status);
        self::assertEquals($session->score, $record->score);
        self::assertEquals($session->begin_time, $record->begin_time);
        self::assertEquals($session->attempt, $record->attempt);
        if (empty($session->has_end_time)) {
            self::assertNull($record->end_time);
        } else {
            self::assertNotNull($record->end_time);
        }
        self::assertEquals($session->duration, $record->duration);
        self::assertEquals($session->persist_state, $record->persist_state);
        self::assertEquals($session->persist_state_id, $record->persist_state_id);
        self::assertEquals($session->max_score, $record->max_score);
        self::assertEquals($session->min_score, $record->min_score);
        self::assertEquals($session->passing_score, $record->passing_score);
        self::assertEquals($session->detailed_report, $record->detailed_report);
        self::assertEquals($session->player_id, $record->player_id);
        self::assertEquals($session->suspend_data, $record->suspend_data);
    }
}
