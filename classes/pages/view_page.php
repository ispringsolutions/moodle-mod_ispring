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
use mod_ispring\ispring_module\api\output\ispring_module_output;
use mod_ispring\ispring_module\domain\model\grading_options;
use mod_ispring\report\module_user_sessions_report;
use mod_ispring\session\api\session_api_interface;

class view_page extends base_page {
    private ispring_module_output $ispringmodule;
    private session_api_interface $sessionapi;
    private int $cmid;
    private int $userid;
    private bool $passingrequirementswereupdated;
    private bool $ismoduleavailable;

    public function __construct(
        ispring_module_output $ispringmodule,
        session_api_interface $sessionapi,
        int $cmid,
        int $userid,
        bool $passingrequirementswereupdated,
        bool $ismoduleavailable,
        string $url,
        array $args = null
    ) {
        parent::__construct($url, $args);

        $this->ispringmodule = $ispringmodule;
        $this->sessionapi = $sessionapi;
        $this->cmid = $cmid;
        $this->userid = $userid;
        $this->passingrequirementswereupdated = $passingrequirementswereupdated;
        $this->ismoduleavailable = $ismoduleavailable;

        $this->setup_page();
    }

    public function get_content(): string {
        $warning = '';
        if ($this->passingrequirementswereupdated) {
            $warning .= $this->get_output()->box_start('generalbox alert alert-warning');
            $warning .= \html_writer::span(get_string('passingrequirementshavebeenupdatedstudenttext', 'ispring'));
            $warning .= $this->get_output()->box_end();
        }

        $hassessions = $this->sessionapi->ispring_module_has_sessions_with_user_id(
            $this->ispringmodule->get_id(),
            $this->userid,
        );

        $context = $this->get_page()->context;

        $playbuttontext = has_capability('mod/ispring:preview', $context)
            ? get_string('previewbutton', 'ispring')
            : get_string('playbutton', 'ispring');

        $moduleinfotext = get_string('viewpageinfotext', 'ispring', [
            'grading_method' => self::get_grading_option_translation($this->ispringmodule->get_grade_method()),
        ]);

        if ($hassessions) {
            $report = system_report_factory::create(
                module_user_sessions_report::class,
                $context,
                '',
                '',
                0,
                [
                    module_user_sessions_report::PARAM_ISPRING_MODULE_ID => $this->ispringmodule->get_id(),
                    module_user_sessions_report::PARAM_USER_ID => $this->userid,
                    module_user_sessions_report::PARAM_PAGE_URL => $this->get_page()->url->out(),
                ],
            );
        }

        return $this->get_output()->render_from_template('mod_ispring/view_page', [
            'play_button_url' => new \moodle_url('/mod/ispring/play.php', ['id' => $this->cmid]),
            'play_button_text' => $this->ismoduleavailable ? $playbuttontext : null,
            'module_info_text' => $moduleinfotext,
            'report' => $hassessions ? $report->output() : null,
            'report_title' => $hassessions ? get_string('studentsessionsreporttitle', 'ispring') : null,
            'warning' => $warning,
        ]);
    }

    private function setup_page(): void {
        $this->get_page()->add_body_class('limitedwidth');
    }

    private static function get_grading_option_translation(int $grademethod): string {
        switch ($grademethod) {
            case grading_options::HIGHEST:
                return get_string('highest', 'ispring');
            case grading_options::AVERAGE:
                return get_string('average', 'ispring');
            case grading_options::FIRST:
                return get_string('first', 'ispring');
            case grading_options::LAST:
                return get_string('last', 'ispring');
            default:
                debugging('Unexpected grading option');
                return '';
        }
    }
}
