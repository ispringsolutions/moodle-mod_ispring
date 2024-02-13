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

namespace mod_ispring\session\api;

use mod_ispring\session\api\input\end_input;
use mod_ispring\session\api\input\update_input;
use mod_ispring\session\api\mapper\session_mapper;
use mod_ispring\session\api\output\detailed_report_output;
use mod_ispring\session\api\output\session_output;
use mod_ispring\session\app\adapter\content_api_interface;
use mod_ispring\session\app\query\session_query_service_interface;
use mod_ispring\session\app\service\session_service;

class session_api implements session_api_interface
{
    private session_service $session_service;
    private session_query_service_interface $session_query_service;
    private content_api_interface $content_api;

    public function __construct(
        session_service $session_service,
        session_query_service_interface $session_query_service,
        content_api_interface $content_api
    )
    {
        $this->session_service = $session_service;
        $this->session_query_service = $session_query_service;
        $this->content_api = $content_api;
    }

    public function add(int $content_id, int $user_id, string $status, string $player_id, bool $session_restored): int
    {
        return $this->session_service->add($content_id, $user_id, $status, $player_id, $session_restored);
    }

    public function get_last_by_content_id(int $content_id, int $user_id): ?session_output
    {
        $session = $this->session_query_service->get_last_by_content_id($content_id, $user_id);

        return $session
            ? session_mapper::get_session_output($session)
            : null;
    }

    public function ispring_module_has_sessions_with_user_id(int $ispring_module_id, int $user_id): bool
    {
        return $this->session_query_service->ispring_module_has_sessions_with_user_id($ispring_module_id, $user_id);
    }

    public function end(int $session_id, int $user_id, end_input $data): bool
    {
        return $this->session_service->end($session_id, $user_id, session_mapper::get_end_data($data));
    }

    public function get_grades_for_gradebook(int $id, int $user_id): ?array
    {
        $content_id = $this->content_api->get_newest_content_id($id);

        if (!$content_id)
        {
            return null;
        }

        return $this->session_query_service->get_grades_for_gradebook($id, $content_id, $user_id);
    }

    public function get_by_id(int $session_id): ?session_output
    {
        $session = $this->session_query_service->get($session_id);
        return $session
            ? session_mapper::get_session_output($session)
            : null;
    }

    public function update(int $session_id, int $user_id, update_input $data): bool
    {
        return $this->session_service->update($session_id, $user_id, session_mapper::get_update_data($data));
    }

    public function get_detailed_report(int $session_id): ?detailed_report_output
    {
        $session = $this->session_query_service->get($session_id);
        if (!$session)
        {
            return null;
        }

        return new detailed_report_output(
            $session->get_user_id(),
            $session->get_content_id(),
            $session->get_detailed_report(),
        );
    }

    public function delete_by_content_id(int $content_id): bool
    {
        return $this->session_service->delete_by_content_id($content_id);
    }

    public function passing_requirements_were_updated(int $ispring_module_id): bool
    {
        $content_ids = $this->content_api->get_ids_by_ispring_module_id($ispring_module_id);

        return $this->session_query_service->passing_requirements_were_updated($content_ids);
    }

    public function passing_requirements_were_updated_for_user(int $ispring_module_id, int $user_id): bool
    {
        $content_ids = $this->content_api->get_ids_by_ispring_module_id($ispring_module_id);

        return $this->session_query_service->passing_requirements_were_updated_for_user($content_ids, $user_id);
    }
}