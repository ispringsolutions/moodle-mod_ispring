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

namespace mod_ispring\common\infrastructure;

class capability_utils
{
    /**
     * Checks whether current user can view detailed reports for given user id in given context
     *
     * @param \context $context Capability is checked in specified context
     * @param int $user_id Capability is checked for detailed reports with specified user id
     * @return bool True if detailed reports matching given criteria can be viewed
     */
    public static function can_view_detailed_reports_for_user(\context $context, int $user_id): bool
    {
        global $USER;
        if (has_capability('mod/ispring:viewallreports', $context))
        {
            return true;
        }
        if (has_capability('mod/ispring:viewmydetailedreports', $context) && $USER->id === $user_id)
        {
            return true;
        }
        return false;
    }
}