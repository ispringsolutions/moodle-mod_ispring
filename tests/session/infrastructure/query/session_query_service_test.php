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

namespace mod_ispring\session\infrastructure\query;

use mod_ispring\ispring_module\domain\model\grading_options;
use mod_ispring\session\app\adapter\ispring_module_api_interface;
use mod_ispring\session\app\query\model\session;
use mod_ispring\session\domain\model\session_state;

/**
 * Test session_query_service class.
 *
 * @covers \mod_ispring\session\infrastructure\query\session_query_service
 */
final class session_query_service_test extends \advanced_testcase {
    /** @var mixed */
    private $ispringmodulestub;
    private session_query_service $sessionqueryservice;

    protected function setUp(): void {
        $this->ispringmodulestub = $this->createStub(ispring_module_api_interface::class);

        $this->sessionqueryservice = new session_query_service($this->ispringmodulestub);
    }

    public function test_get_returns_null_for_nonexistent_id(): void {
        $this->assertNull($this->sessionqueryservice->get(1));
    }

    public function test_get_returns_session_for_valid_id(): void {
        $sessionid = $this->create_mock_sessions(2, 3, [
            ['score' => 80, 'end_time' => 100],
        ])[0];

        $session = $this->sessionqueryservice->get($sessionid);

        $this->assert_db_record_equals_session_model($sessionid, $session);
    }

    public function test_get_grades_returns_highest_grade_when_grade_method_is_set_to_highest(): void {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispringmodulestub->method('get_grade_method')->willReturn(grading_options::HIGHEST);

        $grades = $this->sessionqueryservice->get_grades_for_gradebook(0, 2, 1);

        $this->assertCount(1, $grades);
        $this->assert_grade_equals(1, 25, 20, $grades[1]);
    }

    public function test_get_grades_returns_highest_grades_for_all_users(): void {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispringmodulestub->method('get_grade_method')->willReturn(grading_options::HIGHEST);

        $grades = $this->sessionqueryservice->get_grades_for_gradebook(0, 2, 0);

        $this->assertCount(2, $grades);
        $this->assert_grade_equals(1, 25, 20, $grades[1]);
        $this->assert_grade_equals(2, 60, 100, $grades[2]);
    }

    public function test_get_grades_returns_average_grade_when_grade_method_is_set_to_average(): void {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispringmodulestub->method('get_grade_method')->willReturn(grading_options::AVERAGE);

        $grades = $this->sessionqueryservice->get_grades_for_gradebook(0, 2, 1);

        $this->assertCount(1, $grades);
        $this->assert_grade_equals(1, -10, null, $grades[1]);
    }

    public function test_get_grades_returns_average_grades_for_all_users(): void {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispringmodulestub->method('get_grade_method')->willReturn(grading_options::AVERAGE);

        $grades = $this->sessionqueryservice->get_grades_for_gradebook(0, 2, 0);

        $this->assertCount(2, $grades);
        $this->assert_grade_equals(1, -10, null, $grades[1]);
        $this->assert_grade_equals(2, 60, null, $grades[2]);
    }

    public function test_get_grades_returns_first_grade_when_grade_method_is_set_to_first(): void {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispringmodulestub->method('get_grade_method')->willReturn(grading_options::FIRST);

        $grades = $this->sessionqueryservice->get_grades_for_gradebook(0, 2, 1);

        $this->assertCount(1, $grades);
        $this->assert_grade_equals(1, 0.5, 10, $grades[1]);
    }

    public function test_get_grades_returns_first_grades_for_all_users(): void {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispringmodulestub->method('get_grade_method')->willReturn(grading_options::FIRST);

        $grades = $this->sessionqueryservice->get_grades_for_gradebook(0, 2, 0);

        $this->assertCount(2, $grades);
        $this->assert_grade_equals(1, 0.5, 10, $grades[1]);
        $this->assert_grade_equals(2, 60, 100, $grades[2]);
    }

    public function test_get_grades_returns_last_grade_when_grade_method_is_set_to_last(): void {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispringmodulestub->method('get_grade_method')->willReturn(grading_options::LAST);

        $grades = $this->sessionqueryservice->get_grades_for_gradebook(0, 2, 1);

        $this->assertCount(1, $grades);
        $this->assert_grade_equals(1, -0.5, 50, $grades[1]);
    }

    public function test_get_grades_returns_last_grades_for_all_users(): void {
        $this->create_mock_sessions_for_gradebook_tests();
        $this->ispringmodulestub->method('get_grade_method')->willReturn(grading_options::LAST);

        $grades = $this->sessionqueryservice->get_grades_for_gradebook(0, 2, 0);

        $this->assertCount(2, $grades);
        $this->assert_grade_equals(1, -0.5, 50, $grades[1]);
        $this->assert_grade_equals(2, 60, 100, $grades[2]);
    }

    public function test_requirements_were_updated_returns_false_on_empty_content_ids(): void {
        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated([]));
    }

    public function test_requirements_were_updated_returns_false_on_empty_data(): void {
        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated([1, 2]));
    }

    public function test_requirements_were_updated_returns_false_on_same_content_with_same_requirements(): void {
        $this->create_mock_sessions(2, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->create_mock_sessions(2, 4,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated([2]));
    }

    public function test_requirements_were_updated_returns_false_on_different_contents_with_same_requirements(): void {
        $this->create_mock_sessions(2, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->create_mock_sessions(4, 4,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated([2, 4]));
    }

    public function test_requirements_were_updated_returns_false_on_external_contents(): void {
        $this->create_mock_sessions(2, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->create_mock_sessions(4, 4,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated([1, 3, 5]));
    }

    public function test_requirements_were_updated_returns_true_on_sequentially_changed_contents(): void {
        $this->create_mock_sessions(2, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->create_mock_sessions(3, 4,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 40, 'min_score' => 0]
        );

        $this->create_mock_sessions(4, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 40, 'min_score' => 0]
        );

        $this->create_mock_sessions(5, 4,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 40, 'min_score' => 20]
        );

        $this->create_mock_sessions(6, 4,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->assertTrue($this->sessionqueryservice->passing_requirements_were_updated([2, 3]));
        $this->assertTrue($this->sessionqueryservice->passing_requirements_were_updated([4, 5]));

        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated([2, 6]));
        $this->assertTrue($this->sessionqueryservice->passing_requirements_were_updated([2, 4, 6]));
    }

    public function test_requirements_were_updated_for_user_returns_false_on_empty_content_ids(): void {
        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated_for_user([], 0));
    }

    public function test_requirements_were_updated_for_user_returns_false_on_non_existing_content_ids(): void {
        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated_for_user([1, 2, 3], 0));
    }

    public function test_requirements_were_updated_for_user_returns_false_on_existing_content_ids(): void {
        $this->create_mock_sessions(2, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->create_mock_sessions(2, 4,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        // Method returns False, if student have passed test already.
        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated_for_user([2], 4));

        // Method returns False, if student have not passed test at all.
        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated_for_user([2], 5));
    }

    public function test_requirements_were_updated_for_user_returns_false_on_changed_content_and_non_passing_test_at_all(): void {
        $this->create_mock_sessions(2, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->create_mock_sessions(3, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 20]
        );

        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated_for_user([2, 3], 3));
        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated_for_user([2, 3], 5));
    }

    public function test_requirements_were_updated_for_user_returns_false_on_changed_content_and_passing_first_version(): void {
        $this->create_mock_sessions(2, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );
        $this->create_mock_sessions(2, 5,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 0]
        );

        $this->create_mock_sessions(3, 3,
            [['score' => 18, 'end_time' => 100]],
            ['max_score' => 20, 'min_score' => 20]
        );

        $this->assertFalse($this->sessionqueryservice->passing_requirements_were_updated_for_user([2], 3));
        $this->assertTrue($this->sessionqueryservice->passing_requirements_were_updated_for_user([2, 3], 5));
    }

    private function create_mock_sessions(int $contentid, int $userid, array $grades, ?array $requirements = null): array {
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
        $session->max_score = $requirements ? $requirements['max_score'] : 100;
        $session->min_score = $requirements ? $requirements['min_score'] : 20;
        $session->passing_score = 60;
        $session->detailed_report = '_report';
        $session->player_id = '1234';

        $ids = [];
        foreach ($grades as $grade) {
            $session->score = $grade['score'];
            ++$session->attempt;
            $session->end_time = $grade['end_time'];

            $ids[] = $DB->insert_record('ispring_session', $session);
        }
        return $ids;
    }

    private function create_mock_sessions_for_gradebook_tests(): void {
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

    private function assert_db_record_equals_session_model(int $sessionid, session $model): void {
        global $DB;
        $record = $DB->get_record('ispring_session', ['id' => $sessionid]);

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
        $this->assertEquals($record->player_id, $model->get_player_id());
    }

    private function assert_grade_equals(int $userid, float $score, ?int $dategraded, \stdClass $grade): void {
        $this->assertEquals($userid, $grade->userid);
        $this->assertEquals($score, $grade->rawgrade);
        if ($dategraded !== null) {
            $this->assertEquals($dategraded, $grade->dategraded);
        }
    }
}
