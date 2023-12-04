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

namespace mod_ispring\session\infrastructure;

use mod_ispring\session\app\adapter\ispring_module_api_interface;
use mod_ispring\session\domain\model\session_state;
use mod_ispring\session\infrastructure\query\session_query_service;

class session_repository_test extends \advanced_testcase
{
    private readonly mixed $ispring_module_stub;
    private readonly session_repository $session_repository;
    private readonly session_query_service $session_query_service;

    protected function setUp(): void
    {
        $this->ispring_module_stub = $this->createStub(ispring_module_api_interface::class);
        $this->session_repository = new session_repository();
        $this->session_query_service = new session_query_service($this->ispring_module_stub);
    }

    public function test_delete_by_content_id_removes_single_session_with_given_content_id(): void
    {
        $session_id = $this->create_mock_session(2, 3);
        $this->create_mock_session(3, 3);

        $this->assertTrue($this->session_repository->delete_by_content_id(2));

        $this->assertNull($this->session_query_service->get($session_id));
    }

    public function test_delete_by_content_id_removes_many_sessions_with_given_content_id(): void
    {
        $session_id_1 = $this->create_mock_session(2, 3);
        $session_id_2 = $this->create_mock_session(2, 3);
        $session_id_3 = $this->create_mock_session(2, 3);
        $session_id_4 = $this->create_mock_session(4, 3);

        $this->assertTrue($this->session_repository->delete_by_content_id(2));

        $this->assertNull($this->session_query_service->get($session_id_1));
        $this->assertNull($this->session_query_service->get($session_id_2));
        $this->assertNull($this->session_query_service->get($session_id_3));

        $this->assertNotNull($this->session_query_service->get($session_id_4));
    }

    public function test_delete_by_content_id_removes_no_session_with_given_content_id(): void
    {
        $session_id_1 = $this->create_mock_session(2, 3);
        $session_id_2 = $this->create_mock_session(2, 3);
        $session_id_3 = $this->create_mock_session(2, 3);

        $this->assertTrue($this->session_repository->delete_by_content_id(3));

        $this->assertNotNull($this->session_query_service->get($session_id_1));
        $this->assertNotNull($this->session_query_service->get($session_id_2));
        $this->assertNotNull($this->session_query_service->get($session_id_3));
    }

    private function create_mock_session(int $content_id, int $user_id): int
    {
        global $DB;
        $this->resetAfterTest(true);

        $session = new \stdClass();
        $session->user_id = $user_id;
        $session->ispring_content_id = $content_id;
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

        return $DB->insert_record('ispring_session', $session);
    }
}