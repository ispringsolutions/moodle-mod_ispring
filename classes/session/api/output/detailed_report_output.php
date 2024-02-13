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

namespace mod_ispring\session\api\output;

class detailed_report_output
{
    private int $user_id;
    private int $content_id;
    private ?string $detailed_report;

    public function __construct(
        int $user_id,
        int $content_id,
        ?string $detailed_report
    )
    {
        $this->user_id = $user_id;
        $this->content_id = $content_id;
        $this->detailed_report = $detailed_report;
    }

    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function get_content_id(): int
    {
        return $this->content_id;
    }

    /**
     * @return string|null
     */
    public function get_detailed_report(): ?string
    {
        return $this->detailed_report;
    }
}