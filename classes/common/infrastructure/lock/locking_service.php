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

use core\lock\lock_config;
use mod_ispring\common\app\lock\lock_interface;

class locking_service {
    private const LOCK_NAME_PREFIX = 'mod_ispring_';

    public static function get_lock(string $name, string $resource, int $timeout): lock_interface {
        $lockfactory = lock_config::get_lock_factory(self::LOCK_NAME_PREFIX . $name);
        $lock = $lockfactory->get_lock($resource, $timeout);
        if (!$lock) {
            throw new \RuntimeException('Failed to obtain lock');
        }
        return new auto_release_lock_wrapper($lock);
    }
}
