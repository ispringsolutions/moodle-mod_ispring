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

namespace mod_ispring\use_case;

use mod_ispring\common\infrastructure\transaction\db_transaction;
use mod_ispring\common\infrastructure\transaction\transaction_utils;
use mod_ispring\external\result_state;
use mod_ispring\ispring_module\api\ispring_module_api_interface;
use mod_ispring\mapper\std_mapper;
use mod_ispring\session\api\input\end_input;
use mod_ispring\session\api\input\update_input;
use mod_ispring\session\api\session_api_interface;

class end_session_use_case
{
    private ispring_module_api_interface $ispring_module_api;
    private session_api_interface $session_api;

    public function __construct(
        ispring_module_api_interface $ispring_module_api,
        session_api_interface $session_api
    )
    {
        $this->ispring_module_api = $ispring_module_api;
        $this->session_api = $session_api;
    }

    public function end_session(int $ispring_module_id, int $session_id, int $user_id, result_state $result_state): void
    {
        transaction_utils::do_in_transaction(
            db_transaction::class,
            function() use ($session_id, $user_id, $result_state) {
                $this->session_api->update($session_id, $user_id, new update_input(
                    $result_state->get_state()->get_duration(),
                    $result_state->get_state()->get_id(),
                    $result_state->get_state()->get_persist_state(),
                    $result_state->get_state()->get_status(),
                    $result_state->get_state()->get_player_id(),
                ));

                $this->session_api->end($session_id, $user_id, new end_input(
                    $result_state->get_max_score(),
                    $result_state->get_min_score(),
                    $result_state->get_passing_score(),
                    $result_state->get_score(),
                    $result_state->get_detailed_report(),
                ));
            },
        );
        $this->update_grades_and_completion_state($ispring_module_id, $session_id, $user_id);
    }

    private function update_grades_and_completion_state(int $ispring_module_id, int $session_id, int $user_id): bool
    {
        $module = $this->ispring_module_api->get_by_id($ispring_module_id);
        if (!$module)
        {
            return false;
        }
        $session = $this->session_api->get_by_id($session_id);
        if (!$session)
        {
            return false;
        }

        $module_instance = std_mapper::ispring_module_output_to_std_class($module);
        $module_instance->max_score = $session->get_max_score();
        $module_instance->min_score = $session->get_min_score();

        global $CFG;
        require_once($CFG->dirroot . '/mod/ispring/lib.php');

        ispring_update_grades($module_instance, $user_id);

        [$course, $cm] = get_course_and_cm_from_instance($ispring_module_id, 'ispring');

        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm) && $session->get_passing_score() <= $session->get_score())
        {
            $completion->update_state($cm, COMPLETION_COMPLETE, $user_id);
        }
        grade_regrade_final_grades_if_required($course);

        return true;
    }
}