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

namespace mod_ispring\argparser;

use mod_ispring\ispring_module\api\ispring_module_api_interface;
use mod_ispring\ispring_module\api\output\ispring_module_output;
use stdClass;

class argparser {
    private stdClass $cm;
    private stdClass $moodlecourse;
    private ispring_module_output $ispringmodule;

    public function __construct(
        int $cmid,
        ispring_module_api_interface $ispringmoduleapi
    ) {
        if (empty($cmid)) {
            throw new \moodle_exception('missingparameter');
        }

        $cm = get_coursemodule_from_id('ispring', $cmid);
        if (!$cm) {
            throw new \moodle_exception('invalidcoursemodule');
        }
        $this->cm = $cm;

        $course = get_course($this->cm->course);
        if (!$course) {
            throw new \moodle_exception('coursemisconf');
        }
        $this->moodlecourse = $course;

        $module = $ispringmoduleapi->get_by_id($this->cm->instance);
        if (!$module) {
            throw new \moodle_exception('invalidcoursemodule');
        }
        $this->ispringmodule = $module;
    }

    /**
     * @return stdClass
     */
    public function get_cm(): stdClass {
        return $this->cm;
    }

    /**
     * @return stdClass
     */
    public function get_moodle_course(): stdClass {
        return $this->moodlecourse;
    }

    /**
     * @return ispring_module_output
     */
    public function get_ispring_module(): ispring_module_output {
        return $this->ispringmodule;
    }
}
