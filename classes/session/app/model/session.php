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

namespace mod_ispring\session\app\model;

class session
{
    public function __construct(
        private readonly int $content_id,
        private readonly float $score,
        private readonly string $status,
        private readonly int $begin_time,
        private readonly ?int $end_time,
        private readonly ?int $duration,
        private readonly int $user_id,
        private readonly int $attempt,
        private readonly ?string $persist_state_id,
        private readonly ?string $persist_state,
        private readonly ?float $max_score,
        private readonly float $min_score,
        private readonly ?float $passing_score,
        private readonly ?string $detailed_report,
    )
    {
    }

    /**
     * @return int
     */
    public function get_content_id(): int
    {
        return $this->content_id;
    }

    /**
     * @return float
     */
    public function get_score(): float
    {
        return $this->score;
    }

    /**
     * @return string
     */
    public function get_status(): string
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function get_begin_time(): int
    {
        return $this->begin_time;
    }

    /**
     * @return int|null
     */
    public function get_end_time(): ?int
    {
        return $this->end_time;
    }

    /**
     * @return int|null
     */
    public function get_duration(): ?int
    {
        return $this->duration;
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
    public function get_attempt(): int
    {
        return $this->attempt;
    }

    /**
     * @return string|null
     */
    public function get_persist_state_id(): ?string
    {
        return $this->persist_state_id;
    }

    /**
     * @return string|null
     */
    public function get_persist_state(): ?string
    {
        return $this->persist_state;
    }

    /**
     * @return float|null
     */
    public function get_max_score(): ?float
    {
        return $this->max_score;
    }

    /**
     * @return float
     */
    public function get_min_score(): float
    {
        return $this->min_score;
    }

    /**
     * @return float|null
     */
    public function get_passing_score(): ?float
    {
        return $this->passing_score;
    }

    /**
     * @return string|null
     */
    public function get_detailed_report(): ?string
    {
        return $this->detailed_report;
    }
}