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
 * Course Management for managing course additional data
 *
 * @package    local
 * @subpackage course_management
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Function to add/update the course custom fields.
 * Being called in /course/edit.php for course creation and update.
 */
function save_course_customdetails($data, $ajaxdata = false) {
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot . '/group/lib.php');

    $coursedetail = new stdClass();
    if ($data->course > 1) {
        $coursedetail->course = $data->course;
        $coursedetail->modality = $data->modality;
        $coursedetail->coursetype = 0;
        $coursedetail->venue = $data->venue;
        $coursedetail->days = $data->days;
        $coursedetail->certificatetemplate = $data->certificatetemplate;
        $moocid = $DB->get_field('local_modality', 'id', array('shortname' => 'mooc'));
        $coursedetail->batching = $data->batching;
        $coursedetail->enablegrouping = $data->enablegrouping;
        if ($data->modality == $moocid) {
            $coursedetail->batching = 0;
            $coursedetail->enablegrouping = 0;
        }

        $coursedetailrecord = $DB->get_record('local_course_details', array('course' => $data->course), 'id');
        // Check if course record exists in local_course_details table.
        if ($coursedetailrecord) {
            $coursedetail->id = $coursedetailrecord->id;
            $coursedetail->timemodified = time();
            $coursedetail->usermodified = $USER->id;
            $id = $DB->update_record('local_course_details', $coursedetail);

            // Check for create/update group only when grouping is enabled in course form.
            // Also if there is any other change in the field other than the grouping we shall not trigger the update every time.
            if (($coursedetailrecord->enablegrouping != $coursedetail->enablegrouping) &&
                ($coursedetail->enablegrouping == 1 && $coursedetail->batching != 1)) {

                $groupname = 'INSET'.$data->courseid;
                $grouprecord = $DB->get_record('groups', array('courseid' => $data->courseid,
                                                'idnumber' => $groupname));

                $groupdata = new stdClass();
                $groupdata->name = 'INSET' . $data->course;
                $groupdata->courseid = $data->course;
                $groupdata->idnumber = 'INSET' . $data->course;

                // Group Record to be created if it does not exists.
                if (empty($grouprecord)) {
                    $groupdata->timecreated = time();
                    $groupdata->timemodified = 0;
                    $groupid = groups_create_group($groupdata);

                    if ($groupid > 0) {
                        $localgroup = new stdClass();
                        $localgroup->groupid = $groupid;
                        if ($data->enablegrouping == 1) {
                            $localgroup->venue = $data->venue;
                        }
                        $localgroup->days = $data->days;
                        $localgroup->startdate = $data->startdate;
                        $localgroup->enddate = $data->enddate;
                        save_local_group_details($localgroup);
                    }
                } else {
                    // Group Record to be updated if it does exists.
                    $groupdata->id = $grouprecord->id;
                    $groupdata->timemodified = time();
                    $groupid = groups_update_group($groupdata);

                    if ($groupid > 0) {
                        $localgroup = new stdClass();
                        $localgroup->groupid = $groupid;
                        if ($data->enablegrouping == 1) {
                            $localgroup->venue = $data->venue;
                        }
                        $localgroup->days = $data->days;
                        $localgroup->startdate = $data->startdate;
                        $localgroup->enddate = $data->enddate;
                        save_local_group_details($localgroup);
                    }
                }
            }
        } else {
            // Creation of course details table if does not exists.
            $coursedetail->timecreated = time();
            $coursedetail->usercreated = $USER->id;
            $coursedetail->timemodified = 0;
            $coursedetail->usermodified = 0;
            $id = $DB->insert_record('local_course_details', $coursedetail, true);

            // Upon course creation and grouping is enabled then the grouping concept with trigger.
            if ($coursedetail->enablegrouping = 1 && $coursedetail->batching != 1) {
                $grouprecord = $DB->get_record('groups', array('courseid' => $data->courseid,
                                                'idnumber' => 'INSET'.$data->courseid));

                $groupdata = new stdClass();
                $groupdata->name = 'INSET' . $data->course;
                $groupdata->courseid = $data->course;
                $groupdata->idnumber = 'INSET' . $data->course;
                // Upon course creation and grouping is enabled and groups are not created we need to trigger the grouping.
                if (empty($grouprecord)) {
                    $groupdata->timecreated = time();
                    $groupdata->timemodified = 0;
                    $groupid = groups_create_group($groupdata);

                    if ($groupid > 0) {
                        $localgroup = new stdClass();
                        $localgroup->groupid = $groupid;
                        if ($data->enablegrouping == 1) {
                            $localgroup->venue = $data->venue;
                        }
                        $localgroup->days = $data->days;
                        $localgroup->startdate = $data->startdate;
                        $localgroup->enddate = $data->enddate;
                        save_local_group_details($localgroup);
                    }
                } else {
                    // Upon course creation and grouping is enabled and groups are not updated we need to trigger the grouping.
                    $groupdata->id = $grouprecord->id;
                    $groupdata->timemodified = time();
                    $groupid = groups_update_group($groupdata);

                    if ($groupid > 0) {
                        $localgroup = new stdClass();
                        $localgroup->groupid = $groupid;
                        if ($data->enablegrouping == 1) {
                            $localgroup->venue = $data->venue;
                        }
                        $localgroup->days = $data->days;
                        $localgroup->startdate = $data->startdate;
                        $localgroup->enddate = $data->enddate;
                        save_local_group_details($localgroup);
                    }
                }
            }
        }
    }
    return $id;
}

/*
 * Function to add/update local_group_details fields.
 * Being called if the grouping is selected for course creation and update.
 */
function save_local_group_details($data) {
    global $DB, $USER;

    $groupdetails = new stdClass();
    if ($data->groupid > 1) {
        $groupdetails->groupid = $data->groupid;
        $groupdetails->venue = $data->venue;
        $groupdetails->days = $data->days;
        $groupdetails->startdate = $data->startdate;
        $groupdetails->enddate = $data->enddate;

        $record = $DB->get_record('local_group_details', array('groupid' => $data->groupid));

        if ($record) {
            $groupdetails->id = $record->id;
            $groupdetails->timemodified = time();
            $groupdetails->usermodified = $USER->id;
            $id = $DB->update_record('local_group_details', $groupdetails, true);
        } else {
            $groupdetails->timemodified = 0;
            $groupdetails->usermodified = 0;
            $groupdetails->timecreated = time();
            $groupdetails->usercreated = $USER->id;
            $id = $DB->insert_record('local_group_details', $groupdetails, true);
        }
        return $id;
    } else {
        return false;
    }
}

/*
 * Function to add the course custom fields to course form.
 * Being called in /course/edit_form.php for appending the fields.
 */
function course_extended_form_elements(&$mform, $course) {
    global $DB, $CFG, $PAGE;

    require_once($CFG->dirroot . '/mod/certificate/locallib.php');
    $PAGE->requires->jquery();
    $PAGE->requires->js('/local/batching/js/display_certificate.js');

    $mform->addElement('header', 'Extended Fields', get_string('courseextendedfields', 'local_course_management'), '');

    $coursedetails = new stdClass();
    $moocid = $DB->get_field('local_modality', 'id', array('shortname' => 'mooc'));
    if ($course->id > 0) {
        $coursedetails = $DB->get_record('local_course_details', array('course' => $course->id));
    }

    $sql = "SELECT id, name
            FROM {local_modality} ";
    $modalitylist = $DB->get_records_sql_menu($sql);

    $mform->addElement('select', 'modality', get_string('modality', 'local_course_management'), $modalitylist);
    $mform->addRule('modality', get_string('required'), 'required', null, 'client');
    $mform->setType('modality', PARAM_RAW);

    $enablebatching = get_string('enablebatching', 'local_course_management');
    $mform->addElement('advcheckbox', 'batching', $enablebatching, ' ', array(), array(0, 1));

    $enablegrouping = get_string('enablegrouping', 'local_course_management');
    $mform->addElement('advcheckbox', 'enablegrouping', $enablegrouping, ' ', array(), array(0, 1));
    $mform->hideIf('enablegrouping', 'modality', 'eq', $moocid);

    $sql = "SELECT id, name FROM {local_schools} ";
    $schoollist = $DB->get_records_sql_menu($sql);

    $mform->addElement('select', 'venue', get_string('venue', 'local_course_management'), $schoollist);
    $mform->setType('venue', PARAM_RAW);
    $mform->hideIf('venue', 'modality', 'eq', $moocid);

    $mform->addElement('text', 'days', get_string('courseduration', 'local_course_management'), array('size' => 20));
    $mform->setType('days', PARAM_INT);
    $mform->hideIf('days', 'modality', 'eq', $moocid);

    $mform->addElement('select', 'certificatetemplate', get_string('certificatetemplate', 'local_batching'),
                            certificate_get_images('borders'), array('onchange' => 'javascript:display_certificate(this.value);'));
    $mform->addRule('certificatetemplate', get_string('required'), 'required', null, 'client');
    $mform->setDefault('certificatetemplate', '0');

    if ($course->id > 0 && !empty($coursedetails->certificatetemplate)) {
        $html = html_writer::script("$(document).ready(function(){
                                    display_certificate('".$coursedetails->certificatetemplate."');
                                });");
        $mform->addElement('html', $html);
    }
}

/*
 * Function to set the data for course custom fields.
 * Being called in /course/edit_form.php for course custom fields setdata.
 */
function course_extended_set_data($course) {
    global $DB;

    if ($course->id > 1) {
        $courseid = $course->id;
        $formdata = $DB->get_record('local_course_details', array('course' => $courseid));
        if (!empty($formdata)) {

            unset($formdata->id);
            $course = (object) array_merge((array) $course, (array) $formdata);
        }
        return $course;
    } else {
        return $course;
    }
}

/*
 * Function to validating the data for course custom fields.
 * Being called in /course/edit_form.php for course custom fields setdata.
 */
function course_extended_validation($errors, $data, $files) {
    global $DB;
   
   $modalityid = $data['modality'];

   $modalitydetails = $DB->get_record('local_modality', ['id' => $modalityid]);
   if (!empty($modalitydetails) && $modalitydetails->shortname !== 'mooc') {
        if($data['venue'] == 0){
             $errors['venue'] = get_string('selectvanue', 'local_course_management');
        }
       
   }
    return $errors;
}

function delete_course_relation($courseid) {
    global $DB;
    $DB->delete_records('local_batching', ['course' => $courseid]);
    $DB->delete_records('local_batching_logs', ['course' => $courseid]);
    $DB->delete_records('local_course_details', ['course' => $courseid]);
    $DB->delete_records('local_nbt_training_interests', ['course' => $courseid]);
    $DB->delete_records('local_wall_posts', ['courseid' => $courseid]);
}
