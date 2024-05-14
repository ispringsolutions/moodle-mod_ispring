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

namespace mod_ispring\local\pages;

use core_reportbuilder\system_report_factory;
use mod_ispring\local\report\course_modules_report;

class index_page extends base_page {
    private int $moodlecourseid;

    public function __construct(
        int $moodlecourseid,
        string $url,
        array $args = null
    ) {
        parent::__construct($url, $args);

        $this->moodlecourseid = $moodlecourseid;
    }

    public function get_content(): string {
        $report = system_report_factory::create(
            course_modules_report::class,
            $this->get_page()->context,
            '',
            '',
            0,
            $this->get_report_params()
        );
        return $report->output();
    }

    private function get_report_params(): array {
        return [
            course_modules_report::PARAM_MOODLE_COURSE_ID => $this->moodlecourseid,
        ];
    }
}
