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

class content_input
{
    private int $file_id;
    private int $ispring_module_id;
    private int $context_id;
    private int $user_context_id;

    public function __construct(int $file_id, int $ispring_module_id, int $context_id, int $user_context_id)
    {
        $this->file_id = $file_id;
        $this->ispring_module_id = $ispring_module_id;
        $this->context_id = $context_id;
        $this->user_context_id = $user_context_id;
    }

    /**
     * @return int
     */
    public function get_file_id(): int
    {
        return $this->file_id;
    }

    /**
     * @return int
     */
    public function get_ispring_module_id(): int
    {
        return $this->ispring_module_id;
    }

    /**
     * @return int
     */
    public function get_context_id(): int
    {
        return $this->context_id;
    }

    /**
     * @return int
     */
    public function get_user_context_id(): int
    {
        return $this->user_context_id;
    }
}