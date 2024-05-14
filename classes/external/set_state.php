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
use mod_ispring\local\session\api\input\update_input;
use mod_ispring\local\session\app\exception\player_conflict_exception;

class set_state extends external_api {
    private const INVALID_PLAYER_ID_CODE = 'invalidplayerid';

    /**
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'session_id' => new external_value(PARAM_INT, 'session id'),
            'state' => new external_value(PARAM_RAW, 'object state'),
        ]);
    }

    public static function execute(int $sessionid, string $state): array {
        global $USER;

        ['session_id' => $sessionid, 'state' => $state] = self::validate_parameters(
            self::execute_parameters(),
            ['session_id' => $sessionid, 'state' => $state]
        );

        $parsedstate = state_parser::parse_state($state);

        try {
            di_container::get_session_api()->update($sessionid, $USER->id, new update_input(
                $parsedstate->get_duration(),
                $parsedstate->get_id(),
                $parsedstate->get_persist_state(),
                $parsedstate->get_status(),
                $parsedstate->get_player_id(),
            ));
        } catch (player_conflict_exception $exception) {
            return ['warning' => [[
                'warningcode' => self::INVALID_PLAYER_ID_CODE,
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
