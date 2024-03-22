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

namespace mod_ispring\pages;

use core_reportbuilder\system_report_factory;
use mod_ispring\report\global_report;

class report_page extends base_page {
    private const EN_LINK_DOCUMENTATION = 'https://www.ispringsolutions.com/go/moodle-documentation';
    private const RU_LINK_DOCUMENTATION = 'https://www.ispring.ru/go/moodle-documentation';

    private int $ispringid;
    private bool $passingrequirementswereupdated;

    public function __construct(
        int $ispringid,
        bool $passingrequirementswereupdated,
        string $url,
        array $args = null
    ) {
        parent::__construct($url, $args);

        $this->ispringid = $ispringid;
        $this->passingrequirementswereupdated = $passingrequirementswereupdated;
    }

    public function get_content(): string {
        $content = '';
        if ($this->passingrequirementswereupdated) {
            $content .= $this->get_output()->box_start('generalbox alert alert-warning');
            $content .= \html_writer::span(
                get_string('passingrequirementshavebeenupdatedteachertext', 'ispring', $this->get_url())
            );
            $content .= $this->get_output()->box_end();
        }

        $content .= system_report_factory::create(
            global_report::class,
            $this->get_page()->context,
            '',
            '',
            0,
            $this->get_report_params()
        )->output();

        return $content;
    }

    private function get_report_params(): array {
        return [
            global_report::PARAM_ISPRING_MODULE_ID => $this->ispringid,
            global_report::PARAM_PAGE_URL => $this->get_page()->url->out(),
        ];
    }

    private function get_url(): string {
        $lang = current_language();
        if (current_language() == 'ru') {
            return self::RU_LINK_DOCUMENTATION;
        }

        return self::EN_LINK_DOCUMENTATION . '?lang=' . $lang;
    }
}
