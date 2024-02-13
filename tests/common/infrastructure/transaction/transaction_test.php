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

namespace mod_ispring\common\infrastructure\transaction;

final class transaction_test extends \basic_testcase
{
    private transaction $transaction;

    protected function setUp(): void
    {
        $this->transaction = new transaction();
    }

    public function test_execute_calls_fn(): void
    {
        $fn_was_executed = false;

        $this->transaction->execute(
            function() use(&$fn_was_executed) {
                $fn_was_executed = true;
            },
            function() {
                throw new \LogicException('Unexpected call to $undo_fn');
            },
        );

        $this->assertTrue($fn_was_executed);
    }

    public function test_execute_returns_value_returned_by_fn(): void
    {
        $result = $this->transaction->execute(
            fn() => 'test_return_value',
            function() {
                throw new \LogicException('Unexpected call to $undo_fn');
            },
        );

        $this->assertEquals('test_return_value', $result);
    }

    public function test_execute_rethrows_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('test_error');
        $this->transaction->execute(
            function() {
                throw new \RuntimeException('test_error');
            },
            function() {
                throw new \LogicException('Unexpected call to $undo_fn');
            },
        );
    }

    public function test_rollback_reverts_successful_operation(): void
    {
        $fn_was_executed = false;
        $this->transaction->execute(
            function() use(&$fn_was_executed) {
                $fn_was_executed = true;
            },
            function() use(&$fn_was_executed) {
                $fn_was_executed = false;
            },
        );
        $this->assertTrue($fn_was_executed);

        $this->transaction->rollback(new \Exception());

        $this->assertFalse($fn_was_executed);
    }

    public function test_rollback_passes_fn_return_value_to_undo_fn(): void
    {
        $result = null;
        $this->transaction->execute(
            fn() => 'test_return_value',
            function($value) use(&$result) {
                $result = $value;
            },
        );

        $this->transaction->rollback(new \Exception());

        $this->assertEquals('test_return_value', $result);
    }

    public function test_rollback_reverts_operations_in_reverse_order(): void
    {
        $log = [];
        $this->transaction->execute(
            function() use(&$log) {
                $log[] = 'Do 1';
            },
            function() use(&$log) {
                $log[] = 'Undo 1';
            },
        );
        $this->transaction->execute(
            function() use(&$log) {
                $log[] = 'Do 2';
            },
            function() use(&$log) {
                $log[] = 'Undo 2';
            },
        );

        $this->transaction->rollback(new \Exception());

        $this->assertCount(4, $log);
        $this->assertEquals('Do 1', $log[0]);
        $this->assertEquals('Do 2', $log[1]);
        $this->assertEquals('Undo 2', $log[2]);
        $this->assertEquals('Undo 1', $log[3]);
    }

    public function test_rollback_ignores_exceptions_in_undo_fn(): void
    {
        $log = [];
        $this->transaction->execute(
            fn() => null,
            function() use(&$log) {
                $log[] = 'Undo 1';
                throw new \Exception();
            },
        );
        $this->transaction->execute(
            fn() => null,
            function() use(&$log) {
                $log[] = 'Undo 2';
                throw new \Exception();
            },
        );

        $this->transaction->rollback(new \Exception());

        $this->assertCount(2, $log);
        $this->assertEquals('Undo 2', $log[0]);
        $this->assertEquals('Undo 1', $log[1]);
    }
}