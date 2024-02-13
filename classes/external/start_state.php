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

namespace mod_ispring\external;

class start_state
{
    private string $status;
    private string $player_id;
    private bool $session_restored;

    public function __construct(
        string $status,
        string $player_id,
        bool $session_restored
    )
    {
        $this->status = $status;
        $this->player_id = $player_id;
        $this->session_restored = $session_restored;
    }

    /**
     * @return string
     */
    public function get_status(): string
    {
        return $this->status;
    }

    public function get_player_id(): string
    {
        return $this->player_id;
    }

    public function get_session_restored(): bool
    {
        return $this->session_restored;
    }
}