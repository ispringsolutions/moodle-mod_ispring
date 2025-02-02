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
use mod_ispring\local\common\app\exception\inaccessible_content_exception;
use mod_ispring\local\common\app\exception\inaccessible_session_exception;
use mod_ispring\local\di_container;
use mod_ispring\local\session\api\session_api_interface;
use mod_ispring\local\session\app\exception\player_conflict_exception;
use mod_ispring\local\use_case\end_session_use_case;

class end_session extends external_api {
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

        if (external_base::is_review_session($sessionid)) {
            return ['warning' => []];
        }

        $sessionapi = di_container::get_session_api();
        $ispringmoduleid = self::get_ispring_module_id_by_session_id($sessionapi, $sessionid);

        $usecase = new end_session_use_case(di_container::get_ispring_module_api(), $sessionapi);
        $parsedstate = state_parser::parse_result_state($state);
        try {
            $usecase->end_session($ispringmoduleid, $sessionid, $USER->id, $parsedstate);
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

    /**
     * @param session_api_interface $sessionapi
     * @param int $sessionid
     * @return int
     */
    private static function get_ispring_module_id_by_session_id(
        session_api_interface $sessionapi,
        int $sessionid
    ): int {
        $session = $sessionapi->get_by_id($sessionid);
        if (!$session) {
            throw new inaccessible_session_exception();
        }
        $content = di_container::get_content_api()->get_by_id($session->get_content_id());
        if (!$content) {
            throw new inaccessible_content_exception();
        }
        return $content->get_ispring_module_id();
    }
}
