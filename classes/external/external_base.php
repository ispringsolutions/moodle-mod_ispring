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

class external_base {
    public const REVIEW_SESSION_ID = -1;
    public const ERROR_CODE_INVALID_PLAYER_ID = 'invalidplayerid';

    /**
     * Teachers only have a module preview mode.
     * There is no need to collect teacher's progress and show it in reports.
     *
     * @param \context $context
     * @return bool
     */
    public static function is_review_available(\context $context): bool {
        return has_capability('mod/ispring:preview', $context);
    }

    /**
     * @param int $sessionid
     * @return bool
     */
    public static function is_review_session(int $sessionid): bool {
        return $sessionid == self::REVIEW_SESSION_ID;
    }
}
