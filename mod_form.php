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

use mod_ispring\ispring_module\domain\model\grading_options;
use mod_ispring\content\infrastructure\file_storage as ispring_file_storage;

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
        // Availability.
        $mform->addElement('header', 'availability', get_string('availability'));

        $mform->addElement('date_time_selector', 'timeopen', get_string('timeopen', 'ispring'), ['optional' => true]);
        $mform->addElement('date_time_selector', 'timeclose', get_string('timeclose', 'ispring'), ['optional' => true]);

        // -------------------------------------------------------------------------------
        // Grade settings.
        $this->standard_grading_coursemodule_elements();

        $mform->removeElement('grade');
        $mform->removeElement('gradepass');

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
        // In Moodle 4.3 The 'completionpassgrade' is a radio element with multiple options, so we should remove all of them.
        while ($mform->elementExists('completionpassgrade'))
        {
            $mform->removeElement('completionpassgrade');
        }
    }

    public function data_preprocessing(&$default_values): void
    {
        $default_values['timeopen'] = !empty($default_values['timeopen']) ? $default_values['timeopen'] : 0;
        $default_values['timeclose'] = !empty($default_values['timeclose']) ? $default_values['timeclose'] : 0;

        // prepare already uploaded file
        $draft_item_id = file_get_submitted_draft_itemid('userfile');

        file_prepare_draft_area(
            $draft_item_id,
            $this->context->id,
            ispring_file_storage::COMPONENT_NAME,
            ispring_file_storage::PACKAGE_FILEAREA,
            ispring_file_storage::PACKAGE_ITEM_ID,
            ['subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 0]
        );
        $default_values['userfile'] = $draft_item_id;
    }

    public function validation($data, $files): array
    {
        $errors = parent::validation($data, $files);

        if ($data['timeopen'] && $data['timeclose'] && $data['timeopen'] > $data['timeclose']) {
            $errors['timeclose'] = get_string('closebeforeopen', 'ispring');
        }

        return $errors;
    }

    private static function get_grading_options_translations(): array
    {
        return [
            grading_options::HIGHEST => get_string('highest', 'ispring'),
            grading_options::AVERAGE => get_string('average', 'ispring'),
            grading_options::FIRST => get_string('first', 'ispring'),
            grading_options::LAST => get_string('last', 'ispring'),
        ];
    }
}