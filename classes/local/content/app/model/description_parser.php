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

namespace mod_ispring\local\content\app\model;

use mod_ispring\local\content\app\exception\invalid_description_exception;
use mod_ispring\local\content\app\exception\unsupported_content_exception;
use stored_file;

class description_parser {
    private const MAX_SUPPORTED_VERSION = 1;

    public static function parse(stored_file $file): description {
        $content = json_decode($file->get_content(), true);

        if (!is_array($content)) {
            throw new invalid_description_exception();
        }

        $result = description::create($content);

        if (!$result) {
            throw new invalid_description_exception();
        }
        if ($result->get_description_params()->get_version() > self::MAX_SUPPORTED_VERSION) {
            throw new unsupported_content_exception();
        }

        return $result;
    }
}
