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

namespace mod_ispring\common\infrastructure\lock;

use core\lock\lock;
use mod_ispring\common\app\lock\lock_interface;

class auto_release_lock_wrapper implements lock_interface
{
    private lock $lock;

    public function __construct(
        lock $lock
    )
    {
        $this->lock = $lock;
    }

    public function __destruct()
    {
        try
        {
            $this->release();
        }
        catch (\Throwable $e)
        {
        }
    }

    public function release(): void
    {
        $this->lock->release();
    }
}