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
 * Plugin strings are defined here.
 *
 * @package     mod_ispring
 * @copyright   2024 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['areapackage'] = 'Package file';
$string['attempt'] = 'Attempt';
$string['average'] = 'Average grade';
$string['back'] = 'Back';
$string['begintime'] = 'Started';
$string['calendarend'] = '{$a} closes';
$string['calendarstart'] = '{$a} opens';
$string['closebeforeopen'] = 'The Due Date you\'ve chosen is earlier than the Open Date.';
$string['contentnotfound'] = 'iSpring Module content not found';
$string['contentversiontoonew'] = 'Uploaded content is not supported in the current version of iSpring plugin';
$string['coursemoduleslisttitle'] = '{$a}: iSpring Modules';
$string['detailedreport'] = 'Detailed report';
$string['duration'] = 'Time spent';
$string['endtime'] = 'Completed';
$string['entitycontent'] = 'iSpring content';
$string['entityispring'] = 'iSpring';
$string['entitysession'] = 'iSpring session';
$string['fieldsetpackage'] = 'Upload file';
$string['first'] = 'First attempt';
$string['globalreport'] = 'Summary report';
$string['grademethod'] = 'Grading method';
$string['grademethod_help'] = 'When multiple attempts are allowed, the final quiz grade can be calculated in the following ways:

* Highest grade of all attempts
* Average (mean) grade of all attempts
* First attempt (all other attempts are ignored)
* Last attempt (all other attempts are ignored)';
$string['highest'] = 'Highest grade';
$string['invaliddescriptionfile'] = 'Uploaded package has wrong format';
$string['invalidplayerid'] = 'You have already started this activity on a different tab or device. Refresh the page to restart this activity on this tab. Please note that previous progress will be erased and you will have to start from the beginning.';
$string['ispring:addinstance'] = 'Create iSpring modules';
$string['ispring:preview'] = 'Preview content';
$string['ispring:view'] = 'View content';
$string['ispring:viewallreports'] = 'View all reports';
$string['ispring:viewmydetailedreports'] = 'View detailed reports';
$string['last'] = 'Last attempt';
$string['maxscore'] = 'Max score';
$string['missinguserfile'] = 'No file selected';
$string['moduledescription'] = 'Description';
$string['modulename'] = 'iSpring Module';
$string['modulenameplural'] = 'iSpring Modules';
$string['modulename_help'] = 'Upload courses and exercises created in iSpring Suite and track learner progress with iSpring Module.

Keep track of students\' learning performances through comprehensive summary reports and detailed insights for each attempt. Save all data related to attempts and versions. Easily navigate through reports using version and status filters for quick access and download reports to identify learning challenges.';
$string['modulename_link'] = 'https://www.ispringsolutions.com/go/moodle-documentation';
$string['myoverviewactionlink'] = 'Attempt';
$string['openafterclose'] = 'You have specified an open date after the close date';
$string['passingrequirementshavebeenupdatedstudenttext'] = 'Passing requirements for this activity have been updated. Your score in Gradebook has been recalculated. This doesn\'t affect your results in reports.';
$string['passingrequirementshavebeenupdatedteachertext'] = 'Passing requirements for this activity have been updated. This doesn\'t affect student scores in reports. Their results in Gradebook have been recalculated. <a href="{$a}">Learn more about score recalculation</a>';
$string['playbutton'] = 'Start';
$string['pluginadministration'] = 'Manage iSpring Module';
$string['pluginname'] = 'iSpring Module';
$string['previewbutton'] = 'Preview';
$string['privacy:metadata:ispring_session'] = 'Information about a user\'s activity in a module during a single session';
$string['privacy:metadata:ispring_session:attempt'] = 'The number of times a user does a specific activity';
$string['privacy:metadata:ispring_session:begin_time'] = 'The time a user starts viewing an activity';
$string['privacy:metadata:ispring_session:detailed_report'] = 'Detailed data on how a user completed a specific activity';
$string['privacy:metadata:ispring_session:duration'] = 'The time a user spends viewing an activity';
$string['privacy:metadata:ispring_session:end_time'] = 'The time a user stops viewing an activity';
$string['privacy:metadata:ispring_session:id'] = 'The ID of a session';
$string['privacy:metadata:ispring_session:ispring_content_id'] = 'The ID of an activity';
$string['privacy:metadata:ispring_session:max_score'] = 'The maximum score for an activity';
$string['privacy:metadata:ispring_session:min_score'] = 'The minimum score for an activity';
$string['privacy:metadata:ispring_session:passing_score'] = 'The passing score for an activity';
$string['privacy:metadata:ispring_session:persist_state'] = 'The current state of the player. Records the user\'s progress within an activity, including where they left off, so they can resume from the same point later.';
$string['privacy:metadata:ispring_session:persist_state_id'] = 'A unique identifier that tracks the current state of the player';
$string['privacy:metadata:ispring_session:player_id'] = 'A unique ID for each attempt a user makes to do an activity. Prevents simultaneous attempts.';
$string['privacy:metadata:ispring_session:score'] = 'The score a user receives for a specific activity';
$string['privacy:metadata:ispring_session:status'] = 'The completion status of an activity';
$string['privacy:metadata:ispring_session:suspend_data'] = 'The suspend data of an activity';
$string['privacy:metadata:ispring_session:user_id'] = 'The ID of the user the session belongs to';
$string['report'] = 'Summary report';
$string['reportlink'] = 'View';
$string['reportnotfound'] = 'Report not found';
$string['reviewattempt'] = 'View';
$string['reviewresult'] = 'Detailed report';
$string['score'] = 'Awarded score';
$string['sessionnotfound'] = 'Attempt not found';
$string['status'] = 'Completion status';
$string['statuscomplete'] = 'Completed';
$string['statusfailed'] = 'Failed';
$string['statusinprogress'] = 'In progress';
$string['statuspassed'] = 'Passed';
$string['statusunknown'] = 'Unknown';
$string['studentsessionsreporttitle'] = 'Overview';
$string['timeclose'] = 'Available to';
$string['timeopen'] = 'Available from';
$string['unavailabletime'] = 'Content not available.';
$string['uploadcoursefile'] = 'Select a file to upload';
$string['version'] = 'Version';
$string['viewpageinfotext'] = 'Grading method: {$a->grading_method}';
