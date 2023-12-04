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

namespace mod_ispring\pages;

class detailed_report_page extends base_page
{
    private const PLAYER_ID = 'mod-ispring-report-player';

    public function __construct(
        private readonly int $session_id,
        private readonly string $report_url,
        private readonly \moodle_url $back_url,
        private readonly int $user_id,
        string $url,
        array $args = null
    )
    {
        parent::__construct($url, $args);

        $this->define_properties();
    }

    public function get_content(): string
    {
        $content = $this->get_output()->single_button($this->back_url, get_string('back', 'ispring'));

        $user = \core_user::get_user($this->user_id);
        if (!$user)
        {
            throw new \moodle_exception('invaliduserid');
        }
        $content .= \html_writer::tag('div', \fullname($user), ['class' => 'detailed-report__username']);

        $content .= \html_writer::start_tag('iframe', [
            'id' => self::PLAYER_ID,
            'class' => 'detailed-report__report',
        ]);
        $content .= \html_writer::end_tag('iframe');

        $this->get_page()->requires->js_call_amd('mod_ispring/detailed_report_api', 'init', [
            $this->session_id,
            current_language(),
            self::PLAYER_ID,
            $this->report_url,
        ]);

        return $content;
    }

    private function define_properties(): void
    {
        $this->get_page()->add_body_class('limitedwidth');
    }
}