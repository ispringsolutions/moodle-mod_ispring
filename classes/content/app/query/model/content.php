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

namespace mod_ispring\content\app\query\model;

class content
{

    private int $id;
    private int $file_id;
    private int $ispring_module_id;
    private int $creation_time;
    private string $filename;
    private string $filepath;
    private int $version;
    private ?string $report_path;
    private ?string $report_filename;

    public function __construct(
        int $id,
        int $file_id,
        int $ispring_module_id,
        int $creation_time,
        string $filename,
        string $filepath,
        int $version,
        ?string $report_path,
        ?string $report_filename
    )
    {
        $this->id = $id;
        $this->file_id = $file_id;
        $this->ispring_module_id = $ispring_module_id;
        $this->creation_time = $creation_time;
        $this->filename = $filename;
        $this->filepath = $filepath;
        $this->version = $version;
        $this->report_path = $report_path;
        $this->report_filename = $report_filename;
    }

    /**
     * @return int
     */
    public function get_id(): int
    {
        return $this->id;
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
     * @return string
     */
    public function get_filename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function get_filepath(): string
    {
        return $this->filepath;
    }

    /**
     * @return int
     */
    public function get_version(): int
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function get_report_path(): ?string
    {
        return $this->report_path;
    }

    /**
     * @return string|null
     */
    public function get_report_filename(): ?string
    {
        return $this->report_filename;
    }
}