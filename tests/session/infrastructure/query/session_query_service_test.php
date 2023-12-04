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

namespace mod_ispring\session\infrastructure\query;

use mod_ispring\ispring_module\domain\model\grading_options;
use mod_ispring\session\app\adapter\ispring_module_api_interface;
use mod_ispring\session\app\query\model\session;
use mod_ispring\session\domain\model\session_state;

final class session_query_service_test extends \advanced_testcase
{
    private readonly mixed $ispring_module_stub;
    private readonly session_query_service $session_query_service;

    protected function setUp(): void
    {
        $this->ispring_module_stub = $this->createStub(ispring_module_api_interface::class);

        $this->session_query_service = new session_query_service($this->ispring_module_stub);
    }

    public function test_get_returns_null_for_nonexistent_id(): void
    {
        $this->assertNull($this->session_query_service->get(1));
    }

    public function test_get_returns_session_for_valid_id(): void
    {
        $session_id = $this->create_mock_sessions(2, 3, [
            ['score' => 80, 'end_time' => 100],
        ])[0];

        $session = $this->session_query_service->get($session_id);

        $this->assert_db_record_equals_session_model($session_id, $session);
    }

    public function test_get_grades_returns_highest_grade_when_grade_method_is_set_to_highest(): void
    {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispring_module_stub->method('get_grade_method')->willReturn(grading_options::HIGHEST->value);

        $grades = $this->session_query_service->get_grades_for_gradebook(0, 2, 1);

        $this->assertCount(1, $grades);
        $this->assert_grade_equals(1, 25, 20, $grades[1]);
    }

    public function test_get_grades_returns_highest_grades_for_all_users(): void
    {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispring_module_stub->method('get_grade_method')->willReturn(grading_options::HIGHEST->value);

        $grades = $this->session_query_service->get_grades_for_gradebook(0, 2, 0);

        $this->assertCount(2, $grades);
        $this->assert_grade_equals(1, 25, 20, $grades[1]);
        $this->assert_grade_equals(2, 60, 100, $grades[2]);
    }

    public function test_get_grades_returns_average_grade_when_grade_method_is_set_to_average(): void
    {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispring_module_stub->method('get_grade_method')->willReturn(grading_options::AVERAGE->value);

        $grades = $this->session_query_service->get_grades_for_gradebook(0, 2, 1);

        $this->assertCount(1, $grades);
        $this->assert_grade_equals(1, -10, null, $grades[1]);
    }

    public function test_get_grades_returns_average_grades_for_all_users(): void
    {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispring_module_stub->method('get_grade_method')->willReturn(grading_options::AVERAGE->value);

        $grades = $this->session_query_service->get_grades_for_gradebook(0, 2, 0);

        $this->assertCount(2, $grades);
        $this->assert_grade_equals(1, -10, null, $grades[1]);
        $this->assert_grade_equals(2, 60, null, $grades[2]);
    }

    public function test_get_grades_returns_first_grade_when_grade_method_is_set_to_first(): void
    {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispring_module_stub->method('get_grade_method')->willReturn(grading_options::FIRST->value);

        $grades = $this->session_query_service->get_grades_for_gradebook(0, 2, 1);

        $this->assertCount(1, $grades);
        $this->assert_grade_equals(1, 0.5, 10, $grades[1]);
    }

    public function test_get_grades_returns_first_grades_for_all_users(): void
    {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispring_module_stub->method('get_grade_method')->willReturn(grading_options::FIRST->value);

        $grades = $this->session_query_service->get_grades_for_gradebook(0, 2, 0);

        $this->assertCount(2, $grades);
        $this->assert_grade_equals(1, 0.5, 10, $grades[1]);
        $this->assert_grade_equals(2, 60, 100, $grades[2]);
    }

    public function test_get_grades_returns_last_grade_when_grade_method_is_set_to_last(): void
    {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispring_module_stub->method('get_grade_method')->willReturn(grading_options::LAST->value);

        $grades = $this->session_query_service->get_grades_for_gradebook(0, 2, 1);

        $this->assertCount(1, $grades);
        $this->assert_grade_equals(1, -0.5, 50, $grades[1]);
    }

    public function test_get_grades_returns_last_grades_for_all_users(): void
    {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispring_module_stub->method('get_grade_method')->willReturn(grading_options::LAST->value);

        $grades = $this->session_query_service->get_grades_for_gradebook(0, 2, 0);

        $this->assertCount(2, $grades);
        $this->assert_grade_equals(1, -0.5, 50, $grades[1]);
        $this->assert_grade_equals(2, 60, 100, $grades[2]);
    }

    private function create_mock_sessions(int $content_id, int $user_id, array $grades): array
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

        $ids = [];
        foreach ($grades as $grade)
        {
            $session->score = $grade['score'];
            ++$session->attempt;
            $session->end_time = $grade['end_time'];

            $ids[] = $DB->insert_record('ispring_session', $session);
        }
        return $ids;
    }

    private function create_mock_sessions_for_gradebook_tests(): void
    {
        $this->create_mock_sessions(1, 1, [
            ['score' => 100, 'end_time' => 10],
        ]);
        $this->create_mock_sessions(1, 2, [
            ['score' => -110, 'end_time' => 10],
        ]);
        $this->create_mock_sessions(2, 1, [
            ['score' => 0.5, 'end_time' => 10],
            ['score' => 25, 'end_time' => 20],
            ['score' => -100, 'end_time' => 30],
            ['score' => 25, 'end_time' => 40],
            ['score' => -0.5, 'end_time' => 50],
        ]);
        $this->create_mock_sessions(2, 2, [
            ['score' => 60, 'end_time' => 100],
        ]);
    }

    private function assert_db_record_equals_session_model(int $session_id, session $model): void
    {
        global $DB;
        $record = $DB->get_record('ispring_session', ['id' => $session_id]);

        $this->assertEquals($record->id, $model->get_id());
        $this->assertEquals($record->user_id, $model->get_user_id());
        $this->assertEquals($record->ispring_content_id, $model->get_content_id());
        $this->assertEquals($record->status, $model->get_status());
        $this->assertEquals($record->score, $model->get_score());
        $this->assertEquals($record->begin_time, $model->get_begin_time());
        $this->assertEquals($record->attempt, $model->get_attempt());
        $this->assertEquals($record->end_time, $model->get_end_time());
        $this->assertEquals($record->duration, $model->get_duration());
        $this->assertEquals($record->persist_state, $model->get_persist_state());
        $this->assertEquals($record->persist_state_id, $model->get_persist_state_id());
        $this->assertEquals($record->max_score, $model->get_max_score());
        $this->assertEquals($record->min_score, $model->get_min_score());
        $this->assertEquals($record->passing_score, $model->get_passing_score());
        $this->assertEquals($record->detailed_report, $model->get_detailed_report());
    }

    private function assert_grade_equals(int $user_id, float $score, ?int $date_graded, \stdClass $grade): void
    {
        $this->assertEquals($user_id, $grade->userid);
        $this->assertEquals($score, $grade->rawgrade);
        if ($date_graded !== null)
        {
            $this->assertEquals($date_graded, $grade->dategraded);
        }
    }
}