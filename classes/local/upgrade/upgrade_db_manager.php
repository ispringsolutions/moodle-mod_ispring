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

namespace mod_ispring\local\upgrade;

use xmldb_field;
use xmldb_key;
use xmldb_table;

final class upgrade_db_manager {
    private \database_manager $dbmanager;

    public function __construct() {
        global $DB;
        $this->dbmanager = $DB->get_manager();
    }

    public function upgrade_from($oldversion): void {
        if ($oldversion < 2023090720) {
            $this->upgrade_to_2023090720();
        }
        if ($oldversion < 2023090721) {
            $this->upgrade_to_2023090721();
        }
        if ($oldversion < 2023090722) {
            $this->upgrade_to_2023090722();
        }
        if ($oldversion < 2023090725) {
            $this->upgrade_to_2023090725();
        }
        if ($oldversion < 2023090728) {
            $this->upgrade_to_2023090728();
        }
        if ($oldversion < 2023090733) {
            $this->upgrade_to_2023090733();
        }
        if ($oldversion < 2023120302) {
            $this->upgrade_to_2023120302();
        }
        if ($oldversion < 2024012203) {
            $this->upgrade_to_2024012203();
        }
        if ($oldversion < 2024022901) {
            $this->upgrade_to_2024022901();
        }
    }

    private function upgrade_to_2023090720(): void {
        $dbman = $this->dbmanager;

        $table = new xmldb_table('ispring_session');

        $field = new xmldb_field(
            'persist_state',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null,
            'duration');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'persist_state_id',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null,
            'persist_state');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'max_score',
            XMLDB_TYPE_NUMBER,
            '10, 5',
            null,
            null,
            null,
            null,
            'persist_state_id');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'min_score',
            XMLDB_TYPE_NUMBER,
            '10, 5',
            null,
            XMLDB_NOTNULL,
            null,
            '0',
            'max_score');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'passing_score',
            XMLDB_TYPE_NUMBER,
            '10, 5',
            null,
            null,
            null,
            null,
            'min_score');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'score',
            XMLDB_TYPE_NUMBER,
            '10, 5',
            null,
            null,
            null,
            null,
            'min_score');
        $dbman->change_field_type($table, $field);

        // Ispring savepoint reached.
        upgrade_mod_savepoint(true, 2023090720, 'ispring');
    }

    private function upgrade_to_2023090721(): void {
        $dbman = $this->dbmanager;

        $table = new xmldb_table('ispring_content');

        $field = new xmldb_field(
            'report_path',
            XMLDB_TYPE_CHAR,
            '128',
            null,
            null,
            null,
            null,
            'version'
        );

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'report_filename',
            XMLDB_TYPE_CHAR,
            '128',
            null,
            null,
            null,
            null,
            'report_path'
        );

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2023090721, 'ispring');
    }

    private function upgrade_to_2023090722(): void {
        $dbman = $this->dbmanager;

        $table = new xmldb_table('ispring_session');
        $field = new xmldb_field(
            'detailed_report',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null,
            'passing_score');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2023090722, 'ispring');
    }

    private function upgrade_to_2023090725(): void {
        $dbman = $this->dbmanager;

        $table = new xmldb_table('ispring');

        $field = new xmldb_field(
            'intro',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null,
            'name');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'introformat',
            XMLDB_TYPE_INTEGER,
            '4',
            null,
            XMLDB_NOTNULL,
            null,
            '0',
            'intro');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $key = new xmldb_key(
            'course_id',
            XMLDB_KEY_FOREIGN,
            ['course'],
            'course',
            ['id']);

        $dbman->add_key($table, $key);

        $table = new xmldb_table('ispring_content');

        $key = new xmldb_key(
            'ispring_id',
            XMLDB_KEY_FOREIGN,
            ['ispring_id'],
            'ispring',
            ['id']);

        $dbman->add_key($table, $key);

        $table = new xmldb_table('ispring_session');

        $key = new xmldb_key(
            'ispring_content_id',
            XMLDB_KEY_FOREIGN,
            ['ispring_content_id'],
            'ispring_content',
            ['id']);

        $dbman->add_key($table, $key);

        upgrade_mod_savepoint(true, 2023090725, 'ispring');
    }

    private function upgrade_to_2023090728(): void {
        global $DB;
        $records = $DB->get_records_sql("
                 SELECT iss.id session_id, isc.ispring_id ispring_module_id, iss.user_id user_id
                   FROM {ispring_content} isc
                   JOIN {ispring_session} iss ON isc.id = iss.ispring_content_id
               ORDER BY isc.ispring_id, iss.user_id, isc.version, iss.attempt",
        );

        $currentispringmoduleid = 0;
        $currentuserid = 0;

        foreach ($records as $record) {
            if ($record->user_id !== $currentuserid
                || $record->ispring_module_id !== $currentispringmoduleid) {
                $currentispringmoduleid = $record->ispring_module_id;
                $currentuserid = $record->user_id;
                $currentattempt = 0;
            }
            $DB->update_record('ispring_session', [
                'id' => $record->session_id,
                'attempt' => ++$currentattempt,
            ]);
        }

        upgrade_mod_savepoint(true, 2023090728, 'ispring');
    }

    private function upgrade_to_2023090733(): void {
        $dbman = $this->dbmanager;

        $table = new xmldb_table('ispring_content');
        $field = new xmldb_field('context_id');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2023090733, 'ispring');
    }

    private function upgrade_to_2023120302(): void {
        $dbman = $this->dbmanager;

        $table = new xmldb_table('ispring_session');
        $field = new xmldb_field(
            'player_id',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null,
            'detailed_report'
        );

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2023120302, 'ispring');
    }

    private function upgrade_to_2024012203(): void {
        $dbman = $this->dbmanager;

        $table = new xmldb_table('ispring');
        $field = new xmldb_field(
            'timeopen',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            null,
            0,
            'gradepass'
        );

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'timeclose',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            null,
            0,
            'timeopen'
        );

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('gradepass');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2024012203, 'ispring');
    }

    private function upgrade_to_2024022901(): void {
        $dbman = $this->dbmanager;

        $table = new xmldb_table('ispring_session');
        $field = new xmldb_field('suspend_data', XMLDB_TYPE_TEXT, null, null, null, null, null, 'player_id');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2024022901, 'ispring');
    }
}
