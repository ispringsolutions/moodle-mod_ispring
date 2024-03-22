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

use html_writer;

class play_page extends base_page {
    private const PLAYER_ID = 'mod-ispring-player';
    private const PRELOADER_ID = 'mod-ispring-preloader';
    private const ERROR_BOX_ID = 'mod-ispring-error-box';

    private int $contentid;
    private string $contenturl;
    private string $returnurl;

    public function __construct(
        int $contentid,
        string $contenturl,
        string $returnurl,
        string $url,
        array $args = null
    ) {
        parent::__construct($url, $args);

        $this->contentid = $contentid;
        $this->contenturl = $contenturl;
        $this->returnurl = $returnurl;
    }

    public function get_content(): string {
        $content = $this->get_output()->box_start('generalbox alert alert-warning hidden', self::ERROR_BOX_ID);
        $content .= $this->get_output()->box_end();

        $content .= $this->get_output()->single_button($this->returnurl, get_string('back', 'ispring'));

        $content .= \html_writer::start_tag('div', ['class' => 'container']);
        $content .= \html_writer::tag('div', '', ['class' => 'preloader', 'id' => self::PRELOADER_ID]);
        $content .= html_writer::start_tag('iframe', [
            'id' => self::PLAYER_ID,
            'class' => 'player',
        ]);
        $content .= html_writer::end_tag('iframe');
        $content .= \html_writer::end_tag('div');

        $this->get_page()->requires->js_call_amd('mod_ispring/api', 'init', [
            $this->contentid,
            $this->contenturl,
            self::PLAYER_ID,
            $this->returnurl,
            self::PRELOADER_ID,
            self::ERROR_BOX_ID,
        ]);

        return $content;
    }
}
