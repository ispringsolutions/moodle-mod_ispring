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

use external_value;
use mod_ispring\common\infrastructure\capability_utils;
use mod_ispring\common\infrastructure\context_utils;
use mod_ispring\di_container;
use mod_ispring\session\api\output\detailed_report_output;

class get_report_data extends \external_api
{
    public static function execute_parameters(): \external_function_parameters
    {
        return new \external_function_parameters([
            'session_id' => new external_value(PARAM_INT, 'session id'),
        ]);
    }

    public static function execute(int $session_id): array
    {
        ['session_id' => $session_id] = self::validate_parameters(
            self::execute_parameters(),
            ['session_id' => $session_id],
        );

        $session_api = di_container::get_session_api();

        $detailed_report = $session_api->get_detailed_report($session_id);
        if (!$detailed_report)
        {
            throw new \moodle_exception('sessionnotfound', 'ispring');
        }

        self::require_access_to_report($detailed_report);

        return [
            'report_data' => $detailed_report->get_detailed_report(),
        ];
    }

    public static function execute_returns(): \external_single_structure
    {
        return new \external_single_structure([
            'report_data' => new external_value(PARAM_RAW, 'detailed report data'),
        ]);
    }

    private static function require_access_to_report(detailed_report_output $detailed_report): void
    {
        $content_api = di_container::get_content_api();
        $module_context = context_utils::get_module_context($content_api, $detailed_report->get_content_id());

        if (!capability_utils::can_view_detailed_reports_for_user($module_context, $detailed_report->get_user_id()))
        {
            throw new \moodle_exception('sessionnotfound', 'ispring');
        }
        self::validate_context($module_context);
    }
}