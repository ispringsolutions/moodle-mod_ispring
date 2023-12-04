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

namespace mod_ispring\common\infrastructure\transaction;

use mod_ispring\common\app\transaction\transaction_client_interface;

class transaction implements transaction_client_interface
{
    private array $rollback_stack = [];

    public function execute(callable $fn, callable $undo_fn): mixed
    {
        $result = $fn();
        array_push($this->rollback_stack, fn() => $undo_fn($result));
        return $result;
    }

    public function rollback(\Throwable $e): void
    {
        while (($undo_fn = array_pop($this->rollback_stack)) !== null)
        {
            try
            {
                $undo_fn();
            }
            catch (\Throwable $undo_exception)
            {
                error_log($undo_exception->getMessage());
            }
        }
    }
}