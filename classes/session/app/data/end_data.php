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

namespace mod_ispring\session\app\data;

class end_data
{
    private ?float $max_score;
    private ?float $min_score;
    private ?float $passing_score;
    private ?float $score;
    private ?string $detailed_report;

    public function __construct(
        ?float $max_score,
        ?float $min_score,
        ?float $passing_score,
        ?float $score,
        ?string $detailed_report
    )
    {
        $this->max_score = $max_score;
        $this->min_score = $min_score;
        $this->passing_score = $passing_score;
        $this->score = $score;
        $this->detailed_report = $detailed_report;
    }

    /**
     * @return float|null
     */
    public function get_max_score(): ?float
    {
        return $this->max_score;
    }

    /**
     * @return float|null
     */
    public function get_min_score(): ?float
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
     * @return float|null
     */
    public function get_score(): ?float
    {
        return $this->score;
    }

    /**
     * @return string|null
     */
    public function get_detailed_report(): ?string
    {
        return $this->detailed_report;
    }
}