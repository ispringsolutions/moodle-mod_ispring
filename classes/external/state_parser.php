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

namespace mod_ispring\external;

class state_parser {
    public static function parse_state(string $json): state {
        $data = self::parse_json($json);
        self::require_properties($data, ['duration', 'id', 'persistState', 'status', 'playerId']);
        self::require_properties_are_numeric($data, ['duration']);

        return new state(
            (int)$data->duration,
            $data->id,
            json_encode($data->persistState),
            $data->status,
            $data->playerId,
        );
    }

    public static function parse_result_state(string $json): result_state {
        $state = self::parse_state($json);

        $data = self::parse_json($json);
        self::require_properties_are_numeric($data, ['maxScore', 'minScore', 'passingScore', 'score']);

        return new result_state(
            $state,
            property_exists($data, 'maxScore') ? $data->maxScore : null,
            property_exists($data, 'minScore') ? $data->minScore : null,
            property_exists($data, 'passingScore') ? $data->passingScore : null,
            property_exists($data, 'score') ? $data->score : null,
            property_exists($data, 'detailedReport') ? json_encode($data->detailedReport) : null,
        );
    }

    public static function parse_start_state(string $json): start_state {
        $data = self::parse_json($json);

        self::require_properties($data, ['status', 'playerId', 'sessionRestored']);

        return new start_state(
            $data->status,
            $data->playerId,
            $data->sessionRestored,
        );
    }

    private static function require_properties($data, array $properties): void {
        foreach ($properties as $property) {
            if (!property_exists($data, $property)) {
                throw new \invalid_parameter_exception("Key {$property} is missing");
            }
        }
    }

    private static function require_properties_are_numeric($data, array $properties): void {
        foreach ($properties as $property) {
            if (property_exists($data, $property) && !is_numeric($data->$property)) {
                throw new \invalid_parameter_exception("Key {$property} is not a number");
            }
        }
    }

    private static function parse_json(string $json): \stdClass {
        try {
            return json_decode($json);
        } catch (\Throwable $e) {
            throw new \invalid_parameter_exception("Wrong data format");
        }
    }
}
