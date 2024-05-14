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

namespace mod_ispring\local\session\infrastructure;

use mod_ispring\local\session\app\adapter\ispring_module_api_interface;
use mod_ispring\local\session\domain\model\session_state;
use mod_ispring\local\session\infrastructure\query\session_query_service;

/**
 * Test session_repository class.
 *
 * @covers \mod_ispring\local\session\infrastructure\session_repository
 */
class session_repository_test extends \advanced_testcase {
    private session_repository $sessionrepository;
    private session_query_service $sessionqueryservice;

    protected function setUp(): void {
        $ispringmodulestub = $this->createStub(ispring_module_api_interface::class);
        $this->sessionrepository = new session_repository();
        $this->sessionqueryservice = new session_query_service($ispringmodulestub);
    }

    public function test_delete_by_content_id_removes_single_session_with_given_content_id(): void {
        $sessionid = $this->create_mock_session(2, 3);
        $this->create_mock_session(3, 3);

        $this->assertTrue($this->sessionrepository->delete_by_content_id(2));

        $this->assertNull($this->sessionqueryservice->get($sessionid));
    }

    public function test_delete_by_content_id_removes_many_sessions_with_given_content_id(): void {
        $sessionid1 = $this->create_mock_session(2, 3);
        $sessionid2 = $this->create_mock_session(2, 3);
        $sessionid3 = $this->create_mock_session(2, 3);
        $sessionid4 = $this->create_mock_session(4, 3);

        $this->assertTrue($this->sessionrepository->delete_by_content_id(2));

        $this->assertNull($this->sessionqueryservice->get($sessionid1));
        $this->assertNull($this->sessionqueryservice->get($sessionid2));
        $this->assertNull($this->sessionqueryservice->get($sessionid3));

        $this->assertNotNull($this->sessionqueryservice->get($sessionid4));
    }

    public function test_delete_by_content_id_removes_no_session_with_given_content_id(): void {
        $sessionid1 = $this->create_mock_session(2, 3);
        $sessionid2 = $this->create_mock_session(2, 3);
        $sessionid3 = $this->create_mock_session(2, 3);

        $this->assertTrue($this->sessionrepository->delete_by_content_id(3));

        $this->assertNotNull($this->sessionqueryservice->get($sessionid1));
        $this->assertNotNull($this->sessionqueryservice->get($sessionid2));
        $this->assertNotNull($this->sessionqueryservice->get($sessionid3));
    }

    private function create_mock_session(int $contentid, int $userid): int {
        global $DB;
        $this->resetAfterTest(true);

        $session = new \stdClass();
        $session->user_id = $userid;
        $session->ispring_content_id = $contentid;
        $session->status = session_state::COMPLETE;
        $session->begin_time = 5;
        $session->attempt = 0;
        $session->duration = 10;
        $session->persist_state = '_state';
        $session->persist_state_id = '_id';
        $session->max_score = 100;
        $session->min_score = 20;
        $session->passing_score = 60;
        $session->detailed_report = '_report';
        $session->score = 10;
        $session->end_time = 10;
        $session->player_id = '12313';

        return $DB->insert_record('ispring_session', $session);
    }
}
