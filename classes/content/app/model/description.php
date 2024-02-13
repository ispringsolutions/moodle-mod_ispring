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

namespace mod_ispring\content\app\model;

class description
{
    public const FILENAME = 'description.json';

    private string $content_name;
    private string $description;
    private description_params $description_params;

    public function __construct(string $content_name, string $description, description_params $description_params)
    {
        $this->content_name = $content_name;
        $this->description = $description;
        $this->description_params = $description_params;
    }

    /**
     * @param array $data
     * @return description|null
     */
    public static function create(array $data): ?description
    {
        if (array_key_exists('course_name', $data)
            && array_key_exists('params', $data)
            && array_key_exists('description', $data))
        {
            if (!$params = description_params::create($data['params']))
            {
                return null;
            }
            return new description(
                $data['course_name'],
                $data['description'],
                $params,
            );
        }

        return null;
    }

    /**
     * @return string
     */
    public function get_content_name(): string
    {
        return $this->content_name;
    }

    /**
     * @return string
     */
    public function get_description(): string
    {
        return $this->description;
    }

    /**
     * @return description_params
     */
    public function get_description_params(): description_params
    {
        return $this->description_params;
    }
}