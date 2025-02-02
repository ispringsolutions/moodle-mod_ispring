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

namespace mod_ispring\local\session\api\mapper;

use mod_ispring\local\session\api\input\end_input;
use mod_ispring\local\session\api\input\update_input;
use mod_ispring\local\session\api\output\session_output;
use mod_ispring\local\session\app\data\end_data;
use mod_ispring\local\session\app\data\update_data;
use mod_ispring\local\session\app\query\model\session;

class session_mapper {
    public static function get_session_output(session $data): session_output {
        return new session_output(
            $data->get_content_id(),
            $data->get_persist_state_id(),
            $data->get_persist_state(),
            $data->get_suspend_data(),
            $data->get_max_score(),
            $data->get_min_score(),
            $data->get_passing_score(),
            $data->get_score(),
        );
    }

    public static function get_update_data(update_input $input): update_data {
        return new update_data(
            $input->get_duration(),
            $input->get_persist_state_id(),
            $input->get_persist_state(),
            $input->get_player_id(),
        );
    }

    public static function get_end_data(end_input $input): end_data {
        return new end_data(
            $input->get_status(),
            $input->get_max_score(),
            $input->get_min_score(),
            $input->get_passing_score(),
            $input->get_score(),
            $input->get_detailed_report(),
        );
    }
}
