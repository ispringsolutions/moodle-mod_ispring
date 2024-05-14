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

use external_function_parameters;
use external_single_structure;
use external_value;
use mod_ispring\local\common\infrastructure\capability_utils;
use mod_ispring\local\common\infrastructure\context_utils;
use mod_ispring\local\di_container;
use mod_ispring\local\session\api\output\detailed_report_output;

class get_report_data extends \external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'session_id' => new external_value(PARAM_INT, 'session id'),
        ]);
    }

    public static function execute(int $sessionid): array {
        ['session_id' => $sessionid] = self::validate_parameters(
            self::execute_parameters(),
            ['session_id' => $sessionid],
        );

        $sessionapi = di_container::get_session_api();

        $detailedreport = $sessionapi->get_detailed_report($sessionid);
        if (!$detailedreport) {
            throw new \moodle_exception('sessionnotfound', 'ispring');
        }

        self::require_access_to_report($detailedreport);

        return [
            'report_data' => $detailedreport->get_detailed_report(),
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'report_data' => new external_value(PARAM_RAW, 'detailed report data'),
        ]);
    }

    private static function require_access_to_report(detailed_report_output $detailedreport): void {
        $contentapi = di_container::get_content_api();
        $modulecontext = context_utils::get_module_context($contentapi, $detailedreport->get_content_id());

        if (!capability_utils::can_view_detailed_reports_for_user($modulecontext, $detailedreport->get_user_id())) {
            throw new \moodle_exception('sessionnotfound', 'ispring');
        }
        self::validate_context($modulecontext);
    }
}
