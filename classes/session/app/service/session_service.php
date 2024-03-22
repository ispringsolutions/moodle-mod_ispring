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

class session_service {
    private const CREATE_SESSION_TIMEOUT = 10;
    private session_repository_interface $repository;
    private content_api_interface $contentapi;
    private session_query_service_interface $queryservice;

    public function __construct(
        session_repository_interface $repository,
        content_api_interface $contentapi,
        session_query_service_interface $queryservice
    ) {
        $this->repository = $repository;
        $this->contentapi = $contentapi;
        $this->queryservice = $queryservice;
    }

    public function add(int $contentid, int $userid, string $status, string $playerid, bool $sessionrestored): int {
        return transaction_utils::do_in_transaction(
            db_transaction::class,
            function () use ($contentid, $userid, $status, $playerid, $sessionrestored) {
                if (!$ispringmoduleid = $this->contentapi->get_ispring_module_id_by_content_id($contentid)) {
                    throw new \RuntimeException("Invalid content id");
                }

                $lock = locking_service::get_lock('session_create', "user:{$userid}", self::CREATE_SESSION_TIMEOUT);
                $existingsession = $this->queryservice->get_last_by_content_id($contentid, $userid);

                if ($existingsession && (self::session_completed($status) || $sessionrestored)) {
                    $session = new session(
                        $existingsession->get_content_id(),
                        $existingsession->get_score(),
                        $existingsession->get_status(),
                        $existingsession->get_begin_time(),
                        $existingsession->get_end_time(),
                        $existingsession->get_duration(),
                        $existingsession->get_user_id(),
                        $existingsession->get_attempt(),
                        $existingsession->get_persist_state_id(),
                        $existingsession->get_persist_state(),
                        $existingsession->get_max_score(),
                        $existingsession->get_min_score(),
                        $existingsession->get_passing_score(),
                        $existingsession->get_detailed_report(),
                        $playerid,
                    );

                    $this->repository->update($existingsession->get_id(), $session);
                    return $existingsession->get_id();
                }

                $session = new session(
                    $contentid,
                    0,
                    session_state::INCOMPLETE,
                    time(),
                    null,
                    null,
                    $userid,
                    $this->get_next_attempt_number($ispringmoduleid, $userid),
                    null,
                    null,
                    null,
                    0,
                    null,
                    null,
                    $playerid,
                );
                return $this->repository->add($session);
            },
        );
    }

    public function end(int $sessionid, int $userid, end_data $data): bool {
        return transaction_utils::do_in_transaction(
            db_transaction::class,
            function () use ($sessionid, $userid, $data) {
                $session = $this->queryservice->get($sessionid);
                if (!$session) {
                    return false;
                }

                if ($session->get_user_id() !== $userid) {
                    throw new \RuntimeException("Cannot end session started by different user");
                }

                $updatedsession = new session(
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

                return $this->repository->update($session->get_id(), $updatedsession);
            },
        );
    }

    public function update(int $sessionid, int $userid, update_data $data): bool {
        return transaction_utils::do_in_transaction(
            db_transaction::class,
            function () use ($sessionid, $userid, $data) {
                $session = $this->queryservice->get($sessionid);

                if (!$session) {
                    return false;
                }

                if ($session->get_user_id() !== $userid) {
                    throw new \RuntimeException('Cannot update session started by different user');
                }

                if (self::session_completed($session->get_status())) {
                    throw new \RuntimeException('Cannot update already ended session');
                }

                if ($data->get_player_id() != $session->get_player_id()) {
                    throw new player_conflict_exception('Cannot update from different player');
                }

                $newsession = new session(
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

                return $this->repository->update($sessionid, $newsession);
            },
        );
    }

    private static function session_completed(string $state): bool {
        return $state != session_state::INCOMPLETE;
    }

    private function get_next_attempt_number(int $ispringmoduleid, int $userid): int {
        $session = $this->queryservice->get_last_by_ispring_module_id($ispringmoduleid, $userid);

        return 1 + ($session ? $session->get_attempt() : 0);
    }

    public function delete_by_content_id(int $contentid): bool {
        return $this->repository->delete_by_content_id($contentid);
    }
}
