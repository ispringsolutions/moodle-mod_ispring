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

use invalid_parameter_exception;

/**
 * Test state_parser class.
 *
 * @covers \mod_ispring\external\state_parser
 */
final class state_parser_test extends \basic_testcase {
    public function test_parse_state_throws_exception_on_empty_string(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_state('');
    }

    public function test_parse_state_can_parse_valid_state(): void {
        $state = state_parser::parse_state(
            '{"duration":10,"id":"_id","persistState":"_state","status":"incomplete","playerId":"1234"}',
        );

        $this->assertEquals(10, $state->get_duration());
        $this->assertEquals('_id', $state->get_id());
        $this->assertEquals('"_state"', $state->get_persist_state());
        $this->assertEquals('incomplete', $state->get_status());
        $this->assertEquals('1234', $state->get_player_id());
    }

    public function test_parse_state_throws_exception_when_duration_is_missing(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_state(
            '{"id":"_id","persistState":"_state","status":"incomplete","playerId":"1234"}',
        );
    }

    public function test_parse_state_throws_exception_when_id_is_missing(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_state(
            '{"duration":10,"persistState":"_state","status":"incomplete","playerId":"1234"}',
        );
    }

    public function test_parse_state_throws_exception_when_persist_state_is_missing(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_state(
            '{"duration":10,"id":"_id","status":"incomplete","playerId":"1234"}',
        );
    }

    public function test_parse_state_throws_exception_when_status_is_missing(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_state(
            '{"duration":10,"id":"_id","persistState":"_state","playerId":"1234"}',
        );
    }

    public function test_parse_state_throws_exception_when_player_id_is_missing(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_state(
            '{"duration":9.9,"id":"_id","persistState":"_state","status":"incomplete"}',
        );
    }

    public function test_parse_state_throws_exception_when_duration_is_not_number(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_state(
            '{"duration":"1 0","id":"_id","persistState":"_state","status":"incomplete","playerId":"1234"}',
        );
    }

    public function test_parse_state_succeeds_when_duration_is_not_integer(): void {
        $state = state_parser::parse_state(
            '{"duration":9.9,"id":"_id","persistState":"_state","status":"incomplete","playerId":"1234"}',
        );

        $this->assertEquals(9, $state->get_duration());
    }

    public function test_parse_result_state_throws_exception_on_empty_string(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_result_state('');
    }

    public function test_parse_result_state_can_parse_valid_result_state(): void {
        $resultstate = state_parser::parse_result_state(
            '{"duration":10,"id":"_id","persistState":"_state","status":"incomplete","playerId":"1234"}',
        );

        $state = $resultstate->get_state();
        $this->assertEquals(10, $state->get_duration());
        $this->assertEquals('_id', $state->get_id());
        $this->assertEquals('"_state"', $state->get_persist_state());
        $this->assertEquals('incomplete', $state->get_status());
        $this->assertEquals('1234', $state->get_player_id());
    }

    public function test_parse_result_state_can_parse_optional_parameters(): void {
        $state = state_parser::parse_result_state(
            '{"duration":10,"id":"_id","persistState":"_state","status":"incomplete","playerId":"1234",'
            . '"maxScore":100.5,"minScore":20.5,"passingScore":60.5,"score":80.5,"detailedReport":"_report"}',
        );

        $this->assertEquals(100.5, $state->get_max_score());
        $this->assertEquals(20.5, $state->get_min_score());
        $this->assertEquals(60.5, $state->get_passing_score());
        $this->assertEquals(80.5, $state->get_score());
        $this->assertEquals('"_report"', $state->get_detailed_report());
        $this->assertEquals('1234', $state->get_state()->get_player_id());
    }

    public function test_parse_result_state_throws_exception_when_max_score_is_not_number(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_result_state(
            '{"duration":10,"id":"_id","persistState":"_state","status":"incomplete","playerId":"1234",'
            . '"maxScore":"","minScore":20.5,"passingScore":60.5,"score":80.5,"detailedReport":"_report"}',
        );
    }

    public function test_parse_result_state_throws_exception_when_min_score_is_not_number(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_result_state(
            '{"duration":10,"id":"_id","persistState":"_state","status":"incomplete","playerId":"1234",'
            . '"maxScore":100.5,"minScore":"","passingScore":60.5,"score":80.5,"detailedReport":"_report"}',
        );
    }

    public function test_parse_result_state_throws_exception_when_passing_score_is_not_number(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_result_state(
            '{"duration":10,"id":"_id","persistState":"_state","status":"incomplete","playerId":"1234",'
            . '"maxScore":100.5,"minScore":20.5,"passingScore":"","score":80.5,"detailedReport":"_report"}',
        );
    }

    public function test_parse_result_state_throws_exception_when_score_is_not_number(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_result_state(
            '{"duration":10,"id":"_id","persistState":"_state","status":"incomplete","playerId":"1234",'
            . '"maxScore":100.5,"minScore":20.5,"passingScore":60.5,"score":"","detailedReport":"_report"}',
        );
    }

    public function test_parse_start_state_throws_exception_on_empty_string(): void {
        $this->expectException(invalid_parameter_exception::class);
        state_parser::parse_start_state('');
    }

    public function test_parse_start_state_can_parse_valid_start_state(): void {
        $state = state_parser::parse_start_state('{"status":"incomplete","playerId":"1234","sessionRestored":"true"}');

        $this->assertEquals('incomplete', $state->get_status());
        $this->assertEquals('1234', $state->get_player_id());
        $this->assertEquals('true', $state->get_session_restored());
    }

    public function test_parse_start_state_throws_exception_when_status_is_missing(): void {
        $this->expectException(invalid_parameter_exception::class);

        state_parser::parse_start_state('{}');
    }
}
