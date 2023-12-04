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
 * @package     mod_ispring
 * @copyright   2023 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ispring\content\infrastructure;

use mod_ispring\content\app\model\content;
use mod_ispring\content\app\repository\content_repository_interface;
use stdClass;

class content_repository implements content_repository_interface
{
    private readonly \moodle_database $database;

    public function __construct()
    {
        global $DB;
        $this->database = $DB;
    }

    public function add(content $content): int
    {
        $new_content = new stdClass();
        $new_content->creation_time = $content->get_creation_time();
        $new_content->file_id = $content->get_file_id();
        $new_content->filename = $content->get_content_path()->get_filename();
        $new_content->ispring_id = $content->get_ispring_module_id();
        $new_content->path = $content->get_content_path()->get_path();
        $new_content->version = $content->get_version();
        if ($report_path = $content->get_report_path())
        {
            $new_content->report_path = $report_path->get_path();
            $new_content->report_filename = $report_path->get_filename();
        }

        $transaction = $this->database->start_delegated_transaction();
        try
        {
            $id = $this->database->insert_record('ispring_content', $new_content);
            $transaction->allow_commit();
            return $id;
        }
        catch (\Exception $e)
        {
            $transaction->rollback($e);
            throw new \RuntimeException('Cannot add content to database');
        }
    }

    public function remove(int $id): bool
    {
        $transaction = $this->database->start_delegated_transaction();
        try
        {
            $result = $this->database->delete_records('ispring_content', ['id' => $id]);
            $transaction->allow_commit();
            return $result;
        }
        catch (\Exception $e)
        {
            $transaction->rollback($e);
            throw new \RuntimeException('Cannot remove content from database');
        }
    }
}