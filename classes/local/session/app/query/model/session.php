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

namespace mod_ispring\local\session\app\query\model;

class session {
    private int $id;
    private int $contentid;
    private float $score;
    private string $status;
    private int $begintime;
    private ?int $endtime;
    private ?int $duration;
    private int $userid;
    private int $attempt;
    private ?string $persiststateid;
    private ?string $persiststate;
    private ?float $maxscore;
    private float $minscore;
    private ?float $passingscore;
    private ?string $detailedreport;
    private string $playerid;

    public function __construct(
        int $id,
        int $contentid,
        float $score,
        string $status,
        int $begintime,
        ?int $endtime,
        ?int $duration,
        int $userid,
        int $attempt,
        ?string $persiststateid,
        ?string $persiststate,
        ?float $maxscore,
        float $minscore,
        ?float $passingscore,
        ?string $detailedreport,
        string $playerid
    ) {
        $this->id = $id;
        $this->contentid = $contentid;
        $this->score = $score;
        $this->status = $status;
        $this->begintime = $begintime;
        $this->endtime = $endtime;
        $this->duration = $duration;
        $this->userid = $userid;
        $this->attempt = $attempt;
        $this->persiststateid = $persiststateid;
        $this->persiststate = $persiststate;
        $this->maxscore = $maxscore;
        $this->minscore = $minscore;
        $this->passingscore = $passingscore;
        $this->detailedreport = $detailedreport;
        $this->playerid = $playerid;
    }

    /**
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * @return int
     */
    public function get_content_id(): int {
        return $this->contentid;
    }

    /**
     * @return float
     */
    public function get_score(): float {
        return $this->score;
    }

    /**
     * @return string
     */
    public function get_status(): string {
        return $this->status;
    }

    /**
     * @return int
     */
    public function get_begin_time(): int {
        return $this->begintime;
    }

    /**
     * @return int|null
     */
    public function get_end_time(): ?int {
        return $this->endtime;
    }

    /**
     * @return int|null
     */
    public function get_duration(): ?int {
        return $this->duration;
    }

    /**
     * @return int
     */
    public function get_user_id(): int {
        return $this->userid;
    }

    /**
     * @return int
     */
    public function get_attempt(): int {
        return $this->attempt;
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
     * @return float|null
     */
    public function get_max_score(): ?float {
        return $this->maxscore;
    }

    /**
     * @return float
     */
    public function get_min_score(): float {
        return $this->minscore;
    }

    /**
     * @return float|null
     */
    public function get_passing_score(): ?float {
        return $this->passingscore;
    }

    /**
     * @return string|null
     */
    public function get_detailed_report(): ?string {
        return $this->detailedreport;
    }

    /**
     * @return string
     */
    public function get_player_id(): string {
        return $this->playerid;
    }
}
