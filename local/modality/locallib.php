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
 * The modality Management
 *
 * @package local_modality
 * @author  Nadia Farheen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

function add_modality($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = strip_tags($data->name);
    $profdata->shortname = strip_tags($data->shortname);
    $profdata->description = $data->description['text'];
    $profdata->descriptionformat = $data->description['format'];

    if ($data->id > 0) {
        $modality = $DB->get_record('local_modality', array('id' => $data->id));
        $profdata->id = $modality->id;
        $profdata->timemodified = time();
        $profdata->updatedby = $USER->id;

        $dataid = $DB->update_record('local_modality', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->createdby = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;
        $dataid = $DB->insert_record('local_modality', $profdata);
    }

    return $dataid;
}

function delete_modality($id) {
    global $DB;
    return $DB->delete_records('local_modality', array('id' => $id));
}

function add_coursetype($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = strip_tags($data->name);
    $profdata->shortname = strip_tags($data->shortname);
    $profdata->description = $data->description['text'];
    $profdata->descriptionformat = $data->description['format'];

    if ($data->id > 0) {
        $modality = $DB->get_record('local_coursetype', array('id' => $data->id));
        $profdata->id = $modality->id;
        $profdata->timemodified = time();
        $profdata->usermodified = $USER->id;
        $dataid = $DB->update_record('local_coursetype', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->usercreated = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;
        $dataid = $DB->insert_record('local_coursetype', $profdata);
    }

    return $dataid;
}

function delete_coursetype($id) {
    global $DB;
    return $DB->delete_records('local_coursetype', array('id' => $id));
}

function add_department($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = strip_tags($data->name);
    $profdata->code = strip_tags($data->code);
    $profdata->description = $data->description['text'];
    $profdata->descriptionformat = $data->description['format'];

    if ($data->id > 0) {
        $modality = $DB->get_record('local_departments', array('id' => $data->id));
        $profdata->id = $modality->id;
        $profdata->timemodified = time();
        $profdata->usermodified = $USER->id;
        $dataid = $DB->update_record('local_departments', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->usercreated = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;
        $dataid = $DB->insert_record('local_departments', $profdata);
    }

    return $dataid;
}

function delete_department($id) {
    global $DB;
    return $DB->delete_records('local_departments', array('id' => $id));
}

function add_districts($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = $data->name;
    $profdata->code = $data->code;
    $profdata->departments = implode(',', $data->departments);
    $profdata->state_id = $data->state_id;
    $profdata->description = $data->description['text'];
    $profdata->descriptionformat = $data->description['format'];

    if ($data->id > 0) {
        $modality = $DB->get_record('local_districts', array('id' => $data->id));
        $profdata->id = $modality->id;
        $profdata->timemodified = time();
        $profdata->usermodified = $USER->id;

        $dataid = $DB->update_record('local_districts', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->usercreated = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;
        $dataid = $DB->insert_record('local_districts', $profdata);
    }

    return $dataid;
}

function delete_districts($id) {
    global $DB;
    return $DB->delete_records('local_districts', array('id' => $id));
}

function add_zones($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = strip_tags($data->name);
    $profdata->code = strip_tags($data->code);
    $profdata->departments = implode(',', $data->departments);
    $profdata->diet = $data->diet;
    $profdata->description = $data->description['text'];
    $profdata->descriptionformat = $data->description['format'];

    if ($data->id > 0) {
        $modality = $DB->get_record('local_zones', array('id' => $data->id));
        $profdata->id = $modality->id;
        $profdata->timemodified = time();
        $profdata->usermodified = $USER->id;
        $dataid = $DB->update_record('local_zones', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->usercreated = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;
        $dataid = $DB->insert_record('local_zones', $profdata);
    }

    return $dataid;
}

function delete_zones($id) {
    global $DB;
    return $DB->delete_records('local_zones', array('id' => $id));
}

function add_diets($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = strip_tags($data->name);
    $profdata->code = strip_tags($data->code);
    $profdata->departments = implode(',', $data->departments);
    $profdata->head = $data->head;
    $profdata->district_id = $data->district_id;
    $profdata->description = $data->description['text'];
    $profdata->descriptionformat = $data->description['format'];

    if ($data->id > 0) {
        $modality = $DB->get_record('local_diets', array('id' => $data->id));
        $profdata->id = $modality->id;
        $profdata->timemodified = time();
        // Update diethead role to the user.
        assign_or_unassign_roles($data->head, 'diethead', $modality->head);

        $dataid = $DB->update_record('local_diets', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->usercreated = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;

        // Assign diethead role to the user.
        assign_or_unassign_roles($data->head, 'diethead');

        $dataid = $DB->insert_record('local_diets', $profdata);
    }

    return $dataid;
}

function delete_diets($id) {
    global $DB;
    return $DB->delete_records('local_diets', array('id' => $id));
}

function add_subjects($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = strip_tags($data->name);
    $profdata->code = strip_tags($data->code);
    $profdata->description = $data->description['text'];
    $profdata->descriptionformat = $data->description['format'];

    if ($data->id > 0) {
        $subject = $DB->get_record('local_subjects', array('id' => $data->id));
        $profdata->id = $subject->id;
        $profdata->timemodified = time();
        $profdata->usermodified = $USER->id;

        $dataid = $DB->update_record('local_subjects', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->usercreated = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;
        $dataid = $DB->insert_record('local_subjects', $profdata);
    }

    return $dataid;
}

function delete_subjects($id) {
    global $DB;
    return $DB->delete_records('local_subjects', array('id' => $id));
}

function add_schools($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = strip_tags($data->name);
    $profdata->code = strip_tags($data->code);
    $profdata->departments = implode(',', $data->departments);
    $profdata->zone_id = $data->zone_id;
    $profdata->hos = $data->hos;
    $profdata->isvenue = $data->isvenue;
    $profdata->venue_capacity = $data->venuecapacity;
    $profdata->description = $data->description['text'];
    $profdata->descriptionformat = $data->description['format'];


    if ($data->id > 0) {
        $modality = $DB->get_record('local_schools', array('id' => $data->id));
        $profdata->id = $modality->id;
        $profdata->timemodified = time();
        $profdata->usermodified = $USER->id;

        // Update hos role to the user.
        assign_or_unassign_roles($data->hos, 'hos', $modality->hos);

        $dataid = $DB->update_record('local_schools', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->usercreated = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;

        // Assign hos role to the user.
        assign_or_unassign_roles($data->hos, 'hos');

        $dataid = $DB->insert_record('local_schools', $profdata);
    }

    return $dataid;
}

function delete_schools($id) {
    global $DB;
    return $DB->delete_records('local_schools', array('id' => $id));
}

function add_school_positions($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = strip_tags($data->name);
    $profdata->shortname = strip_tags($data->shortname);
    $profdata->description = $data->description['text'];
    $profdata->descriptionformat = $data->description['format'];

    if ($data->id > 0) {
        $modality = $DB->get_record('local_school_positions', array('id' => $data->id));
        $profdata->id = $modality->id;
        $profdata->timemodified = time();
        $profdata->usermodified = $USER->id;

        $dataid = $DB->update_record('local_school_positions', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->usercreated = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;
        $dataid = $DB->insert_record('local_school_positions', $profdata);
    }

    return $dataid;
}

function delete_school_positions($id) {
    global $DB;
    return $DB->delete_records('local_school_positions', array('id' => $id));
}

function add_or_update_caste($caste) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $currenttimestamp = time();

    $castedata = new stdClass();
    $castedata->name = strtoupper($caste->name);

    if ($caste->id > 0) {
        $caste = $DB->get_record('local_castes', array('id' => $caste->id));
        $castedata->id = $caste->id;
        $castedata->timemodified = $currenttimestamp;
        $castedata->usermodified = $loggedinuserid;

        $casetid = $DB->update_record('local_castes', $castedata);
    } else {
        $castedata->timecreated = $currenttimestamp;
        $castedata->usercreated = $loggedinuserid;
        $castedata->timemodified = $castedata->timecreated;
        $castedata->usermodified = $loggedinuserid;
        $casetid = $DB->insert_record('local_castes', $castedata);
    }

    return $casetid;
}

function get_caste_by_id($castid) {
    global $DB;

    return $DB->get_record('local_castes', array('id' => $castid));
}

function get_castes() {
    global $DB;

    $castessql = <<<SQL
            SELECT c.*, concat(u.firstname,' ',u.lastname) createdby
             FROM {local_castes} c
            JOIN {user} u ON u.id = c.usercreated
            ORDER BY id DESC
            SQL;

    return $DB->get_records_sql($castessql);
}

function delete_caste($casteid) {
    global $DB;

    return $DB->delete_records('local_castes', array('id' => $casteid));
}

function add_or_update_grade($grade) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $currenttimestamp = time();

    $gradedata = new stdClass();
    $gradedata->name = strtoupper($grade->name);

    if ($grade->id > 0) {
        $caste = $DB->get_record('local_grades', array('id' => $grade->id));
        $gradedata->id = $caste->id;
        $gradedata->timemodified = $currenttimestamp;
        $gradedata->usermodified = $loggedinuserid;
        $gradeid = $DB->update_record('local_grades', $gradedata);
    } else {
        $gradedata->timecreated = $currenttimestamp;
        $gradedata->usercreated = $loggedinuserid;
        $gradedata->timemodified = $gradedata->timecreated;
        $gradedata->usermodified = $loggedinuserid;
        $gradeid = $DB->insert_record('local_grades', $gradedata);
    }

    return $gradeid;
}

function get_grade_by_id($castid) {
    global $DB;

    return $DB->get_record('local_grades', array('id' => $castid));
}

function get_grades() {
    global $DB;

    $gradessql = <<<SQL
            SELECT g.*, concat(u.firstname,' ',u.lastname) createdby
             FROM {local_grades} g
             JOIN {user} u ON u.id = g.usercreated
             ORDER BY id DESC
            SQL;

    return $DB->get_records_sql($gradessql);
}

function delete_grade($gradeid) {
    global $DB;

    return $DB->delete_records('local_grade', array('id' => $gradeid));
}

function add_or_update_post($post) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $currenttimestamp = time();

    $postdata = new stdClass();
    $postdata->name = strtoupper($post->name);

    if ($post->id > 0) {
        $caste = $DB->get_record('local_posts', array('id' => $post->id));
        $postdata->id = $caste->id;
        $postdata->timemodified = $currenttimestamp;
        $postdata->usermodified = $loggedinuserid;
        $postid = $DB->update_record('local_posts', $postdata);
    } else {
        $postdata->timecreated = $currenttimestamp;
        $postdata->usercreated = $loggedinuserid;
        $postdata->timemodified = $postdata->timecreated;
        $postdata->usermodified = $loggedinuserid;
        $postid = $DB->insert_record('local_posts', $postdata);
    }

    return $postid;
}

function get_post_by_id($postid) {
    global $DB;

    return $DB->get_record('local_posts', array('id' => $postid));
}

function get_posts() {
    global $DB;

    $postssql = <<<SQL
            SELECT p.*, concat(u.firstname,' ',u.lastname) createdby
             FROM {local_posts} p
            JOIN {user} u ON u.id = p.usercreated
            ORDER BY id DESC
            SQL;

    return $DB->get_records_sql($postssql);
}

function delete_post($postid) {
    global $DB;

    return $DB->delete_records('local_posts', array('id' => $postid));
}
/*
 * This function is used to find all users role from a course.
 */
function get_all_user_course_role() {
    $courseroles = get_roles_for_contextlevels(CONTEXT_COURSE);
    $allroles = get_all_roles();
    $allowedroles = ['student', 'facilitator', 'coordinator', 'observer'];
    $userroleoptions = array();
    foreach ($courseroles as $courserole) {
        $usercourserole = $allroles[$courserole];
        if (in_array($usercourserole->shortname, $allowedroles)) {
            $userroleoptions[$courserole] = role_get_name($usercourserole);
        }
    }
    return $userroleoptions;
}

function get_users_role($roleid) {
    global $DB;
    $roledetails = $DB->get_record('role' , array('id' => $roleid));
    $rolename = $roledetails->name;
    return $rolename;
}
/* INTG Customization End */

function add_or_update_financial_category($financialcategory) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $currenttimestamp = time();

    $financialcategorydata = new stdClass();
    $financialcategorydata->name = strip_tags($financialcategory->name);
    $financialcategorydata->code = strip_tags($financialcategory->code);

    if ($financialcategory->id > 0) {
        $financialcategory = $DB->get_record('local_financial_categories', array('id' => $financialcategory->id));
        $financialcategorydata->id = $financialcategory->id;
        $financialcategorydata->timemodified = $currenttimestamp;
        $financialcategorydata->usermodified = $loggedinuserid;

        $financialcategoryid = $DB->update_record('local_financial_categories', $financialcategorydata);
    } else {
        $financialcategorydata->timecreated = $currenttimestamp;
        $financialcategorydata->usercreated = $loggedinuserid;
        $financialcategorydata->timemodified = $financialcategorydata->timecreated;
        $financialcategorydata->usermodified = $loggedinuserid;
        $financialcategoryid = $DB->insert_record('local_financial_categories', $financialcategorydata);
    }

    return $financialcategoryid;
}

function add_or_update_financial_details($financialdetails) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $currenttimestamp = time();

    $financialdetailsdata = new stdClass();
    $financialdetailsdata->category = $financialdetails->category;
    $financialdetailsdata->grade = $financialdetails->grade;
    $financialdetailsdata->lunchtype = $financialdetails->lunchtype;
    $financialdetailsdata->dependenton = $financialdetails->dependenton;
    $financialdetailsdata->fromvalue = strip_tags($financialdetails->fromvalue);
    $financialdetailsdata->tovalue = strip_tags($financialdetails->tovalue);
    $financialdetailsdata->value = strip_tags($financialdetails->value);

    if ($financialdetails->id > 0) {
        $financialdetails = $DB->get_record('local_financial_details', array('id' => $financialdetails->id));
        $financialdetailsdata->id = $financialdetails->id;
        $financialdetailsdata->timemodified = $currenttimestamp;
        $financialdetailsdata->usermodified = $loggedinuserid;

        $financialdetailsid = $DB->update_record('local_financial_details', $financialdetailsdata);
    } else {
        $financialdetailsdata->timecreated = $currenttimestamp;
        $financialdetailsdata->usercreated = $loggedinuserid;
        $financialdetailsdata->timemodified = $financialdetailsdata->timecreated;
        $financialdetailsdata->usermodified = $loggedinuserid;
        $financialdetailsid = $DB->insert_record('local_financial_details', $financialdetailsdata);
    }

    return $financialdetailsid;
}

function get_financial_category_by_id($financialcategoryid) {
    global $DB;

    return $DB->get_record('local_financial_categories', array('id' => $financialcategoryid));
}

function get_financial_categories() {
    global $DB;

    $financialcategoriessql = <<<SQL
            SELECT lfc.*, concat(u.firstname,' ',u.lastname) createdby
             FROM {local_financial_categories} lfc
            JOIN {user} u ON u.id = lfc.usercreated
            ORDER BY id DESC
            SQL;

    return $DB->get_records_sql($financialcategoriessql);
}

function get_financial_details_by_id($id) {
    global $DB;

    return $DB->get_record('local_financial_details', array('id' => $id));
}

function get_financial_details() {
    global $DB;

    $financialdetailssql = <<<SQL
            SELECT lfcd.*, fg.name as group_name, lt.name as lunch_type, c.name, concat(u.firstname,' ',u.lastname) reatedby
             FROM {local_financial_details} lfcd
            JOIN {local_financial_categories} c ON c.id = lfcd.category
            LEFT JOIN {local_financial_grades} fg ON lfcd.grade = fg.id
            LEFT JOIN {local_financial_lunch_types} lt ON lfcd.lunchtype = lt.id
            JOIN {user} u ON u.id = lfcd.usercreated
            ORDER BY id DESC
            SQL;

    $data = $DB->get_records_sql($financialdetailssql);

    return $data;

}

function delete_financial_category($financialcategoryid) {
    global $DB;

    return $DB->delete_records('local_financial_categories', array('id' => $financialcategoryid));
}

// Create check_modality function for checking modality is exists in courses or not.
function check_modality($id) {
    global $DB, $CFG;
    if ($DB->record_exists('local_course_details', array('modality' => $id))) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/index.php');
        redirect($url, get_string('preventmodalitydelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_districts function for checking district is exists in courses or not.
function check_districts($id) {
    global $DB, $CFG;
    if ($DB->record_exists('local_diets', array('district_id' => $id))) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_districts.php');
        redirect($url, get_string('preventdistrictsdelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_zones function for checking zone is exists in courses or not.
function check_zones($id) {
    global $DB, $CFG;
    if ($DB->record_exists('local_schools', array('zone_id' => $id))) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_zones.php');
        redirect($url, get_string('preventzonedelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_diets function for checking diet is exists in courses or not.
function check_diets($id) {
    global $DB, $CFG;
    if ($DB->record_exists('local_zones', array('diet' => $id))) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_diets.php');
        redirect($url, get_string('preventdietdelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_department function for checking department is exists in courses or not.
function check_department($id) {
    global $DB, $CFG;
    $departments = $DB->get_records_sql_menu("SELECT id, department FROM {local_user_details} GROUP BY department");
    $checkdepartment = in_array($id, explode(',', implode(',', array_filter(array_values($departments)))));
    if ($checkdepartment) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_departments.php');
        redirect($url, get_string('preventdepartmentdelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_school function for checking modality is exists in courses or not.
function check_school($id) {
    global $DB, $CFG;
    $schools = $DB->get_records_sql_menu("SELECT id, schoolid FROM {local_user_details} GROUP BY schoolid");
    $checkschool = in_array($id, explode(',', implode(',', array_filter(array_values($schools)))));
    if ($checkschool) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_schools.php');
        redirect($url, get_string('preventschooldelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_subject function for checking subject is exists in courses or not.
function check_subject($id) {
    global $DB, $CFG;
    $subject = $DB->get_records_sql_menu("SELECT id, subject FROM {local_user_details} GROUP BY subject");
    $checksubject = in_array($id, explode(',', implode(',', array_filter(array_values($subject)))));
    if ($checksubject) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_subjects.php');
        redirect($url, get_string('preventsubjectdelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_position function for checking position is exists for user or not.
function check_position($id) {
    global $DB, $CFG;
    $position = $DB->get_records_sql_menu("SELECT id, position FROM {local_user_details} GROUP BY position");
    $checkposition = in_array($id, explode(',', implode(',', array_filter(array_values($position)))));
    if ($checkposition) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_school_positions.php');
        redirect($url, get_string('preventpositiondelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_castes function for checking cast is exists in users or not.
function check_castes($id) {
    global $DB, $CFG;
    if ($DB->record_exists('local_user_details', array('caste' => $id))) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_castes.php');
        redirect($url, get_string('preventcastedelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_grades function for checking grade is exists in users or not.
function check_grades($id) {
    global $DB, $CFG;
    if ($DB->record_exists('local_user_details', array('grade' => $id))) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_grades.php');
        redirect($url, get_string('preventgradesdelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_post function for checking post is exists in users or not.
function check_post($id) {
    global $DB, $CFG;
    if ($DB->record_exists('local_user_details', array('post' => $id))) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_posts.php');
        redirect($url, get_string('preventpostsdelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create check_financial_categories function for checking financial categories is exists in users or not.
function check_financial_categories($id) {
    global $DB, $CFG;
    if ($DB->record_exists('local_batching_financials', array('category' => $id))) {
        $url = new moodle_url($CFG->wwwroot . '/local/modality/show_financial_categories.php');
        redirect($url, get_string('preventshowfinancialcategoriesdelete', 'local_modality'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create assign_or_unassign_roles function for assign or update the hos and diethead role.
function assign_or_unassign_roles($userid, $rolename, $edituserid = null) {
    global $DB;

    $systemcontext = context_system::instance();
    $roleid = $DB->get_field('role', 'id', array('shortname' => $rolename));
    if ($edituserid && $roleid && $DB->record_exists('user', ['id' => $userid, 'deleted' => 0])) {
        role_unassign($roleid, $edituserid, $systemcontext->id);
    }

    if ($userid && $roleid && $DB->record_exists('user', ['id' => $userid, 'deleted' => 0])) {
        role_assign($roleid, $userid, $systemcontext->id);
    }
}
