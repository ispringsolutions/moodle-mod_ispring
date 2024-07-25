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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use external_warnings;
use mod_ispring\local\di_container;
use mod_ispring\local\session\app\exception\player_conflict_exception;

class set_suspend_data extends external_api {
    /**
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'session_id' => new external_value(PARAM_INT, 'session id'),
            'player_id' => new external_value(PARAM_RAW, 'player id'),
            'suspend_data' => new external_value(PARAM_RAW, 'suspend data'),
        ]);
    }

    public static function execute(int $sessionid, string $playerid, string $suspenddata): array {
        [
            'session_id' => $sessionid,
            'player_id' => $playerid,
            'suspend_data' => $suspenddata,
        ] = self::validate_parameters(
            self::execute_parameters(),
            [
                'session_id' => $sessionid,
                'player_id' => $playerid,
                'suspend_data' => $suspenddata,
            ],
        );

        if (external_base::is_review_session($sessionid)) {
            return ['warning' => []];
        }

        try {
            global $USER;
            di_container::get_session_api()->set_suspend_data($sessionid, $USER->id, $playerid, $suspenddata);
        } catch (player_conflict_exception $exception) {
            return ['warning' => [[
                'warningcode' => external_base::ERROR_CODE_INVALID_PLAYER_ID,
                'message' => get_string('invalidplayerid', 'ispring'),
            ]]];
        }

        return ['warning' => []];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'warning' => new external_warnings(),
        ]);
    }
}
