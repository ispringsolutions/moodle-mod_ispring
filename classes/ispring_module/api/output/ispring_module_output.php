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

namespace mod_ispring\ispring_module\api\output;

class ispring_module_output {
    private int $id;
    private string $name;
    private int $moodlecourseid;
    private int $grade;
    private int $grademethod;
    private ?description_output $description;
    private int $timeopen;
    private int $timeclose;

    public function __construct(
        int $id,
        string $name,
        int $moodlecourseid,
        int $grade,
        int $grademethod,
        ?description_output $description,
        int $timeopen,
        int $timeclose
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->moodlecourseid = $moodlecourseid;
        $this->grade = $grade;
        $this->grademethod = $grademethod;
        $this->description = $description;
        $this->timeopen = $timeopen;
        $this->timeclose = $timeclose;
    }

    /**
     * @return int
     */
    public function get_id(): int {
        return $this->id;
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
    public function get_grade(): int {
        return $this->grade;
    }

    /**
     * @return int
     */
    public function get_grade_method(): int {
        return $this->grademethod;
    }

    /**
     * @return description_output|null
     */
    public function get_description(): ?description_output {
        return $this->description;
    }

    public function get_time_open(): int {
        return $this->timeopen;
    }

    public function get_time_close(): int {
        return $this->timeclose;
    }
}
