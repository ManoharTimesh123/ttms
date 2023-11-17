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
 * Bulk user registration script from a comma separated file
 *
 * @package    local
 * @subpackage user_management
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function save_user_customdetails($data, $ajaxdata = false) {
    global $DB, $USER;
    $id = '';

    $userdetail = new stdClass();
    if ($data->id > 2) {
        $userdetail->userid = $data->userid;
        $userdetail->salutation = $data->salutation;
        $userdetail->type = $data->type;
        $userdetail->dob = $data->dob;
        $userdetail->gender = $data->custom_gender;
        $userdetail->department = implode(',', $data->custom_department);
        $userdetail->schoolid = $data->schoolid;
        $userdetail->subject = implode(',', $data->subject);
        $userdetail->position = implode(',', $data->position);
        $userdetail->doj = $data->doj;
        $userdetail->jobtype = $data->jobtype;
        $userdetail->caste = $data->caste;
        $userdetail->grade = $data->grade;
        $userdetail->post = $data->post;
        $userdetail->address = $data->address;
        $userdetail->uid = trim($data->uid);
        $userdetail->timemodified = time();
        $userdetail->usermodified = $USER->id;

        $userdetailrecord = $DB->get_record('local_user_details', array('userid' => $userdetail->userid), 'id');
        if ($userdetailrecord) {
            $userdetail->id = $userdetailrecord->id;
            $id = $DB->update_record('local_user_details', $userdetail);
        } else {
			
            $userdetail->timecreated = time();
            $userdetail->usercreated = $USER->id;
            $id = $DB->insert_record('local_user_details', $userdetail);
        }
    } else {
        // No record entry for admin.
    }
    return $id;
}

function user_extended_form_elements (&$mform, $userid) {
    global $DB, $USER, $CFG;

    $mform->addElement('header', 'Extended Fields', get_string('orgextended', 'local_user_management'), '');

    $id = 0;
    if ($userid > 0) {
        $id = $userid;
    }
    $mform->addElement('hidden', 'userid', $id);
    $mform->setType('userid', PARAM_INT);

    $salutationarray = array(null => 'Select', "Dr." => 'Dr.', "Mr." => 'Mr.', "Mrs." => 'Mrs.', "Ms." => 'Ms.');
    $mform->addElement('select', 'salutation', get_string('salutation', 'local_user_management'), $salutationarray);
    $mform->addRule('salutation', get_string('required'), 'required', null, 'client');
    $mform->setType('salutation', PARAM_RAW);

    $typearray = array(0 => 'Internal', 1 => 'External');
    $mform->addElement('select', 'type', get_string('type', 'local_user_management'), $typearray);
    $mform->addRule('type', get_string('required'), 'required', null, 'client');

    $genderarray = array(null => 'Select', "male" => 'Male', "female" => 'Female', "others" => 'Others');
    $mform->addElement('select', 'custom_gender', get_string('gender', 'local_user_management'), $genderarray);
    $mform->setType('custom_gender', PARAM_RAW);

    $mform->addElement('text', 'uid', get_string('uid', 'local_user_management'), array('size' => 20));
    $mform->setType('uid', PARAM_RAW);

    $options = array(
       'startyear' => 1950,
       'stopyear' => customdateformat('YEAR', time()),
       'timezone' => 99,
       'optional' => false
    );
    $mform->addElement('date_selector', 'dob', get_string('dob', 'local_user_management'), $options);

    $sql = "SELECT id, name
			 FROM {local_departments} ";
    $deptslist = $DB->get_records_sql_menu($sql);

    $options = array('multiple' => true, 'noselectionstring' => get_string('selectdepartments', 'local_user_management'));
    $mform->addElement('autocomplete', 'custom_department', get_string('department', 'local_user_management'), $deptslist, $options);

    $sql = "SELECT id, name
             FROM {local_schools} ";
    $schoolslist = $DB->get_records_sql_menu($sql);

    $mform->addElement('select', 'schoolid', get_string('school', 'local_user_management'), $schoolslist);

    $sql = "SELECT id, name
             FROM {local_subjects} ";
    $subjectslist = $DB->get_records_sql_menu($sql);

    $options = array('multiple' => true, 'noselectionstring' => get_string('selectsubjects', 'local_user_management'));
    $mform->addElement('autocomplete', 'subject', get_string('subjects', 'local_user_management'), $subjectslist, $options);

    $sql = "SELECT id, name
             FROM {local_school_positions} ";
    $positionslist = $DB->get_records_sql_menu($sql);

    $options = array('multiple' => true, 'noselectionstring' => get_string('selectpositions', 'local_user_management'));
    $mform->addElement('autocomplete', 'position', get_string('position', 'local_user_management'), $positionslist, $options);

    $jobtypearray = array(null => 'Select', "regular" => 'Regular', "contract" => 'Contract');
    $mform->addElement('select', 'jobtype', get_string('jobtype', 'local_user_management'), $jobtypearray);

    $mform->addElement('date_selector', 'doj', get_string('doj', 'local_user_management'), $options);

    // Get caste.
    $castesql = "SELECT id, name FROM {local_castes} ORDER BY name";

    $castes = $DB->get_records_sql($castesql);
    $castearray = array();
    $castearray[''] = '--Select--';
    foreach ($castes as $key => $caste) {
        $castearray[$key] = $caste->name;
    }
    $mform->addElement('select', 'caste', get_string('caste', 'local_user_management'), $castearray);

    // Get Grades.
    $gradesql = "SELECT id, name FROM {local_grades} ORDER BY name ";

    $grades = $DB->get_records_sql($gradesql);
    $gradearray = array();
    $gradearray[''] = '--Select--';
    foreach ($grades as $key => $grade) {
        $gradearray[$key] = $grade->name;
    }
    $mform->addElement('select', 'grade', get_string('grade', 'local_user_management'), $gradearray);

    // Get posts.
    $postsql = "SELECT id, name FROM {local_posts} ORDER BY name ";

    $posts = $DB->get_records_sql($postsql);
    $postarray = array();
    $postarray[''] = '--Select--';
    foreach ($posts as $key => $post) {
        $postarray[$key] = $post->name;
    }
    $mform->addElement('select', 'post', get_string('post', 'local_user_management'), $postarray);

}

function user_extended_set_data ($user) {
    global $DB;
    $userid = $user->id;
    if ($user->id > 2) {
        $formdata = $DB->get_record('local_user_details', array('userid' => $userid));
        if (!empty($formdata)) {
            $formdata->userid = $formdata->userid;
            $formdata->custom_gender = $formdata->gender;
            $formdata->schoolid = $formdata->schoolid;
            $formdata->custom_department = explode(',', $formdata->department);
            $formdata->subject = explode(',', $formdata->subject);
            $formdata->position = explode(',', $formdata->position);
            unset($formdata->userid);
            unset($formdata->id);
            $user = (object) array_merge((array) $user, (array) $formdata);
        }
        return $user;
    } else {
        return $user;
    }
}
function user_extended_validation($data, $files) {
    global $DB;

    $errors = array();
    if (strlen($data->uid) > 15) {
        $errors['uid'] = get_string('invaliduid', 'local_user_management');
    }
    return $errors;
}
