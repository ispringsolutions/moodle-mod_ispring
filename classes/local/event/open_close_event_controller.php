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

namespace mod_ispring\local\event;

use calendar_event;
use mod_ispring\local\common\infrastructure\lock\locking_service;

class open_close_event_controller {
    private const SET_EVENT_TIMEOUT = 10;

    /**
     * Create, update and delete new events given as timeopen and timeclose by $ispring.
     *
     * @param \stdClass $ispring
     * @return void
     */
    public static function set_events(\stdClass $ispring): void {
        $lock = locking_service::get_lock('set_events', "ispring_events:{$ispring->instance}", self::SET_EVENT_TIMEOUT);

        self::process_event($ispring, $ispring->timeopen, event_types::OPEN);
        self::process_event($ispring, $ispring->timeclose, event_types::CLOSE);
    }

    private static function process_event(\stdClass $ispring, ?int $time, string $eventtype): void {
        global $CFG;

        require_once($CFG->dirroot . '/calendar/lib.php');

        $eventid = self::get_event_id($ispring->instance, $eventtype);
        if ($time > 0) {
            $event = self::create_event($eventtype, $ispring, $eventid);

            if ($eventid) {
                $calendarevent = calendar_event::load($eventid);
                $calendarevent->update($event, false);
            } else {
                calendar_event::create($event, false);
            }
        } else if ($eventid) {
            $calendarevent = calendar_event::load($eventid);
            $calendarevent->delete();
        }
    }

    /**
     * Get event id
     *
     * @param int $instance
     * @param string $eventtype
     * @return int
     */
    private static function get_event_id(int $instance, string $eventtype): int {
        global $DB;

        return $DB->get_field('event', 'id',
            ['modulename' => 'ispring', 'instance' => $instance, 'eventtype' => $eventtype]);
    }

    /**
     * Generate event by params
     *
     * @param string $eventtype
     * @param \stdClass $ispring
     * @param int $eventid
     * @return \stdClass
     */
    private static function create_event(string $eventtype, \stdClass $ispring, int $eventid): \stdClass {
        $event = $eventtype == event_types::OPEN
            ? self::create_default_open_event($ispring)
            : self::create_default_close_event($ispring);

        $eventid
            ? self::add_params_to_existent_event($event, $eventid)
            : self::add_params_to_nonexistent_event($event, $ispring);

        return $event;
    }

    /**
     * Generate default event
     *
     * @param \stdClass $ispring
     * @return \stdClass
     */
    private static function create_default_event(\stdClass $ispring): \stdClass {
        $event = new \stdClass();
        $event->description = format_module_intro('ispring', $ispring, $ispring->coursemodule, false);
        $event->format = FORMAT_HTML;
        $event->visible = instance_is_visible('ispring', $ispring);
        $event->timeduration = 0;

        return $event;
    }

    /**
     * Generate default timeclose event
     *
     * @param \stdClass $ispring
     * @return \stdClass
     */
    private static function create_default_close_event(\stdClass $ispring): \stdClass {
        $event = self::create_default_event($ispring);

        $event->type = CALENDAR_EVENT_TYPE_ACTION;
        $event->eventtype = event_types::CLOSE;
        $event->timestart = $ispring->timeclose;
        $event->timesort = $ispring->timeclose;
        $event->name = get_string('calendarend', 'ispring', $ispring->name);

        return $event;
    }

    /**
     * Generate default timeopen event
     *
     * @param \stdClass $ispring
     * @return \stdClass
     */
    private static function create_default_open_event(\stdClass $ispring): \stdClass {
        $event = self::create_default_event($ispring);
        $event->type = CALENDAR_EVENT_TYPE_STANDARD;
        $event->eventtype = event_types::OPEN;
        $event->timestart = $ispring->timeopen;
        $event->timesort = $ispring->timeopen;
        $event->name = get_string('calendarstart', 'ispring', $ispring->name);

        return $event;
    }

    /**
     * Add params for existing event
     *
     * @param \stdClass $event
     * @param int $eventid
     * @return void
     */
    private static function add_params_to_existent_event(\stdClass &$event, int $eventid): void {
        $event->id = $eventid;
    }

    /**
     * Add params for nonexistent event
     *
     * @param \stdClass $event
     * @param \stdClass $ispring
     * @return void
     */
    private static function add_params_to_nonexistent_event(\stdClass &$event, \stdClass $ispring): void {
        $event->courseid = $ispring->course;
        $event->groupid = 0;
        $event->userid = 0;
        $event->modulename = 'ispring';
        $event->instance = $ispring->instance;
    }
}
