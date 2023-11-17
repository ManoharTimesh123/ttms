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
 * Reports
 * @package    local_reports renderers
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/local/reports/locallib.php');
require($CFG->dirroot . '/local/reports/filter_form.php');

function render_activity_form ($activityname, $moduleid = false) {
    global $DB;

    // Add a form tag to the certificate_form.
    $url = new moodle_url("/local/reports/$activityname/index.php");

    $courses = $DB->get_records_sql_menu('SELECT id, fullname FROM {course} WHERE visible=1');
    $selectcourse = '';
    $selectactivity = '';
    $activity = [];
    if ($moduleid) {
        if ($activityname == 'questionnaire') {
            $questionnaire = $DB->get_record_sql("SELECT q.id, q.course FROM {questionnaire} q WHERE q.id = $moduleid");
            $selectcourse = $questionnaire->course;
        } else {
            $module = $DB->get_record_sql("SELECT m.id, cm.course FROM {course_modules} cm JOIN {modules} m ON m.id = cm.module WHERE cm.id = $moduleid");
            $selectcourse = $module->course;
        }

        $activitydetails = activity_list($selectcourse, $activityname);
        $activity = $activitydetails;
        $selectactivity = $moduleid;
    }

    $data = new stdClass();
    $data->course = $selectcourse;
    $data->activity = $selectactivity;
    $data->activityname = $activityname;
    $data->activitylist = $activitydetails;
    $mform = new report_feedback_filter_form($url, array('data' => $data));

    $mform->set_data($data);
    $mform->display();

}

function render_course_form ($courserelated, $course = '') {
    global $DB;
    
    // Add a form tag to the course_activity_form.
    $url = new moodle_url("/local/reports/$courserelated/index.php");

    $data = new stdClass();
    $data->course = $course;
    $mform = new report_activity_filter_form($url, array('data' => $data));

    $mform->set_data($data);
    $mform->display();

}
