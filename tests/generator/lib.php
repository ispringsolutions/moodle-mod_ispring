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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../user_file_creator.php');

use mod_ispring\local\ispring_module\domain\model\grading_options;
use mod_ispring\user_file_creator;

class mod_ispring_generator extends testing_module_generator {
    public function create_instance($record = null, array $options = null) {
        $record = (array)$record + [
                'grademethod' => grading_options::HIGHEST,
                'timeopen' => 0,
                'timeclose' => 3,
            ];

        if (!isset($record['userfile'])) {
            $file = user_file_creator::create_from_path(__DIR__ . '/../packages/stub.zip');
            $record['userfile'] = $file->get_itemid();
        }

        return parent::create_instance($record, $options);
    }
}
