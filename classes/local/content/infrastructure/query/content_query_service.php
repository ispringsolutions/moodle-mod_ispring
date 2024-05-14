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

namespace mod_ispring\local\content\infrastructure\query;

use mod_ispring\local\content\app\query\content_query_service_interface;
use mod_ispring\local\content\app\query\model\content;

class content_query_service implements content_query_service_interface {
    private \moodle_database $database;

    public function __construct() {
        global $DB;
        $this->database = $DB;
    }

    public function get_latest_version_content_by_ispring_module_id(int $ispringmoduleid): ?content {
        try {
            $contents = $this->database->get_records(
                'ispring_content',
                ['ispring_id' => $ispringmoduleid],
                'version desc',
                '*',
                0,
                1
            );

            if (count($contents) === 0) {
                return null;
            }

            $content = array_values($contents)[0];

            return $this->get_content($content);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function exists(int $id): bool {
        try {
            $content = $this->database->get_record('ispring_content', ['id' => $id]);
            return (bool)$content;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function get_by_id(int $contentid): ?content {
        try {
            $content = $this->database->get_record('ispring_content', ['id' => $contentid]);

            if (!$content) {
                return null;
            }

            return $this->get_content($content);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function get_ids_by_ispring_module_id(int $ispringmoduleid): array {
        try {
            $contents = $this->database->get_records(
                'ispring_content',
                ['ispring_id' => $ispringmoduleid],
                '',
                'id'
            );

            $result = [];
            foreach ($contents as $content) {
                $result[] = $content->id;
            }

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function get_content(\stdClass $contentdata): content {
        return new content(
            $contentdata->id,
            $contentdata->file_id,
            $contentdata->ispring_id,
            $contentdata->creation_time,
            $contentdata->filename,
            $contentdata->path,
            $contentdata->version,
            $contentdata->report_path,
            $contentdata->report_filename,
        );
    }
}
