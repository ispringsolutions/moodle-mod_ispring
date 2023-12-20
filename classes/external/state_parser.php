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

namespace mod_ispring\external;

class state_parser
{
    public static function parse_state(string $data): state
    {
        $parsed_data = self::parse_json($data);
        self::require_properties($parsed_data, ['duration', 'id', 'persistState', 'status', 'playerId']);
        self::require_properties_are_numeric($parsed_data, ['duration']);

        return new state(
            (int)$parsed_data->duration,
            $parsed_data->id,
            json_encode($parsed_data->persistState),
            $parsed_data->status,
            $parsed_data->playerId,
        );
    }

    public static function parse_result_state(string $data): result_state
    {
        $state = self::parse_state($data);

        $parsed_data = self::parse_json($data);
        self::require_properties_are_numeric($parsed_data, ['maxScore', 'minScore', 'passingScore', 'score']);

        return new result_state(
            $state,
            property_exists($parsed_data, 'maxScore') ? $parsed_data->maxScore : null,
            property_exists($parsed_data, 'minScore') ? $parsed_data->minScore : null,
            property_exists($parsed_data, 'passingScore') ? $parsed_data->passingScore : null,
            property_exists($parsed_data, 'score') ? $parsed_data->score : null,
            property_exists($parsed_data, 'detailedReport') ? json_encode($parsed_data->detailedReport) : null,
        );
    }

    public static function parse_start_state(string $data): start_state
    {
        $parsed_data = self::parse_json($data);

        self::require_properties($parsed_data, ['status', 'playerId', 'sessionRestored']);

        return new start_state(
            $parsed_data->status,
            $parsed_data->playerId,
            $parsed_data->sessionRestored,
        );
    }

    private static function require_properties($data, array $properties): void
    {
        foreach ($properties as $property)
        {
            if (!property_exists($data, $property))
            {
                throw new \invalid_parameter_exception("Key {$property} is missing");
            }
        }
    }

    private static function require_properties_are_numeric($data, array $properties): void
    {
        foreach ($properties as $property)
        {
            if (property_exists($data, $property) && !is_numeric($data->$property))
            {
                throw new \invalid_parameter_exception("Key {$property} is not a number");
            }
        }
    }

    private static function parse_json(string $json): \stdClass
    {
        try
        {
            return json_decode($json);
        }
        catch (\Throwable)
        {
            throw new \invalid_parameter_exception("Wrong data format");
        }
    }
}