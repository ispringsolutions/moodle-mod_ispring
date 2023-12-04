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

namespace mod_ispring\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use mod_ispring\di_container;
use mod_ispring\session\api\input\update_input;

class set_state extends external_api
{
    /**
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'session_id' => new external_value(PARAM_INT, 'session id'),
            'state' => new external_value(PARAM_RAW, 'object state'),
        ]);
    }

    public static function execute(int $session_id, string $state): void {
        global $USER;

        ['session_id' => $session_id, 'state' => $state] = self::validate_parameters(
            self::execute_parameters(),
            ['session_id' => $session_id, 'state' => $state]
        );

        $parsed_state = state_parser::parse_state($state);

        di_container::get_session_api()->update($session_id, $USER->id, new update_input(
            $parsed_state->get_duration(),
            $parsed_state->get_id(),
            $parsed_state->get_persist_state(),
            $parsed_state->get_status(),
        ));
    }

    public static function execute_returns()
    {
        return null;
    }
}