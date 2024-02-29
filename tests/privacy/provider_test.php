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
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use mod_ispring\session\domain\model\session_state;

class provider_test extends \core_privacy\tests\provider_testcase
{
    private const COMPONENT_NAME = 'mod_ispring';
    private const FIRST_SESSION_COUNT = 2;
    private const SECOND_SESSION_COUNT = 3;
    private const TOTAL_SESSION_COUNT = self::FIRST_SESSION_COUNT + self::SECOND_SESSION_COUNT;

    public function test_get_metadata(): void
    {
        global $DB;

        $collection = new collection(self::COMPONENT_NAME);
        $new_collection = provider::get_metadata($collection);
        $item_collection = $new_collection->get_collection();
        $this->assertCount(1, $item_collection);

        $table = reset($item_collection);
        $this->assertEquals('ispring_session', $table->get_name());

        $privacy_fields = $table->get_privacy_fields();
        $columns = $DB->get_columns('ispring_session');
        $this->assertCount(count($columns), $privacy_fields);
        $this->assertEquals(array_keys($columns), array_keys($privacy_fields));
    }

    public function test_get_contexts_for_userid_no_data(): void
    {
        global $USER;
        $this->assertEquals(0, $USER->id);

        $context_list = provider::get_contexts_for_userid($USER->id);
        $this->assertEmpty($context_list);
    }

    public function test_get_only_one_context_for_existing_user_id(): void
    {
        global $USER;
        $context_count = 1;

        [$context, $cm] = $this->create_context_and_cm();
        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);

        $context_list = provider::get_contexts_for_userid($USER->id);
        $context_for_user = $context_list->current();

        $this->assertCount($context_count, $context_list);
        $this->assertEquals($context->id, $context_for_user->id);
    }

    public function test_get_only_no_context_for_user_with_no_sessions(): void
    {
        global $USER;
        $second_user = self::getDataGenerator()->create_user();

        $cm = $this->create_cm();
        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);

        $context_list = provider::get_contexts_for_userid($second_user->id);

        $this->assertEmpty($context_list);
    }

    public function test_get_no_users_in_context(): void
    {
        [$context,] = $this->create_context_and_cm();

        $user_list = new userlist($context, self::COMPONENT_NAME);
        provider::get_users_in_context($user_list);

        $this->assertEmpty($user_list);
    }

    public function test_get_one_user_in_context(): void
    {
        global $USER;
        $user_count = 1;

        [$context, $cm] = $this->create_context_and_cm();
        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);

        $user_list = new userlist($context, self::COMPONENT_NAME);
        provider::get_users_in_context($user_list);

        $this->assertCount($user_count, $user_list);
        $actual = $user_list->get_userids();
        $expected = [$USER->id];

        $this->assertEquals($expected, $actual);
    }

    public function test_get_few_users_in_context(): void
    {
        global $USER;
        [$context, $cm] = $this->create_context_and_cm();
        $second_user = self::getDataGenerator()->create_user();

        $user_count = 2;

        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);
        $this->create_content_and_session($cm, $second_user->id, self::SECOND_SESSION_COUNT);

        $user_list = new userlist($context, self::COMPONENT_NAME);
        provider::get_users_in_context($user_list);

        $this->assertCount($user_count, $user_list);
        $actual = $user_list->get_userids();
        $expected = [$USER->id, $second_user->id];

        $this->assertEquals($expected, $actual);
    }

    public function test_delete_empty_data(): void
    {
        global $DB;
        [$context,] = $this->create_context_and_cm();

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(0, $count);

        // Delete data based on the context
        provider::delete_data_for_all_users_in_context($context);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(0, $count);
    }

    public function test_delete_data_for_all_users_with_one_context(): void
    {
        global $DB, $USER;

        [$context, $cm] = $this->create_context_and_cm();
        $second_user = self::getDataGenerator()->create_user();

        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);
        $this->create_content_and_session($cm, $second_user->id, self::SECOND_SESSION_COUNT);

        // Delete data based on the context.
        provider::delete_data_for_all_users_in_context($context);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(0, $count);
    }

    public function test_delete_data_for_all_users_with_few_context(): void
    {
        global $DB, $USER;

        [$context, $cm] = $this->create_context_and_cm();
        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);
        $second_user = self::getDataGenerator()->create_user();

        $cm_second = $this->create_cm();
        $this->create_content_and_session($cm_second, $second_user->id, self::SECOND_SESSION_COUNT);

        // Delete data based on the context.
        provider::delete_data_for_all_users_in_context($context);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(self::SECOND_SESSION_COUNT, $count);
    }

    public function test_delete_empty_data_user(): void
    {
        global $DB, $USER;

        [$context,] = $this->create_context_and_cm();

        $approved_contextlist = new approved_contextlist($USER, 'ispring', [$context->id]);
        provider::delete_data_for_user($approved_contextlist);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(0, $count);
    }

    public function test_delete_data_user(): void
    {
        global $DB, $USER;

        [$context, $cm] = $this->create_context_and_cm();
        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(self::FIRST_SESSION_COUNT, $count);

        $approved_contextlist = new approved_contextlist($USER, 'ispring', [$context->id]);
        provider::delete_data_for_user($approved_contextlist);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(0, $count);
    }

    public function test_delete_data_user_with_one_more_user(): void
    {
        global $DB, $USER;

        [$context, $cm] = $this->create_context_and_cm();
        $second_user = self::getDataGenerator()->create_user();

        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);
        $this->create_content_and_session($cm, $second_user->id, self::SECOND_SESSION_COUNT);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(self::TOTAL_SESSION_COUNT, $count);

        $approved_contextlist = new approved_contextlist($USER, 'ispring', [$context->id]);
        provider::delete_data_for_user($approved_contextlist);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(self::SECOND_SESSION_COUNT, $count);
    }

    public function test_delete_data_user_with_few_contexts(): void
    {
        global $DB, $USER;

        [$context, $cm] = $this->create_context_and_cm();
        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);

        [$context_second, $cm_second] = $this->create_context_and_cm();
        $this->create_content_and_session($cm_second, $USER->id, self::SECOND_SESSION_COUNT);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(self::TOTAL_SESSION_COUNT, $count);

        $approved_contextlist = new approved_contextlist($USER, 'ispring', [$context->id, $context_second->id]);
        provider::delete_data_for_user($approved_contextlist);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(0, $count);
    }

    public function test_delete_empty_data_users(): void
    {
        global $DB, $USER;

        [$context,] = $this->create_context_and_cm();
        $second_user = self::getDataGenerator()->create_user();

        $approved_list = new approved_userlist($context, self::COMPONENT_NAME, [$USER->id, $second_user->id]);
        provider::delete_data_for_users($approved_list);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(0, $count);
    }

    public function test_delete_data_of_one_user(): void
    {
        global $DB, $USER;

        [$context, $cm] = $this->create_context_and_cm();
        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(self::FIRST_SESSION_COUNT, $count);

        $approved_list = new approved_userlist($context, self::COMPONENT_NAME, [$USER->id]);
        provider::delete_data_for_users($approved_list);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(0, $count);
    }

    public function test_delete_data_of_few_user(): void
    {
        global $DB, $USER;

        [$context, $cm] = $this->create_context_and_cm();
        $second_user = self::getDataGenerator()->create_user();

        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);
        $this->create_content_and_session($cm, $second_user->id, self::SECOND_SESSION_COUNT);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(self::TOTAL_SESSION_COUNT, $count);

        $approved_list = new approved_userlist($context, self::COMPONENT_NAME, [$USER->id, $second_user->id]);
        provider::delete_data_for_users($approved_list);

        $count = $DB->count_records('ispring_session');
        $this->assertEquals(0, $count);
    }

    public function test_export_empty_data(): void
    {
        global $USER;
        [$context,] = $this->create_context_and_cm();

        $writer = writer::with_context($context);

        $this->export_context_data_for_user($USER->id, $context, self::COMPONENT_NAME);
        $data = $writer->get_data();

        $this->assertEmpty($data);
    }

    public function test_export_data_with_one_record_and_one_context(): void
    {
        global $USER;
        [$context, $cm] = $this->create_context_and_cm();
        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);

        $writer = writer::with_context($context);
        $this->export_context_data_for_user($USER->id, $context, self::COMPONENT_NAME);
        $data = $writer->get_data();

        $this->assertNotEmpty($data);
        $this->assertCount(self::FIRST_SESSION_COUNT, $data->sessions);
    }

    public function test_export_data_with_few_record_and_one_context(): void
    {
        global $USER;
        [$context, $cm] = $this->create_context_and_cm();
        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);
        $this->create_content_and_session($cm, $USER->id, self::SECOND_SESSION_COUNT);

        $writer = writer::with_context($context);
        $this->export_context_data_for_user($USER->id, $context, self::COMPONENT_NAME);
        $data = $writer->get_data();

        $this->assertNotEmpty($data);
        $this->assertCount(self::TOTAL_SESSION_COUNT, $data->sessions);
    }

    public function test_export_data_with_few_record_and_one_context_with_few_users(): void
    {
        global $USER;
        [$context, $cm] = $this->create_context_and_cm();
        $second_user = self::getDataGenerator()->create_user();

        $this->create_content_and_session($cm, $USER->id, self::FIRST_SESSION_COUNT);
        $this->create_content_and_session($cm, $second_user->id, self::SECOND_SESSION_COUNT);

        $writer = writer::with_context($context);
        $this->export_context_data_for_user($USER->id, $context, self::COMPONENT_NAME);
        $data = $writer->get_data();

        $this->assertNotEmpty($data);
        $this->assertCount(self::FIRST_SESSION_COUNT, $data->sessions);
    }

    public function test_export_data_with_few_record_and_few_context(): void
    {
        global $USER;
        [$context, $cm] = $this->create_context_and_cm();
        $this->create_content_and_session($cm, $USER->id, 3);

        [$context_second, $cm_second] = $this->create_context_and_cm();
        $this->create_content_and_session($cm_second, $USER->id, 3);

        $writer = writer::with_context($context);
        $writer_second = writer::with_context($context_second);
        $contextlist = new approved_contextlist(
            $USER,
            self::COMPONENT_NAME,
            [$context->id, $context_second->id]
        );

        provider::export_user_data($contextlist);

        $data = $writer->get_data();
        $this->assertNotEmpty($data);
        $this->assertCount(6, $data->sessions);

        $data_second = $writer_second->get_data();
        $this->assertNotEmpty($data_second);
        $this->assertCount(6, $data_second->sessions);
    }

    private function create_mock_content(int $ispring_module_id, int $version): \stdClass
    {
        global $DB;
        $this->resetAfterTest();

        $content = new \stdClass();
        $content->ispring_id = $ispring_module_id;
        $content->file_id = 3;
        $content->creation_time = 0;
        $content->filename = 'index.html';
        $content->filepath = '/';
        $content->version = $version;
        $content->report_path = '/report';
        $content->report_filename = 'report.html';

        $content->id = $DB->insert_record('ispring_content', $content);
        return $content;
    }

    private function create_mock_session(int $content_id, int $user_id): \stdClass
    {
        global $DB;
        $this->resetAfterTest();

        $session = new \stdClass();
        $session->user_id = $user_id;
        $session->ispring_content_id = $content_id;
        $session->status = session_state::COMPLETE;
        $session->begin_time = 5;
        $session->attempt = 0;
        $session->duration = 10;
        $session->persist_state = '_state';
        $session->persist_state_id = '_id';
        $session->max_score = 100;
        $session->min_score = 20;
        $session->passing_score = 60;
        $session->detailed_report = '_report';
        $session->score = 10;
        $session->end_time = 10;
        $session->player_id = '12313';
        $session->id = $DB->insert_record('ispring_session', $session);

        return $session;
    }

    private function create_cm(): \stdClass
    {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $form_data['course'] = $course;
        $ispring_instance = $this->getDataGenerator()->create_module('ispring', $form_data);

        return get_coursemodule_from_instance('ispring', $ispring_instance->id);
    }

    private function create_context_and_cm(): array
    {
        $cm = $this->create_cm();

        return [\context_module::instance($cm->id), $cm];
    }

    private function create_content_and_session(\stdClass $cm, int $user_id, int $session_count)
    {
        global $DB;
        $this->resetAfterTest();

        $content = $this->create_mock_content($cm->instance, 1);
        for ($index = 0; $index < $session_count; $index++)
        {
            $this->create_mock_session($content->id, $user_id);
        }
    }

}