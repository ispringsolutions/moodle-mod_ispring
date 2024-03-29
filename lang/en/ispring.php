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
$string['closebeforeopen'] = 'You have specified a close date before the open date.';
$string['contentnotfound'] = 'iSpring Module content not found';
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
$string['invalidplayerid'] = 'You have already started this quiz on a different tab or device. Refresh the page to restart this quiz on this tab. Please note that previous progress will be erased and you will have to start from the beginning.';
$string['ispring:addinstance'] = 'Create iSpring modules';
$string['ispring:preview'] = 'Preview content';
$string['ispring:view'] = 'View content';
$string['ispring:viewallreports'] = 'Preview summary reports';
$string['ispring:viewmydetailedreports'] = 'View detailed reports';
$string['last'] = 'Last attempt';
$string['maxscore'] = 'Max score';
$string['missinguserfile'] = 'No file selected';
$string['moduledescription'] = 'Description';
$string['modulename'] = 'iSpring Module';
$string['modulenameplural'] = 'iSpring Modules';
$string['modulename_help'] = 'Upload quizzes created in iSpring QuizMaker and track learner progress with iSpring Module.

Keep track of students\' quiz performances through comprehensive quiz summary reports and detailed insights for each attempt. Save all data related to attempts and versions. Easily navigate through reports using version and status filters for quick access and download reports to identify learning challenges.';
$string['modulename_link'] = 'https://www.ispringsolutions.com/go/moodle-documentation';
$string['myoverviewactionlink'] = 'Attempt';
$string['openafterclose'] = 'You have specified an open date after the close date';
$string['passingrequirementshavebeenupdatedstudenttext'] = 'Passing requirements for this quiz have been updated. Your score in Gradebook has been recalculated. This doesn\'t affect your quiz results in reports.';
$string['passingrequirementshavebeenupdatedteachertext'] = 'Passing requirements for this quiz have been updated. This doesn\'t affect student scores in reports. Their results in Gradebook have been recalculated. <a href="{$a}">Learn more about score recalculation</a>';
$string['playbutton'] = 'Start quiz';
$string['pluginadministration'] = 'Manage iSpring Module';
$string['pluginname'] = 'iSpring Module';
$string['previewbutton'] = 'Preview';
$string['privacy:metadata:ispring_session'] = 'Information about the user\'s activities in education material for a given activity';
$string['privacy:metadata:ispring_session:attempt'] = 'The attempt number of education material';
$string['privacy:metadata:ispring_session:begin_time'] = 'The start time for viewing education material';
$string['privacy:metadata:ispring_session:detailed_report'] = 'The detailed report of passed education material';
$string['privacy:metadata:ispring_session:duration'] = 'The duration of viewing education material';
$string['privacy:metadata:ispring_session:end_time'] = 'The end time for viewing education material';
$string['privacy:metadata:ispring_session:id'] = 'The id user session';
$string['privacy:metadata:ispring_session:ispring_content_id'] = 'The id of education material';
$string['privacy:metadata:ispring_session:max_score'] = 'The max score for education material';
$string['privacy:metadata:ispring_session:min_score'] = 'The min score for education material';
$string['privacy:metadata:ispring_session:passing_score'] = 'The passing score for education material';
$string['privacy:metadata:ispring_session:persist_state'] = 'The persist state of education material. Needs to store progress of current attempt';
$string['privacy:metadata:ispring_session:persist_state_id'] = 'The id of persist state';
$string['privacy:metadata:ispring_session:player_id'] = 'The id of player. Needs to block two simultaneous attempt';
$string['privacy:metadata:ispring_session:score'] = 'The user score for education material';
$string['privacy:metadata:ispring_session:status'] = 'The user completion status for education material';
$string['privacy:metadata:ispring_session:user_id'] = 'The user id';
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
$string['timeclose'] = 'Closed at';
$string['timeopen'] = 'Opened at';
$string['unavailabletime'] = 'Module is not available now.';
$string['uploadcoursefile'] = 'Select a file to upload';
$string['version'] = 'Version';
$string['viewpageinfotext'] = 'Grading method: {$a->grading_method}';
