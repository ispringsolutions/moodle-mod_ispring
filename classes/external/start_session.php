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
use mod_ispring\common\infrastructure\context_utils;
use mod_ispring\di_container;

class start_session extends external_api
{
    /**
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'content_id' => new external_value(PARAM_INT, 'id of ispring content'),
            'state' => new external_value(PARAM_RAW, 'object state'),
        ]);
    }

    public static function execute($content_id, $state)
    {
        global $USER;
        ['content_id' => $content_id, 'state' => $state] = self::validate_parameters(
            self::execute_parameters(),
            ['content_id' => $content_id, 'state' => $state]
        );

        self::require_access_to_content($content_id);
        $state = state_parser::parse_start_state($state);

        $session_id = di_container::get_session_api()->add(
            $content_id,
            $USER->id,
            $state->get_status(),
            $state->get_player_id(),
            $state->get_session_restored()
        );

        return ['session_id' => $session_id];
    }

    public static function execute_returns(): external_single_structure
    {
        return new external_single_structure([
            'session_id' => new external_value(PARAM_INT, 'session id')
        ]);
    }

    private static function require_access_to_content(int $content_id): void
    {
        $content_api = di_container::get_content_api();
        $module_context = context_utils::get_module_context($content_api, $content_id);

        try
        {
            self::validate_context($module_context);
        }
        catch (\Throwable $e)
        {
            throw new \moodle_exception('contentnotfound', 'ispring');
        }
    }
}