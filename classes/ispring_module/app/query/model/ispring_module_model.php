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

namespace mod_ispring\ispring_module\app\query\model;

use mod_ispring\ispring_module\app\model\description;

class ispring_module_model
{
    private int $id;
    private string $name;
    private int $moodle_course_id;
    private int $grade;
    private int $grade_method;
    private ?description $description;
    private int $time_open;
    private int $time_close;

    public function __construct(
        int $id,
        string $name,
        int $moodle_course_id,
        int $grade,
        int $grade_method,
        ?description $description,
        int $time_open,
        int $time_close
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->moodle_course_id = $moodle_course_id;
        $this->grade = $grade;
        $this->grade_method = $grade_method;
        $this->description = $description;
        $this->time_open = $time_open;
        $this->time_close = $time_close;
    }

    /**
     * @return int
     */
    public function get_id(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function get_moodle_course_id(): int
    {
        return $this->moodle_course_id;
    }

    /**
     * @return int
     */
    public function get_grade(): int
    {
        return $this->grade;
    }

    /**
     * @return int
     */
    public function get_grade_method(): int
    {
        return $this->grade_method;
    }

    /**
     * @return description|null
     */
    public function get_description(): ?description
    {
        return $this->description;
    }

    public function get_time_open(): int
    {
        return $this->time_open;
    }

    public function get_time_close(): int
    {
        return $this->time_close;
    }
}