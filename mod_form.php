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

use mod_ispring\ispring_module\domain\model\grading_options;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_ispring_mod_form extends moodleform_mod
{
    function definition(): void
    {
        $mform = $this->_form;

        // -------------------------------------------------------------------------------
        // General settings.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => 64));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('moduledescription', 'ispring'));

        // -------------------------------------------------------------------------------
        // Package settings.
        $mform->addElement('header', 'fieldsetpackage', get_string('fieldsetpackage', 'ispring'));
        $mform->setExpanded('fieldsetpackage');

        $filemanager_options = [
            'accepted_types' => ['.zip'],
            'maxbytes' => 0,
            'maxfiles' => 1,
            'subdirs' => 0,
        ];

        $mform->addElement('filemanager', 'userfile', get_string('uploadcoursefile', 'ispring'), null, $filemanager_options);
        if (empty($this->current->instance))
        {
            $mform->addRule('userfile', get_string('missinguserfile', 'ispring'), 'required', null, 'client');
        }

        // -------------------------------------------------------------------------------
        // Grade settings.
        $this->standard_grading_coursemodule_elements();

        $mform->removeElement('grade');
        $mform->removeElement('gradepass');

        // Grading method.
        $mform->addElement(
            'select',
            'grademethod',
            get_string('grademethod', 'ispring'),
            self::get_grading_options_translations(),
        );
        $mform->addHelpButton('grademethod', 'grademethod', 'ispring');

        // -------------------------------------------------------------------------------
        // Standard coursemodule elements.
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();

        // -------------------------------------------------------------------------------
        // Activity completion settings.
        $mform->removeElement('completionpassgrade');
    }

    private static function get_grading_options_translations(): array
    {
        return [
            grading_options::HIGHEST->value => get_string('highest', 'ispring'),
            grading_options::AVERAGE->value => get_string('average', 'ispring'),
            grading_options::FIRST->value => get_string('first', 'ispring'),
            grading_options::LAST->value => get_string('last', 'ispring'),
        ];
    }
}