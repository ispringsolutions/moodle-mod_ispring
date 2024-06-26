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

namespace mod_ispring\local\use_case;

use mod_ispring\external\result_state;
use mod_ispring\local\common\infrastructure\transaction\db_transaction;
use mod_ispring\local\common\infrastructure\transaction\transaction_utils;
use mod_ispring\local\ispring_module\api\ispring_module_api_interface;
use mod_ispring\local\mapper\std_mapper;
use mod_ispring\local\session\api\input\end_input;
use mod_ispring\local\session\api\input\update_input;
use mod_ispring\local\session\api\session_api_interface;

class end_session_use_case {
    private ispring_module_api_interface $ispringmoduleapi;
    private session_api_interface $sessionapi;

    public function __construct(
        ispring_module_api_interface $ispringmoduleapi,
        session_api_interface $sessionapi
    ) {
        $this->ispringmoduleapi = $ispringmoduleapi;
        $this->sessionapi = $sessionapi;
    }

    public function end_session(
        int $ispringmoduleid,
        int $sessionid,
        int $userid,
        result_state $state
    ): void {
        transaction_utils::do_in_transaction(
            db_transaction::class,
            function () use ($sessionid, $userid, $state) {
                $updateinput = new update_input(
                    $state->get_state()->get_duration(),
                    $state->get_state()->get_id(),
                    $state->get_state()->get_persist_state(),
                    $state->get_state()->get_player_id(),
                );

                $this->sessionapi->end($sessionid, $userid, new end_input(
                    $updateinput,
                    $state->get_status(),
                    $state->get_max_score(),
                    $state->get_min_score(),
                    $state->get_passing_score(),
                    $state->get_score(),
                    $state->get_detailed_report(),
                ));
            },
        );
        $this->update_grades_and_completion_state($ispringmoduleid, $sessionid, $userid);
    }

    private function update_grades_and_completion_state(int $ispringmoduleid, int $sessionid, int $userid): bool {
        $module = $this->ispringmoduleapi->get_by_id($ispringmoduleid);
        if (!$module) {
            return false;
        }
        $session = $this->sessionapi->get_by_id($sessionid);
        if (!$session) {
            return false;
        }

        $moduleinstance = std_mapper::ispring_module_output_to_std_class($module);
        $moduleinstance->max_score = $session->get_max_score();
        $moduleinstance->min_score = $session->get_min_score();

        global $CFG;
        require_once($CFG->dirroot . '/mod/ispring/lib.php');

        ispring_update_grades($moduleinstance, $userid);

        [$course, $cm] = get_course_and_cm_from_instance($ispringmoduleid, 'ispring');

        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm) && $session->get_passing_score() <= $session->get_score()) {
            $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
        }
        grade_regrade_final_grades_if_required($course);

        return true;
    }
}
