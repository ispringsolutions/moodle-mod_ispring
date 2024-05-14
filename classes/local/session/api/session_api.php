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

namespace mod_ispring\local\session\api;

use mod_ispring\local\session\api\input\end_input;
use mod_ispring\local\session\api\input\update_input;
use mod_ispring\local\session\api\mapper\session_mapper;
use mod_ispring\local\session\api\output\detailed_report_output;
use mod_ispring\local\session\api\output\session_output;
use mod_ispring\local\session\app\adapter\content_api_interface;
use mod_ispring\local\session\app\query\session_query_service_interface;
use mod_ispring\local\session\app\service\session_service;

class session_api implements session_api_interface {
    private session_service $service;
    private session_query_service_interface $queryservice;
    private content_api_interface $contentapi;

    public function __construct(
        session_service $service,
        session_query_service_interface $queryservice,
        content_api_interface $contentapi
    ) {
        $this->service = $service;
        $this->queryservice = $queryservice;
        $this->contentapi = $contentapi;
    }

    public function add(int $contentid, int $userid, string $status, string $playerid, bool $sessionrestored): int {
        return $this->service->add($contentid, $userid, $status, $playerid, $sessionrestored);
    }

    public function get_last_by_content_id(int $contentid, int $userid): ?session_output {
        $session = $this->queryservice->get_last_by_content_id($contentid, $userid);

        return $session
            ? session_mapper::get_session_output($session)
            : null;
    }

    public function ispring_module_has_sessions_with_user_id(int $ispringmoduleid, int $userid): bool {
        return $this->queryservice->ispring_module_has_sessions_with_user_id($ispringmoduleid, $userid);
    }

    public function end(int $sessionid, int $userid, end_input $data): bool {
        return $this->service->end($sessionid, $userid, session_mapper::get_end_data($data));
    }

    public function get_grades_for_gradebook(int $id, int $userid): ?array {
        $contentid = $this->contentapi->get_newest_content_id($id);

        if (!$contentid) {
            return null;
        }

        return $this->queryservice->get_grades_for_gradebook($id, $contentid, $userid);
    }

    public function get_by_id(int $sessionid): ?session_output {
        $session = $this->queryservice->get($sessionid);
        return $session
            ? session_mapper::get_session_output($session)
            : null;
    }

    public function update(int $sessionid, int $userid, update_input $data): bool {
        return $this->service->update($sessionid, $userid, session_mapper::get_update_data($data));
    }

    public function get_detailed_report(int $sessionid): ?detailed_report_output {
        $session = $this->queryservice->get($sessionid);
        if (!$session) {
            return null;
        }

        return new detailed_report_output(
            $session->get_user_id(),
            $session->get_content_id(),
            $session->get_detailed_report(),
        );
    }

    public function delete_by_content_id(int $contentid): bool {
        return $this->service->delete_by_content_id($contentid);
    }

    public function passing_requirements_were_updated(int $ispringmoduleid): bool {
        $contentids = $this->contentapi->get_ids_by_ispring_module_id($ispringmoduleid);

        return $this->queryservice->passing_requirements_were_updated($contentids);
    }

    public function passing_requirements_were_updated_for_user(int $ispringmoduleid, int $userid): bool {
        $contentids = $this->contentapi->get_ids_by_ispring_module_id($ispringmoduleid);

        return $this->queryservice->passing_requirements_were_updated_for_user($contentids, $userid);
    }
}
