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

$functions = [
    'mod_ispring_get_player_data' => [
        'classname' => 'mod_ispring\external\get_player_data',
        'description' => 'Returns player persist state data for specified content',
        'type' => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_ispring_get_report_data' => [
        'classname' => 'mod_ispring\external\get_report_data',
        'description' => 'Returns detailed report data for specified session id',
        'type' => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_ispring_start_session' => [
        'classname' => 'mod_ispring\external\start_session',
        'description' => 'Starts new playback session. This function is called when the user starts content playback',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_ispring_set_state' => [
        'classname' => 'mod_ispring\external\set_state',
        'description' => 'Sets playback state associated with specified session id. This function is called when player state changes',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_ispring_end_session' => [
        'classname' => 'mod_ispring\external\end_session',
        'description' => 'End user content playback session',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];