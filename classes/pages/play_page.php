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

use html_writer;

class play_page extends base_page
{
    private const PLAYER_ID = 'mod-ispring-player';

    public function __construct(
        private readonly int $content_id,
        private readonly string $content_url,
        private readonly string $return_url,
        string $url,
        array $args = null
    )
    {
        parent::__construct($url, $args);
    }

    public function get_content(): string
    {
        $content = $this->get_output()->single_button($this->return_url, get_string('back', 'ispring'));

        $content .= html_writer::start_tag('iframe', [
            'id' => self::PLAYER_ID,
            'class' => 'player',
            ]);
        $content .= html_writer::end_tag('iframe');

        $this->get_page()->requires->js_call_amd('mod_ispring/api', 'init', [
            $this->content_id,
            $this->content_url,
            self::PLAYER_ID,
            $this->return_url
        ]);

        return $content;
    }
}