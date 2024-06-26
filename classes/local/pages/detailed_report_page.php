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

class detailed_report_page extends base_page {
    private const PLAYER_ID = 'mod-ispring-report-player';
    private const PRELOADER_ID = 'mod-ispring-preloader';

    private int $sessionid;
    private string $reporturl;
    private string $backurl;
    private int $userid;

    public function __construct(
        int $sessionid,
        string $reporturl,
        string $backurl,
        int $userid,
        string $url,
        array $args = null
    ) {
        parent::__construct($url, $args);

        $this->sessionid = $sessionid;
        $this->reporturl = $reporturl;
        $this->backurl = $backurl;
        $this->userid = $userid;

        $this->define_properties();
    }

    public function get_content(): string {
        $user = \core_user::get_user($this->userid);
        if (!$user) {
            throw new \moodle_exception('invaliduserid');
        }

        $content = \html_writer::tag('div', '', ['class' => 'preloader', 'id' => self::PRELOADER_ID]);
        $content .= \html_writer::start_tag('iframe', [
            'id' => self::PLAYER_ID,
            'class' => 'detailed-report__report',
        ]);
        $content .= \html_writer::end_tag('iframe');

        $this->get_page()->requires->js_call_amd('mod_ispring/detailed_report_api', 'init', [
            $this->sessionid,
            current_language(),
            self::PLAYER_ID,
            $this->reporturl,
            self::PRELOADER_ID,
            $this->backurl,
            \fullname($user),
            $this->get_user_image_url($user),
        ]);

        return $content;
    }

    private function define_properties(): void {
        $this->get_page()->add_body_class('limitedwidth');
    }

    private function get_user_image_url(\stdClass $user): string {
        if ($user->picture == 0 && empty($CFG->enablegravatar)) {
            return '';
        }

        $userpicture = new \user_picture($user);
        $userimageurl = $userpicture->get_url($this->get_page());
        return $userimageurl->out(false);
    }
}
