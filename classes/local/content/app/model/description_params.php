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

namespace mod_ispring\local\content\app\model;

class description_params {
    private string $entrypoint;
    private int $creationtime;
    private ?string $reportentrypoint;

    public function __construct(
        string $entrypoint,
        int $creationtime,
        ?string $reportentrypoint
    ) {
        $this->entrypoint = $entrypoint;
        $this->creationtime = $creationtime;
        $this->reportentrypoint = $reportentrypoint;
    }

    /**
     * @param string[] $data
     * @return description_params|null
     */
    public static function create(array $data): ?description_params {
        $entrypoint = null;
        if (array_key_exists('report_entrypoint', $data)) {
            $entrypoint = $data['report_entrypoint'];
        }

        if (array_key_exists('entrypoint', $data)
            && array_key_exists('creation_time', $data)) {
            return new description_params(
                $data['entrypoint'],
                (int)$data['creation_time'],
                $entrypoint
            );
        }

        return null;
    }

    /**
     * @return string
     */
    public function get_entrypoint(): string {
        return $this->entrypoint;
    }

    /**
     * @return int
     */
    public function get_creation_time(): int {
        return $this->creationtime;
    }

    /**
     * @return string|null
     */
    public function get_report_entrypoint(): ?string {
        return $this->reportentrypoint;
    }
}
