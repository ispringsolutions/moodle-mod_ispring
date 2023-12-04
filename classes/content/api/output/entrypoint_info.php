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

namespace mod_ispring\content\api\output;

class entrypoint_info
{
    private int $content_id;
    private string $entrypoint_url;

    public function __construct(int $content_id, string $entrypoint_url)
    {
        $this->content_id = $content_id;
        $this->entrypoint_url = $entrypoint_url;
    }

    /**
     * @return int
     */
    public function get_content_id(): int
    {
        return $this->content_id;
    }

    /**
     * @return string
     */
    public function get_entrypoint_url(): string
    {
        return $this->entrypoint_url;
    }
}