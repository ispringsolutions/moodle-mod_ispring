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

namespace mod_ispring\content\app\model;

class content
{
    private int $file_id;
    private int $ispring_module_id;
    private int $creation_time;
    private file_info $content_path;
    private int $version;
    private ?file_info $report_path;

    public function __construct(
        int $file_id,
        int $ispring_module_id,
        int $creation_time,
        file_info $content_path,
        int $version,
        ?file_info $report_path
    )
    {
        $this->file_id = $file_id;
        $this->ispring_module_id = $ispring_module_id;
        $this->creation_time = $creation_time;
        $this->content_path = $content_path;
        $this->version = $version;
        $this->report_path = $report_path;
    }

    /**
     * @return int
     */
    public function get_file_id(): int
    {
        return $this->file_id;
    }

    /**
     * @return int
     */
    public function get_ispring_module_id(): int
    {
        return $this->ispring_module_id;
    }

    /**
     * @return int
     */
    public function get_creation_time(): int
    {
        return $this->creation_time;
    }

    /**
     * @return file_info
     */
    public function get_content_path(): file_info
    {
        return $this->content_path;
    }

    /**
     * @return int
     */
    public function get_version(): int
    {
        return $this->version;
    }

    /**
     * @return file_info|null
     */
    public function get_report_path(): ?file_info
    {
        return $this->report_path;
    }
}