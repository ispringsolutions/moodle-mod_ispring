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

namespace mod_ispring\ispring_module\app\data;

use mod_ispring\ispring_module\app\model\description;

class ispring_module_data
{
    public function __construct(
        private readonly string $name,
        private readonly int $moodle_course_id,
        private readonly int $grade_method,
        private readonly ?description $description,
    )
    {
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
}