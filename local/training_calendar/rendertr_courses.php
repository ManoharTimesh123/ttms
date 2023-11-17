<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MOODLE VERSION INFORMATION
 *
 * This file defines the current version of the core Moodle code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/training_calendar/lib.php');
global $DB, $PAGE, $USER, $CFG, $OUTPUT;

$filterjson = optional_param('param', '',  PARAM_RAW);
$filterdata = json_decode($filterjson);
$format = $filterdata->format;

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$requestdata = $_REQUEST;
$perpage = $requestdata['iDisplayLength'];
$recordsperpage = $requestdata['iDisplayStart'];
$ssearch = $requestdata['sSearch'];
$category = $requestdata['category'];

$selectsql = "SELECT c.id as courseid,
                    c.fullname,
                    c.shortname,
                    c.summary as description,
                    c.idnumber,
                    c.format as courseformat,
                    c.startdate,
                    c.enddate,
                    c.visible as coursestatus
                    FROM {course} c";
$countsql = "SELECT count(c.id) FROM {course} c";

$formsql = " WHERE 1 = 1";

if ($requestdata['sSearch'] != "" ) {
    if (is_numeric($requestdata['sSearch'])) {
        $formsql .= " and c.id = ".$requestdata['sSearch']."";
    } else {
        $formsql .= " and (LOWER(c.fullname) LIKE '%".$requestdata['sSearch']."%') OR
                    (UPPER(c.fullname) LIKE '%".$requestdata['sSearch']."%')";
    }
}

if (!empty($filterdata->selectview)) {
    $formsql .= "";
}

if (!empty($filterdata->coursetype)) {
    $formsql .= "";
}

if (!empty($filterdata->organiser)) {
    $formsql .= "";
}

if (!empty($filterdata->trainingtimeline)) {
    $formsql .= " AND c.visible = 0";
}

if (!empty($filterdata->trainingyear)) {
    $formsql .= " AND c.visible = 0";
}

$coursescount = $DB->count_records_sql($countsql.$formsql);
$formsql .= " ORDER BY c.id desc LIMIT $perpage offset $recordsperpage";
$courseslist = $DB->get_records_sql($selectsql.$formsql);

$data = array();
foreach ($courseslist as $course) {
    $row = array();
    $row[] = $course->fullname;
    $row[] = ($filterdata->coursetype != 'mock') ? 'Training' : 'Mock';
    $row[] = 'Teacher 1';
    $row[] = 'N/A';
    $row[] = ($course->enddate > $course->startdate) ? secondstotime($course->enddate - $course->startdate) : 'N/A';
    $row[] = ($course->enddate) ? customdateformat('DATE_WITHOUT_TIME', $course->enddate) : 'N/A';
    $row[] = !is_siteadmin($USER->id) ? usercourse_completionprogress($course->id, $USER->id) : 'N/A';
    $row[] = html_writer::link(new moodle_url('/course/view.php?id='.$course->id.''), 'View', array('target' => '_blank'));
    $data[] = $row;
}

$itotal = $coursescount;
$ifilteredtotal = $itotal;
$output = array(
    "sEcho" => intval($requestdata['sEcho']),
    "iTotalRecords" => $itotal,
    "iTotalDisplayRecords" => $ifilteredtotal,
    "aaData" => $data
);

echo json_encode($output);

function secondstotime($seconds) {
    $dtf = new \DateTime('@0');
    $dtt = new \DateTime("@$seconds");
    return $dtf->diff($dtt)->format('%a days, %h hours, %i mins and %s secs');
}

function usercourse_completionprogress($courseid, $userid) {
    $completion = new \core_completion\course_completion($courseid, $userid);
    // Get the completion progress for the user and course.
    $progress = $completion->get_progress();
    return $progress;
}

function get_courseteacher($courseid) {
    $rolename = 'editingteacher'; // Replace with the shortname of the role you want to retrieve.
    // Get the enrolled users for the course and role.
    $users = get_enrolled_users(context_course::instance($courseid), $rolename);
    // Get the first user in the array.
    $user = reset($users);
    return $user;
}
