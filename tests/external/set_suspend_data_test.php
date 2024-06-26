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

use mod_ispring\local\common\app\exception\inaccessible_session_exception;
use mod_ispring\ispring_testcase;

/**
 * Test set_suspend_data class.
 *
 * @covers \mod_ispring\external\set_suspend_data
 */
final class set_suspend_data_test extends \advanced_testcase {
    private const STARTUP_PLAYER_ID = '1234';
    private const CONFLICTING_PLAYER_ID = '4321';
    private const STARTUP_SUSPEND_DATA = 'initial';
    private const UPDATED_SUSPEND_DATA = 'updated';

    private int $sessionid;
    private ?\stdClass $session = null;

    protected function tearDown(): void {
        global $DB;
        $record = $DB->get_record('ispring_session', ['id' => $this->sessionid]);
        self::assertEquals($this->session, $record);
    }

    public function test_execute_updates_session_if_current_user_and_player_are_valid(): void {
        // This test makes sure backward compatibility is not broken
        // DO NOT MODIFY unless you know what you are doing.
        $this->start_mock_session();

        $result = set_suspend_data::execute($this->sessionid, self::STARTUP_PLAYER_ID, self::UPDATED_SUSPEND_DATA);

        self::assertEmpty($result['warning']);
        $this->session->suspend_data = self::UPDATED_SUSPEND_DATA;
    }

    public function test_execute_throws_exception_if_session_does_not_belong_to_invoker(): void {
        $this->start_mock_session();
        self::setUser(self::getDataGenerator()->create_user());

        self::expectException(inaccessible_session_exception::class);
        set_suspend_data::execute($this->sessionid, self::STARTUP_PLAYER_ID, self::UPDATED_SUSPEND_DATA);
    }

    public function test_execute_throws_exception_if_session_does_not_exist(): void {
        $this->resetAfterTest();
        $this->sessionid = 1;

        $this->expectException(inaccessible_session_exception::class);
        set_suspend_data::execute($this->sessionid, self::STARTUP_PLAYER_ID, self::UPDATED_SUSPEND_DATA);
    }

    public function test_execute_generates_warning_if_player_id_has_changed(): void {
        $this->start_mock_session();

        $result = set_suspend_data::execute($this->sessionid, self::CONFLICTING_PLAYER_ID, self::UPDATED_SUSPEND_DATA);

        $warnings = $result['warning'];
        self::assertCount(1, $warnings);
        self::assertEquals(external_base::ERROR_CODE_INVALID_PLAYER_ID, $warnings[0]['warningcode']);
    }

    public function test_execute_makes_no_changes_if_session_is_review_session(): void {
        $this->sessionid = external_base::REVIEW_SESSION_ID;

        $result = set_suspend_data::execute($this->sessionid, self::STARTUP_PLAYER_ID, self::UPDATED_SUSPEND_DATA);

        self::assertEmpty($result['warning']);
    }

    private function start_mock_session(): void {
        global $DB;
        $this->resetAfterTest();

        $this->sessionid = ispring_testcase::create_mock_session([
            'player_id' => self::STARTUP_PLAYER_ID,
            'suspend_data' => self::STARTUP_SUSPEND_DATA,
        ]);

        $this->session = $DB->get_record('ispring_session', ['id' => $this->sessionid]);
    }
}
