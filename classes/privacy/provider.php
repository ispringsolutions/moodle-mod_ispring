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

namespace mod_ispring\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider {
    private const MOD_NAME = 'ispring';

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'ispring_session',
            [
                'id' => 'privacy:metadata:ispring_session:id',
                'user_id' => 'privacy:metadata:ispring_session:user_id',
                'ispring_content_id' => 'privacy:metadata:ispring_session:ispring_content_id',
                'status' => 'privacy:metadata:ispring_session:status',
                'score' => 'privacy:metadata:ispring_session:score',
                'begin_time' => 'privacy:metadata:ispring_session:begin_time',
                'attempt' => 'privacy:metadata:ispring_session:attempt',
                'end_time' => 'privacy:metadata:ispring_session:end_time',
                'duration' => 'privacy:metadata:ispring_session:duration',
                'persist_state' => 'privacy:metadata:ispring_session:persist_state',
                'persist_state_id' => 'privacy:metadata:ispring_session:persist_state_id',
                'max_score' => 'privacy:metadata:ispring_session:max_score',
                'min_score' => 'privacy:metadata:ispring_session:min_score',
                'passing_score' => 'privacy:metadata:ispring_session:passing_score',
                'detailed_report' => 'privacy:metadata:ispring_session:detailed_report',
                'player_id' => 'privacy:metadata:ispring_session:player_id',
                'suspend_data' => 'privacy:metadata:ispring_session:suspend_data',
            ],
            'privacy:metadata:ispring_session'
        );

        return $collection;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        $params = [
            'module' => self::MOD_NAME,
            'cmid' => $context->instanceid,
        ];

        $sql = "SELECT iss.user_id
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :module
                  JOIN {ispring} isp ON isp.id = cm.instance
                  JOIN {ispring_content} isc ON isc.ispring_id = isp.id
                  JOIN {ispring_session} iss ON iss.ispring_content_id = isc.id
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql('user_id', $sql, $params);
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid User ID
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $params = [
            'context_level' => CONTEXT_MODULE,
            'module' => self::MOD_NAME,
        ];

        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :context_level
                  JOIN {modules} m ON m.id = cm.module AND m.name = :module
                  JOIN {ispring} isp ON isp.id = cm.instance
                  JOIN {ispring_content} isc ON isc.ispring_id = isp.id
                  JOIN {ispring_session} iss ON iss.ispring_content_id = isc.id
                 WHERE iss.user_id = {$userid}";
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();

        [$contextsql, $contextparams] = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $contextparams['module'] = self::MOD_NAME;
        $contextparams['user_id'] = $user->id;

        $sql = "SELECT iss.*, cm.id AS cmid
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :module
                  JOIN {ispring} isp ON isp.id = cm.instance
                  JOIN {ispring_content} isc ON isc.ispring_id = isp.id
                  JOIN {ispring_session} iss ON iss.ispring_content_id = isc.id
                 WHERE iss.user_id = :user_id
                       AND c.id {$contextsql}";

        $sessions = $DB->get_records_sql($sql, $contextparams);
        $result = [];
        foreach ($sessions as $session) {
            $context = \context_module::instance($session->cmid);
            $contextdata = helper::get_context_data($context, $user);
            $result[] = $session;
            $contextdata = (object)array_merge((array)$contextdata, ['sessions' => $result]);

            helper::export_context_files($context, $user);
            writer::with_context($context)->export_data([], $contextdata);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$cm = get_coursemodule_from_id(self::MOD_NAME, $context->instanceid)) {
            return;
        }
        $ispring = $DB->get_record('ispring', ['id' => $cm->instance]);
        $contents = array_keys($DB->get_records('ispring_content', ['ispring_id' => $ispring->id], '', 'id'));

        [$usersql, $userparams] = $DB->get_in_or_equal($userlist->get_userids());
        [$contentsql, $contentparams] = $DB->get_in_or_equal($contents);
        $params = array_merge($contentparams, $userparams);

        $DB->delete_records_select('ispring_session', "ispring_content_id {$contentsql} AND user_id {$usersql}", $params);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        // Get the course module.
        if (!$cm = get_coursemodule_from_id(self::MOD_NAME, $context->instanceid)) {
            return;
        }

        $ispringid = $cm->instance;

        $contents = array_keys($DB->get_records('ispring_content', ['ispring_id' => $ispringid], '', 'id'));
        [$contentsql, $contentparams] = $DB->get_in_or_equal($contents);

        $DB->delete_records_select('ispring_session', "ispring_content_id {$contentsql}", $contentparams);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        foreach ($contextlist as $context) {
            if (!$cm = get_coursemodule_from_id(self::MOD_NAME, $context->instanceid)) {
                continue;
            }
            $ispring = $DB->get_record('ispring', ['id' => $cm->instance]);
            $ispringcontents = array_keys($DB->get_records('ispring_content', ['ispring_id' => $ispring->id], '', 'id'));
            [$contentsql, $contentparams] = $DB->get_in_or_equal($ispringcontents);

            $DB->delete_records_select(
                'ispring_session',
                "ispring_content_id {$contentsql} AND user_id = {$userid}",
                $contentparams);
        }
    }
}
