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

namespace mod_ispring\local\content\api\output;

class content_output {
    private int $id;
    private int $fileid;
    private int $ispringmoduleid;
    private int $creationtime;
    private string $filename;
    private string $filepath;
    private int $version;

    public function __construct(
        int $id,
        int $fileid,
        int $ispringmoduleid,
        int $creationtime,
        string $filename,
        string $filepath,
        int $version
    ) {
        $this->id = $id;
        $this->fileid = $fileid;
        $this->ispringmoduleid = $ispringmoduleid;
        $this->creationtime = $creationtime;
        $this->filename = $filename;
        $this->filepath = $filepath;
        $this->version = $version;
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
    public function get_file_id(): int {
        return $this->fileid;
    }

    /**
     * @return int
     */
    public function get_ispring_module_id(): int {
        return $this->ispringmoduleid;
    }

    /**
     * @return int
     */
    public function get_creation_time(): int {
        return $this->creationtime;
    }

    /**
     * @return string
     */
    public function get_filename(): string {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function get_filepath(): string {
        return $this->filepath;
    }

    /**
     * @return int
     */
    public function get_version(): int {
        return $this->version;
    }
}
