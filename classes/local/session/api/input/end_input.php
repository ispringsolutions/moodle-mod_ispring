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

namespace mod_ispring\local\session\api\input;

class end_input {
    private update_input $updateinput;
    private string $status;
    private ?float $maxscore;
    private ?float $minscore;
    private ?float $passingscore;
    private ?float $score;
    private ?string $detailedreport;

    public function __construct(
        update_input $updateinput,
        string $status,
        ?float $maxscore,
        ?float $minscore,
        ?float $passingscore,
        ?float $score,
        ?string $detailedreport
    ) {
        $this->updateinput = $updateinput;
        $this->status = $status;
        $this->maxscore = $maxscore;
        $this->minscore = $minscore;
        $this->passingscore = $passingscore;
        $this->score = $score;
        $this->detailedreport = $detailedreport;
    }

    /**
     * @return update_input
     */
    public function get_update_input(): update_input {
        return $this->updateinput;
    }

    /**
     * @return string
     */
    public function get_status(): string {
        return $this->status;
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
     * @return float|null
     */
    public function get_score(): ?float {
        return $this->score;
    }

    /**
     * @return string|null
     */
    public function get_detailed_report(): ?string {
        return $this->detailedreport;
    }
}
