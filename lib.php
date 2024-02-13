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

use mod_ispring\di_container;
use mod_ispring\event\event_types;
use mod_ispring\event\open_close_event_controller;
use mod_ispring\mapper\std_mapper;
use mod_ispring\use_case\create_or_update_ispring_module_use_case;
use mod_ispring\use_case\delete_ispring_module_use_case;
use mod_ispring\content\infrastructure\file_storage as ispring_file_storage;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../config.php');

/**
 * Returns true, false or null depending on plugin support for $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return bool|null True if the feature is supported, false if not, null if unknown.
 */
function ispring_supports(string $feature): ?bool
{
    switch ($feature)
    {
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Creates a new instance of ispring activity module using the data from $new_ispring.
 * Additional properties for $new_ispring can be defined in {@see mod_form.php}.
 *
 * @param stdClass $new_ispring
 * @param stdClass $mform
 * @return int
 */
function ispring_add_instance($new_ispring, $mform = null): int
{
    global $USER;

    $ispring_module_api = di_container::get_ispring_module_api();
    $content_api = di_container::get_content_api();

    $module_context = context_module::instance($new_ispring->coursemodule);
    $user_context = context_user::instance($USER->id);

    $use_case = new create_or_update_ispring_module_use_case($ispring_module_api, $content_api);
    $ispring_module_id = $use_case->create($new_ispring, $module_context->id, $user_context->id);

    $ispring_instance = std_mapper::ispring_module_output_to_std_class(
        $ispring_module_api->get_by_id($ispring_module_id),
    );

    ispring_update_grades($ispring_instance);
    if ($new_ispring->completionexpected)
    {
        \core_completion\api::update_completion_date_event(
            $new_ispring->coursemodule,
            'ispring',
            $ispring_instance,
            $new_ispring->completionexpected,
        );
    }

    $new_ispring->instance = $ispring_module_id;
    open_close_event_controller::set_events($new_ispring);

    return $ispring_module_id;
}

/**
 * Serves ispring content, introduction images videos, fonts, etc.
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $force_download whether force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function ispring_pluginfile(
    stdClass $course,
    stdClass $cm,
    stdClass $context,
    string $filearea,
    array $args,
    bool $force_download,
    array $options = []
): bool
{
    require_login($course, true, $cm);

    $content_api = di_container::get_content_api();
    return $content_api->present_file($context->id, $filearea, $args, $force_download, $options);
}

/**
 * Lists all file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array
 */
function ispring_get_file_areas(stdClass $course, stdClass $cm, stdClass $context): array
{
    return [
        'package' => get_string('areapackage', 'ispring'),
    ];
}

/**
 * File browsing support for ispring module.
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int|null $item_id
 * @param string|null $filepath
 * @param string|null $filename
 * @return file_info|null
 */
function ispring_get_file_info(
    file_browser $browser,
    array $areas,
    stdClass $course,
    stdClass $cm,
    stdClass $context,
    string $filearea,
    ?int $item_id,
    ?string $filepath,
    ?string $filename
): ?file_info
{
    global $CFG;

    // show only packages
    if ($filearea !== ispring_file_storage::PACKAGE_FILEAREA)
    {
        return null;
    }

    $fs = get_file_storage();
    $filepath = $filepath ?? '/';
    $filename = $filename ?? '.';
    $item_id = $item_id ?? 0;

    if (!$stored_file = $fs->get_file($context->id, ispring_file_storage::COMPONENT_NAME, $filearea, $item_id, $filepath, $filename))
    {
        return null;
    }

    $url_base = $CFG->wwwroot . '/pluginfile.php';
    return new file_info_stored(
        $browser,
        $context,
        $stored_file,
        $url_base,
        $areas[$filearea],
        false,
        true,
        false,
        false
    );
}

/**
 * Updates an existing instance of ispring activity module with the data from $new_ispring.
 * Additional properties for $new_ispring can be defined in {@see mod_form.php}.
 *
 * @param stdClass $new_ispring object containing all the properties required to update an instance
 * @return bool true if ispring activity module was updated successfully
 */
function ispring_update_instance(stdClass $new_ispring): bool
{
    global $USER;

    $ispring_module_api = di_container::get_ispring_module_api();
    $content_api = di_container::get_content_api();

    $module_context = context_module::instance($new_ispring->coursemodule);
    $user_context = context_user::instance($USER->id);

    $use_case = new create_or_update_ispring_module_use_case($ispring_module_api, $content_api);
    if (!$use_case->update($new_ispring, $module_context->id, $user_context->id))
    {
        return false;
    }

    $ispring_instance = std_mapper::ispring_module_output_to_std_class(
        $ispring_module_api->get_by_id($new_ispring->instance),
    );

    ispring_update_grades($ispring_instance);
    \core_completion\api::update_completion_date_event(
        $new_ispring->coursemodule,
        'ispring',
        $ispring_instance,
        $new_ispring->completionexpected ? $new_ispring->completionexpected : null,
    );

    open_close_event_controller::set_events($new_ispring);

    return true;
}

function ispring_delete_instance($id): bool
{
    global $DB;
    $ispring_module_api = di_container::get_ispring_module_api();
    $module = $ispring_module_api->get_by_id($id);
    if (!$module)
    {
        return false;
    }

    $use_case = new delete_ispring_module_use_case($ispring_module_api, $module);

    if ($use_case->delete())
    {
        $events = $DB->get_records('event', ['modulename' => 'ispring', 'instance' => $module->get_id()]);
        foreach ($events as $event) {
            $event = calendar_event::load($event);
            $event->delete();
        }

        return ispring_grade_item_delete(std_mapper::ispring_module_output_to_std_class($module)) != GRADE_UPDATE_FAILED;
    }

    return false;
}

/**
 * It is safe to rely on PAGE here as we will only ever be within the module
 * context when this is called
 *
 * @param settings_navigation $settings navigation_node object.
 * @param navigation_node $ispringnode navigation_node object.
 * @return void
 */
function ispring_extend_settings_navigation(settings_navigation $settings, navigation_node $ispringnode): void
{
    if (has_capability('mod/ispring:viewallreports', $settings->get_page()->cm->context))
    {
        $url = new moodle_url('/mod/ispring/report.php', ['id' => $settings->get_page()->cm->id]);
        $new_node = $ispringnode->add(
            get_string('report', 'ispring'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'report'
        );
    }
}

/**
 * Creates or updates grade item for the given mod_ispring instance.
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $module_instance Instance object with extra cmidnumber and modname property.
 * @param mixed $grades Optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void
 * @throws coding_exception
 */
function ispring_grade_item_update(stdClass $module_instance, $grades = null): void
{
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $details = [
        'itemname' => clean_param($module_instance->name, PARAM_NOTAGS),
    ];

    if (property_exists($module_instance, 'max_score'))
    {
        $details['gradetype'] = GRADE_TYPE_VALUE;
        $details['grademax'] = $module_instance->max_score;
        $details['grademin'] = $module_instance->min_score ?? 0;

        $details['hidden'] = false;
    }

    if ($grades === 'reset')
    {
        $details['reset'] = true;
        $grades = null;
    }

    grade_update(
        'mod/ispring',
        $module_instance->course,
        'mod',
        'ispring',
        $module_instance->id,
        0,
        $grades,
        $details
    );
}

/**
 * Update grades in central gradebook
 *
 * @param stdClass $module_instance Instance object with extra cmidnumber and modname property.
 * @param int $user_id specific user only, 0 means all users.
 * @param bool $null_if_none If a single user is specified and $null_if_none is true a grade item with a null rawgrade will be inserted
 * @throws coding_exception
 */
function ispring_update_grades(stdClass $module_instance, int $user_id = 0, bool $null_if_none = true): void
{
    if ($grades = ispring_get_user_grades($module_instance, $user_id))
    {
        count($grades) === 0
            ? ispring_grade_item_update($module_instance)
            : ispring_grade_item_update($module_instance, $grades);
        return;
    }
    if ($null_if_none && $user_id)
    {
        $grade = new stdClass();
        $grade->userid = $user_id;
        $grade->rawgrade = null;
        ispring_grade_item_update($module_instance, $grade);
    }
    else
    {
        ispring_grade_item_update($module_instance);
    }
}

/**
 * Delete grade item for given mod_ispring instance.
 *
 * @param stdClass $module_instance Instance object.
 * @return int
 */
function ispring_grade_item_delete($module_instance): int
{
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('/mod/ispring', $module_instance->course, 'mod', 'ispring',
        $module_instance->id, 0, null, ['deleted' => 1]);
}

function ispring_get_user_grades(stdClass $module_instance, int $user_id): ?array
{
    return di_container::get_session_api()->get_grades_for_gradebook($module_instance->id, $user_id);
}

/**
 * Takes a calendar event and provides the action associated with it, or null if there is none.
 * This function is used by the block_myoverview plugin.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $user_id User id to use for all capability checks, etc. Set to 0 for current user (default).
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_ispring_core_calendar_provide_event_action(
    calendar_event $event,
    \core_calendar\action_factory $factory,
    int $user_id = null
): ?\core_calendar\local\event\entities\action_interface
{
    $mod_info = get_fast_modinfo($event->courseid);
    $cm = $mod_info->get_instances_of('ispring')[$event->instance];

    if (!$cm)
    {
        return null;
    }

    return $factory->create_instance(
        get_string('myoverviewactionlink', 'ispring'),
        new \moodle_url('/mod/ispring/view.php', ['id' => $cm->id]),
        1,
        true,
    );
}

/**
 * Create cached info for ispring course module.
 *
 * @param stdClass $course_module The course_module object (record).
 * @return cached_cm_info|bool An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function ispring_get_coursemodule_info(stdClass $course_module)
{
    if (!$module = di_container::get_ispring_module_api()->get_by_id($course_module->instance))
    {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $module->get_name();
    $time_open = $module->get_time_open();
    $time_close = $module->get_time_close();

    if ($time_open)
    {
        $result->customdata['timeopen'] = $time_open;
    }
    if ($time_close)
    {
        $result->customdata['timeclose'] = $time_close;
    }

    return $result;
}

/**
 * This function calculates the minimum and maximum cutoff values for the timestart of
 * the given event.
 *
 * It will return an array with two values, the first being the minimum cutoff value and
 * the second being the maximum cutoff value. Either or both values can be null, which
 * indicates there is no minimum or maximum, respectively.
 *
 * If a cutoff is required then the function must return an array containing the cutoff
 * timestamp and error string to display to the user if the cutoff value is violated.
 *
 * A minimum and maximum cutoff return value will look like:
 * [
 *     [1505704373, 'The date must be after this date'],
 *     [1506741172, 'The date must be before this date']
 * ]
 *
 * @param calendar_event $event The calendar event to get the time range for
 * @param stdClass $module The module instance to get the range from
 * @return array
 */
function mod_ispring_core_calendar_get_valid_event_timestart_range(calendar_event $event, stdClass $module): array
{
    $min_date = null;
    $max_date = null;

    if ($event->eventtype == event_types::OPEN && !empty($module->timeclose))
    {
        $min_date = [
            $module->timeclose,
            get_string('openafterclose', 'ispring'),
        ];
    } else if ($event->eventtype == event_types::CLOSE && !empty($module->timeopen))
    {
        $max_date = [
            $module->timeopen,
            get_string('closebeforeopen', 'ispring'),
        ];
    }

    return [$min_date, $max_date];
}