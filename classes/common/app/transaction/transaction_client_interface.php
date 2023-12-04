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

namespace mod_ispring\common\app\transaction;

interface transaction_client_interface
{
    /**
     * Executes callable `$fn` which is expected to do exactly one of the following:
     * 1) complete successfully and return a value
     * 2) throw an exception in case of an error
     *
     * If `$fn` completes successfully, transaction pushes `$undo_fn` onto rollback stack and returns `$fn` result.
     * If an exception occurs in `$fn`, it is rethrown and no other actions are performed.
     *
     * @param callable $fn Action to be executed. `$fn` is called synchronously with no arguments.
     * It is the responsibility of `$fn` to undo any changes if an exception is thrown.
     * @param callable $undo_fn Action that must be executed during rollback to undo every change performed in `$fn`.
     * `$undo_fn` receives the value returned by `$fn` as the only argument. Any exceptions thrown in `$undo_fn` are
     * ignored.
     * @return mixed Value returned by `$fn`
     */
    public function execute(callable $fn, callable $undo_fn): mixed;
}