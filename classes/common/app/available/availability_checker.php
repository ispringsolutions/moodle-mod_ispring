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

namespace mod_ispring\common\app\available;

use mod_ispring\di_container;

class availability_checker
{
    /**
     * Check module's availability
     *
     * @param int $module_id
     * @param \context $context
     * @return bool
     */
    public static function module_available(int $module_id, \context $context): bool
    {
        $can_preview = has_capability('mod/ispring:preview', $context);

        return $can_preview || di_container::get_ispring_module_api()->is_available($module_id);
    }
}