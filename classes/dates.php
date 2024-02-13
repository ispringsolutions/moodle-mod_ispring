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

namespace mod_ispring;

use core\activity_dates;

/**
 * Class for fetching the important dates in mod_ispring for a given module instance and a user.
 */
class dates extends activity_dates {
    /**
     * Returns a list of important dates in mod_ispring
     *
     * @return array
     * @throws \coding_exception
     */
    protected function get_dates(): array {
        $time_open = $this->cm->customdata['timeopen'] ?? null;
        $time_close = $this->cm->customdata['timeclose'] ?? null;
        $now = time();
        $dates = [];

        if ($time_open) {
            $open_label_id = $time_open > $now ? 'activitydate:opens' : 'activitydate:opened';
            $dates[] = [
                'dataid' => 'timeopen',
                'label' => get_string($open_label_id, 'core_course'),
                'timestamp' => (int) $time_open,
            ];
        }

        if ($time_close) {
            $close_label_id = $time_close > $now ? 'activitydate:closes' : 'activitydate:closed';
            $dates[] = [
                'dataid' => 'timeclose',
                'label' => get_string($close_label_id, 'core_course'),
                'timestamp' => (int) $time_close,
            ];
        }

        return $dates;
    }
}
