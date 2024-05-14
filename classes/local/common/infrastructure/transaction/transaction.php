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

namespace mod_ispring\local\common\infrastructure\transaction;

use mod_ispring\local\common\app\transaction\transaction_client_interface;

class transaction implements transaction_client_interface {
    private array $rollbackstack = [];

    public function execute(callable $fn, callable $undofn) {
        $result = $fn();
        array_push($this->rollbackstack, fn() => $undofn($result));
        return $result;
    }

    public function rollback(\Throwable $e): void {
        while (($undofn = array_pop($this->rollbackstack)) !== null) {
            try {
                $undofn();
            } catch (\Throwable $undoexception) {
                debugging($undoexception->getMessage());
            }
        }
    }
}
