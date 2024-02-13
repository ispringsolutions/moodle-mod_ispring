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

class view_page extends base_page
{
    private ispring_module_output $ispring_module;
    private session_api_interface $session_api;
    private int $cm_id;
    private int $user_id;
    private bool $passing_requirements_were_updated;
    private bool $module_is_available;

    public function __construct(
        ispring_module_output $ispring_module,
        session_api_interface $session_api,
        int $cm_id,
        int $user_id,
        bool $passing_requirements_were_updated,
        bool $module_is_available,
        string $url,
        array $args = null
    )
    {
        parent::__construct($url, $args);

        $this->ispring_module = $ispring_module;
        $this->session_api = $session_api;
        $this->cm_id = $cm_id;
        $this->user_id = $user_id;
        $this->passing_requirements_were_updated = $passing_requirements_were_updated;
        $this->module_is_available = $module_is_available;

        $this->setup_page();
    }

    public function get_content(): string
    {
        $warning = '';
        if ($this->passing_requirements_were_updated)
        {
            $warning .= $this->get_output()->box_start('generalbox alert alert-warning');
            $warning .= \html_writer::span(get_string('passingrequirementshavebeenupdatedstudenttext', 'ispring'));
            $warning .= $this->get_output()->box_end();
        }

        $module_has_sessions = $this->session_api->ispring_module_has_sessions_with_user_id(
            $this->ispring_module->get_id(),
            $this->user_id,
        );

        $context = $this->get_page()->context;

        $play_button_text = has_capability('mod/ispring:preview', $context)
            ? get_string('previewbutton', 'ispring')
            : get_string('playbutton', 'ispring');

        $module_info_text = get_string('viewpageinfotext', 'ispring', [
            'grading_method' => self::get_grading_option_translation($this->ispring_module->get_grade_method()),
        ]);

        if ($module_has_sessions)
        {
            $report = system_report_factory::create(
                module_user_sessions_report::class,
                $context,
                '',
                '',
                0,
                [
                    module_user_sessions_report::PARAM_ISPRING_MODULE_ID => $this->ispring_module->get_id(),
                    module_user_sessions_report::PARAM_USER_ID => $this->user_id,
                    module_user_sessions_report::PARAM_PAGE_URL => $this->get_page()->url->out(),
                ],
            );
        }

        return $this->get_output()->render_from_template('mod_ispring/view_page', [
            'play_button_url' => new \moodle_url('/mod/ispring/play.php', ['id' => $this->cm_id]),
            'play_button_text' => $this->module_is_available ? $play_button_text : null,
            'module_info_text' => $module_info_text,
            'report' => $module_has_sessions ? $report->output() : null,
            'report_title' => $module_has_sessions ? get_string('studentsessionsreporttitle', 'ispring') : null,
            'warning' => $warning
        ]);
    }

    private function setup_page(): void
    {
        $this->get_page()->add_body_class('limitedwidth');
    }

    private static function get_grading_option_translation(int $grade_method): string
    {
        switch ($grade_method)
        {
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