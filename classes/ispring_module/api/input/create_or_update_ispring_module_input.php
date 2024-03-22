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

namespace mod_ispring\ispring_module\api\input;

class create_or_update_ispring_module_input {
    private string $name;
    private int $moodlecourseid;
    private int $grademethod;
    private ?description_input $description;
    private int $timeopen;
    private int $timeclose;

    public function __construct(
        string $name,
        int $moodlecourseid,
        int $grademethod,
        ?description_input $description,
        int $timeopen,
        int $timeclose
    ) {
        $this->name = $name;
        $this->moodlecourseid = $moodlecourseid;
        $this->grademethod = $grademethod;
        $this->description = $description;
        $this->timeopen = $timeopen;
        $this->timeclose = $timeclose;
    }

    /**
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function get_moodle_course_id(): int {
        return $this->moodlecourseid;
    }

    /**
     * @return int
     */
    public function get_grade_method(): int {
        return $this->grademethod;
    }

    /**
     * @return description_input|null
     */
    public function get_description(): ?description_input {
        return $this->description;
    }

    public function get_time_open(): int {
        return $this->timeopen;
    }

    public function get_time_close(): int {
        return $this->timeclose;
    }
}
