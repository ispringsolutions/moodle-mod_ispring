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

namespace mod_ispring\session\app\service;

use mod_ispring\session\app\adapter\content_api_interface;
use mod_ispring\session\app\data\end_data;
use mod_ispring\session\app\data\update_data;
use mod_ispring\session\app\model\session;
use mod_ispring\session\app\query\model\session as session_query;
use mod_ispring\session\app\query\session_query_service_interface;
use mod_ispring\session\app\repository\session_repository_interface;
use mod_ispring\session\domain\model\session_state;

class session_service
{
    public function __construct(
        private readonly session_repository_interface $session_repository,
        private readonly content_api_interface $content_api,
        private readonly session_query_service_interface $session_query_service,
    )
    {
    }

    public function add(int $content_id, int $user_id, string $status): int
    {
        if (!$ispring_module_id = $this->content_api->get_ispring_module_id_by_content_id($content_id))
        {
            throw new \RuntimeException("Invalid content id");
        }

        $existing_session = $this->session_query_service->get_last_by_content_id($content_id, $user_id);

        if ($existing_session && self::session_completed($status))
        {
            return $existing_session->get_id();
        }

        $session = new session(
            content_id: $content_id,
            score: 0,
            status: session_state::INCOMPLETE,
            begin_time: time(),
            end_time: null,
            duration: null,
            user_id: $user_id,
            attempt: $this->get_next_attempt_number($ispring_module_id, $user_id),
            persist_state_id: null,
            persist_state: null,
            max_score: null,
            min_score: 0,
            passing_score: null,
            detailed_report: null,
        );
        return $this->session_repository->add($session);
    }

    public function end(session_query $session, int $user_id, end_data $data): bool
    {
        if ($session->get_user_id() !== $user_id)
        {
            throw new \RuntimeException("Cannot end session by different user");
        }

        $updated_session = new session(
            content_id: $session->get_content_id(),
            score: $data->get_score() ?? 0,
            status: $session->get_status(),
            begin_time: $session->get_begin_time(),
            end_time: time(),
            duration: $session->get_duration(),
            user_id: $session->get_user_id(),
            attempt: $session->get_attempt(),
            persist_state_id: $session->get_persist_state_id(),
            persist_state: $session->get_persist_state(),
            max_score: $data->get_max_score() ?? 0,
            min_score: $data->get_min_score() ?? 0,
            passing_score: $data->get_passing_score() ?? 0,
            detailed_report: $data->get_detailed_report(),
        );

        return $this->session_repository->update($session->get_id(), $updated_session);
    }

    public function update(int $session_id, int $user_id, update_data $data): bool
    {
        $session = $this->session_query_service->get($session_id);

        if (!$session)
        {
            return false;
        }

        if ($session->get_user_id() !== $user_id)
        {
            throw new \RuntimeException("Cannot end session by different user");
        }

        if (self::session_completed($session->get_status()))
        {
            throw new \RuntimeException("Cannot end already ended session");
        }

        $new_session = new session(
            content_id: $session->get_content_id(),
            score: $session->get_score(),
            status: $data->get_status(),
            begin_time: $session->get_begin_time(),
            end_time: $session->get_end_time(),
            duration: $data->get_duration(),
            user_id: $session->get_user_id(),
            attempt: $session->get_attempt(),
            persist_state_id: $data->get_persist_state_id(),
            persist_state: $data->get_persist_state(),
            max_score: $session->get_max_score(),
            min_score: $session->get_min_score(),
            passing_score: $session->get_passing_score(),
            detailed_report: $session->get_detailed_report(),
        );

        return $this->session_repository->update($session_id, $new_session);
    }

    private static function session_completed(string $session_status): bool
    {
        return $session_status != session_state::INCOMPLETE;
    }

    private function get_next_attempt_number(int $ispring_module_id, int $user_id): int
    {
        $existing_session = $this->session_query_service->get_last_by_ispring_module_id($ispring_module_id, $user_id);

        return 1 + ($existing_session ? $existing_session->get_attempt() : 0);
    }

    public function delete_by_content_id(int $content_id): bool
    {
        return $this->session_repository->delete_by_content_id($content_id);
    }
}