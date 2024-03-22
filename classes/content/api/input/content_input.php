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

namespace mod_ispring\content\api\input;

class content_input {
    private int $fileid;
    private int $ispringmoduleid;
    private int $contextid;
    private int $usercontextid;

    public function __construct(int $fileid, int $ispringmoduleid, int $contextid, int $usercontextid) {
        $this->fileid = $fileid;
        $this->ispringmoduleid = $ispringmoduleid;
        $this->contextid = $contextid;
        $this->usercontextid = $usercontextid;
    }

    /**
     * @return int
     */
    public function get_file_id(): int {
        return $this->fileid;
    }

    /**
     * @return int
     */
    public function get_ispring_module_id(): int {
        return $this->ispringmoduleid;
    }

    /**
     * @return int
     */
    public function get_context_id(): int {
        return $this->contextid;
    }

    /**
     * @return int
     */
    public function get_user_context_id(): int {
        return $this->usercontextid;
    }
}
