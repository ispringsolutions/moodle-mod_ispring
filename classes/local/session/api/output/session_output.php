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

namespace mod_ispring\local\session\api\output;

class session_output {
    private int $contentid;
    private ?string $persiststateid;
    private ?string $persiststate;
    private ?string $suspenddata;
    private ?float $maxscore;
    private ?float $minscore;
    private ?float $passingscore;
    private float $score;

    public function __construct(
        int $contentid,
        ?string $persiststateid,
        ?string $persiststate,
        ?string $suspenddata,
        ?float $maxscore,
        ?float $minscore,
        ?float $passingscore,
        float $score
    ) {
        $this->contentid = $contentid;
        $this->persiststateid = $persiststateid;
        $this->persiststate = $persiststate;
        $this->suspenddata = $suspenddata;
        $this->maxscore = $maxscore;
        $this->minscore = $minscore;
        $this->passingscore = $passingscore;
        $this->score = $score;
    }

    /**
     * @return int
     */
    public function get_content_id(): int {
        return $this->contentid;
    }

    /**
     * @return string|null
     */
    public function get_persist_state_id(): ?string {
        return $this->persiststateid;
    }

    /**
     * @return string|null
     */
    public function get_persist_state(): ?string {
        return $this->persiststate;
    }

    /**
     * @return string|null
     */
    public function get_suspend_data(): ?string {
        return $this->suspenddata;
    }

    /**
     * @return float|null
     */
    public function get_max_score(): ?float {
        return $this->maxscore;
    }

    /**
     * @return float|null
     */
    public function get_min_score(): ?float {
        return $this->minscore;
    }

    /**
     * @return float|null
     */
    public function get_passing_score(): ?float {
        return $this->passingscore;
    }

    /**
     * @return float
     */
    public function get_score(): float {
        return $this->score;
    }
}
