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

namespace mod_ispring\ispring_module\infrastructure\query;

use mod_ispring\ispring_module\app\model\description;
use mod_ispring\ispring_module\app\query\ispring_module_query_service_interface;
use mod_ispring\ispring_module\app\query\model\ispring_module_model;

class ispring_module_query_service implements ispring_module_query_service_interface
{
    private mixed $database;

    public function __construct()
    {
        global $DB;
        $this->database = $DB;
    }

    public function exists(int $id): bool
    {
        try
        {
            $ispring = $this->database->get_record('ispring', ['id' => $id]);
            return (bool) $ispring;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function get_by_id(int $id): ?ispring_module_model
    {
        try
        {
            $ispring = $this->database->get_record('ispring', ['id' => $id]);
            if (!$ispring)
            {
                return null;
            }

            $description = null;
            if ($ispring->intro !== null)
            {
                $description = new description(
                    $ispring->intro,
                    $ispring->introformat,
                );
            }

            return new ispring_module_model(
                $ispring->id,
                $ispring->name,
                $ispring->course,
                $ispring->grade,
                $ispring->grademethod,
                $description,
            );
        }
        catch (\Exception)
        {
            return null;
        }
    }
}