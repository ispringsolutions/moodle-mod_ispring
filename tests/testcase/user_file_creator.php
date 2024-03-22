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

namespace mod_ispring\testcase;

final class user_file_creator {
    public static function create_from_path(string $filepath): \stored_file {
        global $USER;
        $usercontext = \context_user::instance($USER->id);

        return get_file_storage()->create_file_from_pathname(
            [
                'contextid' => $usercontext->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => file_get_unused_draft_itemid(),
                'filename' => pathinfo($filepath, PATHINFO_BASENAME),
                'filepath' => '/',
            ],
            $filepath,
        );
    }

    public static function create_from_string(string $basename, string $content): \stored_file {
        global $USER;
        $usercontext = \context_user::instance($USER->id);

        return get_file_storage()->create_file_from_string(
            [
                'contextid' => $usercontext->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => file_get_unused_draft_itemid(),
                'filename' => $basename,
                'filepath' => '/',
            ],
            $content,
        );
    }
}
