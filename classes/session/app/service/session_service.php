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

namespace mod_ispring\session\app\service;

use mod_ispring\common\infrastructure\lock\locking_service;
use mod_ispring\common\infrastructure\transaction\db_transaction;
use mod_ispring\common\infrastructure\transaction\transaction_utils;
use mod_ispring\session\app\adapter\content_api_interface;
use mod_ispring\session\app\data\end_data;
use mod_ispring\session\app\data\update_data;
use mod_ispring\session\app\exception\player_conflict_exception;
use mod_ispring\session\app\model\session;
use mod_ispring\session\app\query\session_query_service_interface;
use mod_ispring\session\app\repository\session_repository_interface;
use mod_ispring\session\domain\model\session_state;

class session_service
{
    private const CREATE_SESSION_TIMEOUT = 10;
    private session_repository_interface $session_repository;
    private content_api_interface $content_api;
    private session_query_service_interface $session_query_service;

    public function __construct(
        session_repository_interface $session_repository,
        content_api_interface $content_api,
        session_query_service_interface $session_query_service
    )
    {
        $this->session_repository = $session_repository;
        $this->content_api = $content_api;
        $this->session_query_service = $session_query_service;
    }

    public function add(int $content_id, int $user_id, string $status, string $player_id, bool $session_restored): int
    {
        return transaction_utils::do_in_transaction(
            db_transaction::class,
            function() use ($content_id, $user_id, $status, $player_id, $session_restored) {
                if (!$ispring_module_id = $this->content_api->get_ispring_module_id_by_content_id($content_id))
                {
                    throw new \RuntimeException("Invalid content id");
                }

                $lock = locking_service::get_lock('session_create', "user:{$user_id}", self::CREATE_SESSION_TIMEOUT);
                $existing_session = $this->session_query_service->get_last_by_content_id($content_id, $user_id);

                if ($existing_session && (self::session_completed($status) || $session_restored))
                {
                    $session = new session(
                        $existing_session->get_content_id(),
                        $existing_session->get_score(),
                        $existing_session->get_status(),
                        $existing_session->get_begin_time(),
                        $existing_session->get_end_time(),
                        $existing_session->get_duration(),
                        $existing_session->get_user_id(),
                        $existing_session->get_attempt(),
                        $existing_session->get_persist_state_id(),
                        $existing_session->get_persist_state(),
                        $existing_session->get_max_score(),
                        $existing_session->get_min_score(),
                        $existing_session->get_passing_score(),
                        $existing_session->get_detailed_report(),
                        $player_id,
                    );

                    $this->session_repository->update($existing_session->get_id(), $session);
                    return $existing_session->get_id();
                }

                $session = new session(
                    $content_id,
                    0,
                    session_state::INCOMPLETE,
                    time(),
                    null,
                    null,
                    $user_id,
                    $this->get_next_attempt_number($ispring_module_id, $user_id),
                    null,
                    null,
                    null,
                    0,
                    null,
                    null,
                    $player_id,
                );
                return $this->session_repository->add($session);
            },
        );
    }

    public function end(int $session_id, int $user_id, end_data $data): bool
    {
        return transaction_utils::do_in_transaction(
            db_transaction::class,
            function() use ($session_id, $user_id, $data) {
                $session = $this->session_query_service->get($session_id);
                if (!$session)
                {
                    return false;
                }

                if ($session->get_user_id() !== $user_id)
                {
                    throw new \RuntimeException("Cannot end session started by different user");
                }

                $updated_session = new session(
                    $session->get_content_id(),
                    $data->get_score() ?? 0,
                    $session->get_status(),
                    $session->get_begin_time(),
                    time(),
                    $session->get_duration(),
                    $session->get_user_id(),
                    $session->get_attempt(),
                    $session->get_persist_state_id(),
                    $session->get_persist_state(),
                    $data->get_max_score() ?? 0,
                    $data->get_min_score() ?? 0,
                    $data->get_passing_score() ?? 0,
                    $data->get_detailed_report(),
                    $session->get_player_id(),
                );

                return $this->session_repository->update($session->get_id(), $updated_session);
            },
        );
    }

    public function update(int $session_id, int $user_id, update_data $data): bool
    {
        return transaction_utils::do_in_transaction(
            db_transaction::class,
            function() use ($session_id, $user_id, $data) {
                $session = $this->session_query_service->get($session_id);

                if (!$session)
                {
                    return false;
                }

                if ($session->get_user_id() !== $user_id)
                {
                    throw new \RuntimeException('Cannot update session started by different user');
                }

                if (self::session_completed($session->get_status()))
                {
                    throw new \RuntimeException('Cannot update already ended session');
                }

                if ($data->get_player_id() != $session->get_player_id())
                {
                    throw new player_conflict_exception('Cannot update from different player');
                }

                $new_session = new session(
                    $session->get_content_id(),
                    $session->get_score(),
                    $data->get_status(),
                    $session->get_begin_time(),
                    $session->get_end_time(),
                    $data->get_duration(),
                    $session->get_user_id(),
                    $session->get_attempt(),
                    $data->get_persist_state_id(),
                    $data->get_persist_state(),
                    $session->get_max_score(),
                    $session->get_min_score(),
                    $session->get_passing_score(),
                    $session->get_detailed_report(),
                    $session->get_player_id(),
                );

                return $this->session_repository->update($session_id, $new_session);
            },
        );
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