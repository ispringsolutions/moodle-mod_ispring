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

namespace mod_ispring\session\api\input;

class update_input
{
    public function __construct(
        private readonly int $duration,
        private readonly string $persist_state_id,
        private readonly string $persist_state,
        private readonly string $status,
        private readonly string $player_id,
    )
    {
    }

    /**
     * @return int
     */
    public function get_duration(): int
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function get_persist_state_id(): string
    {
        return $this->persist_state_id;
    }

    /**
     * @return string
     */
    public function get_persist_state(): string
    {
        return $this->persist_state;
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
}