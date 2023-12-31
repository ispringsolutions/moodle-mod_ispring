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

namespace mod_ispring\content\infrastructure\query;

use mod_ispring\content\app\query\content_query_service_interface;
use mod_ispring\content\app\query\model\content;

class content_query_service implements content_query_service_interface
{
    private mixed $database;

    public function __construct()
    {
        global $DB;
        $this->database = $DB;
    }

    public function get_latest_version_content_by_ispring_module_id(int $ispring_module_id): ?content
    {
        try
        {
            $contents = $this->database->get_records(
                'ispring_content',
                ['ispring_id' => $ispring_module_id],
                'version desc',
                '*',
                0,
                1
            );

            if (count($contents) === 0)
            {
                return null;
            }

            $content = array_values($contents)[0];

            return $this->get_content($content);
        }
        catch (\Exception)
        {
            return null;
        }
    }

    public function exists(int $id): bool
    {
        try
        {
            $content = $this->database->get_record('ispring_content', ['id' => $id]);
            return (bool) $content;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function get_by_id(int $content_id): ?content
    {
        try
        {
            $content = $this->database->get_record('ispring_content', ['id' => $content_id]);

            if (!$content)
            {
                return null;
            }

            return $this->get_content($content);
        }
        catch (\Exception)
        {
            return null;
        }
    }

    public function get_ids_by_ispring_module_id(int $ispring_module_id): array
    {
        try
        {
            $contents = $this->database->get_records(
                'ispring_content',
                ['ispring_id' => $ispring_module_id],
                '',
                'id'
            );

            $result = [];
            foreach ($contents as $content)
            {
                $result[] = $content->id;
            }

            return $result;
        }
        catch (\Exception)
        {
            return [];
        }
    }

    private function get_content(\stdClass $content_data): content
    {
        return new content(
            $content_data->id,
            $content_data->file_id,
            $content_data->ispring_id,
            $content_data->creation_time,
            $content_data->filename,
            $content_data->path,
            $content_data->version,
            $content_data->report_path,
            $content_data->report_filename,
        );
    }
}