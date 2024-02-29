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

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use mod_ispring\common\infrastructure\context_utils;
use mod_ispring\di_container;

class get_player_data extends external_api
{
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'content_id' => new external_value(PARAM_INT, 'content id'),
        ]);
    }

    public static function execute(int $content_id): array
    {
        global $USER;

        ['content_id' => $content_id] = self::validate_parameters(
            self::execute_parameters(),
            ['content_id' => $content_id],
        );

        $module_context = self::get_module_context($content_id);

        $session_api = di_container::get_session_api();
        $session = $session_api->get_last_by_content_id($content_id, $USER->id);

        if (has_capability('mod/ispring:preview', $module_context))
        {
            $persist_state = null;
        }
        else if (has_capability('mod/ispring:view', $module_context))
        {
            $persist_state = $session ? $session->get_persist_state() : null;
        }
        else
        {
            throw new \moodle_exception('contentnotfound', 'ispring');
        }
        self::validate_context($module_context);

        return [
            'persist_state_id' => $session ? $session->get_persist_state_id() : null,
            'persist_state' => $persist_state,
        ];
    }

    public static function execute_returns(): external_single_structure
    {
        return new external_single_structure([
            'persist_state_id' => new external_value(PARAM_RAW, 'content persist state id'),
            'persist_state' => new external_value(PARAM_RAW, 'content persist state'),
        ]);
    }

    private static function get_module_context(int $content_id): \context_module
    {
        $content_api = di_container::get_content_api();
        return context_utils::get_module_context($content_api, $content_id);
    }
}