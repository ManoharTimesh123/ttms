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
/**
 * The Organisation stracture.
 *
 * @package    local_user_management
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Nadia Farheen Limited
 */
 
defined('MOODLE_INTERNAL') || die();
// Add_class.

require_once($CFG->dirroot . '/enrol/cohort/locallib.php');
require_once($CFG->dirroot . '/blocks/user_achievements/externallib.php');

function add_class ($data) {
    global $DB, $USER;
    $i = count($data->classlist);
    $returnassignclass = array();
    for ($j = 1; $j <= $i; $j++) {
        $classorder = new stdClass();
        $classorder->class_content_id = $data->classlist[$j - 1];
        $classorder->company_id = $data->cId;
        $classorder->department_id = $data->departmentid;
        $classorder->enrolled = $data->enrolled;
        $classorder->deleted = 0;
        $classorder->suspended = 0;
        $classorder->usercreated = $USER->id;
        $classorder->timecreated = time();

        $courseid = $DB->get_record_sql("SELECT c.course FROM {local_classroom} c
                    INNER JOIN {local_classroom_content} cc
                    ON cc.classroomid = c.id
                    WHERE cc.id = {$data->classlist[$j - 1]}")->course;

        if ($data->departmentid == 0) {
            $departments = $DB->get_records('user_department', array('company_id' => $data->cId, 'deleted' => 0));
            foreach ($departments as $department) {
                $classorder->department_id = $department->id;
                $departmentwithenrollexists = $DB->get_record('user_assign_class', array('class_content_id' => $data->classlist[$j - 1], 'department_id' => $data->departmentid, 'enrolled' => $data->enrolled, 'deleted' => 0));
                if (!$departmentwithenrollexists) {
                    $departmentwithoutenrollexists = $DB->get_record('user_assign_class', array('class_content_id' => $data->classlist[$j - 1], 'department_id' => $department->id, 'deleted' => 0));
                    if ($departmentwithoutenrollexists) {
                        array_push($returnassignclass, $departmentwithoutenrollexists->id);
                        // Update.
                        $classorder->id = $DB->get_record('user_assign_class', array('class_content_id' => $data->classlist[$j - 1], 'department_id' => $department->id, 'deleted' => 0))->id;
                        $DB->update_record('user_assign_class', $classorder);
                        // If enroll is 1 enroll cohort else unenroll.
                        if ($data->enrolled == 1) {
                            $cohortid = $DB->get_record('user_department', array('id' => $department->id))->cohortid;
                            cohort_add_course($courseid, $cohortid);
                            cohort_add_class($courseid, $cohortid, $data->classlist[$j - 1]);
                        }
                    } else {
                        // Insert.
                        $DB->insert_record('user_assign_class', $classorder);
                        // If enroll is 1 enroll cohort.
                        if ($data->enrolled == 1) {
                            $cohortid = $DB->get_record('user_department', array('id' => $department->id))->cohortid;
                            cohort_add_course($courseid, $cohortid);
                            cohort_add_class($courseid, $cohortid, $data->classlist[$j - 1]);
                        }
                    }
                } else {
                    array_push($returnassignclass, $departmentwithenrollexists->id);
                }
            }
        } else {
            $departmentwithenrollexists = $DB->get_record('user_assign_class', array('class_content_id' => $data->classlist[$j - 1], 'department_id' => $data->departmentid, 'enrolled' => $data->enrolled, 'deleted' => 0));

            if (!$departmentwithenrollexists) {
                $departmentwithoutenrollexists = $DB->get_record('user_assign_class', array('class_content_id' => $data->classlist[$j - 1], 'department_id' => $data->departmentid, 'deleted' => 0));
                if ($departmentwithoutenrollexists) {
                    array_push($returnassignclass, $departmentwithoutenrollexists->id);
                    // Update.
                    $classorder->id = $DB->get_record('user_assign_class', array('class_content_id' => $data->classlist[$j - 1], 'department_id' => $data->departmentid, 'deleted' => 0))->id;
                    $DB->update_record('user_assign_class', $classorder);
                    // If enroll is 1 enroll cohort else unenroll.
                    if ($data->enrolled == 1) {
                        $cohortid = $DB->get_record('user_department', array('id' => $data->departmentid))->cohortid;
                        cohort_add_course($courseid, $cohortid);
                        cohort_add_class($courseid, $cohortid, $data->classlist[$j - 1]);
                    }
                } else {
                    // Insert.
                    $DB->insert_record('user_assign_class', $classorder);
                    // If enroll is yes enroll cohort.
                    if ($data->enrolled == 1) {
                        $cohortid = $DB->get_record('user_department', array('id' => $data->departmentid))->cohortid;
                        cohort_add_course($courseid, $cohortid);
                        cohort_add_class($courseid, $cohortid, $data->classlist[$j - 1]);
                    }
                }
            } else {
                array_push($returnassignclass, $departmentwithenrollexists->id);
            }
        }
    }
    return $returnassignclass;
}
function add_course($data) {
    global $DB, $USER;
    $i = count($data->courselist);

    for ($j = 1; $j <= $i; $j++) {
        $courseorder = new stdClass();
        $courseorder->course_id = $data->courselist[$j - 1];
        $courseorder->company_id = $data->cId;
        $courseorder->department_id = $data->departmentid;
        $courseorder->enrolled = $data->enrolled;
        $courseorder->deleted = 0;
        $courseorder->suspended = 0;
        $courseorder->createdby = $USER->id;
        $courseorder->timecreated = time();
        if ($data->departmentid == 0) {
            $departments = $DB->get_records('user_department', array('company_id' => $data->cId, 'deleted' => 0));
            foreach ($departments as $department) {
                $courseorder->department_id = $department->id;
                $departmentwithenrollexists = $DB->record_exists('user_course', array('course_id' => $data->courselist[$j - 1], 'department_id' => $department->id, 'enrolled' => $data->enrolled, 'deleted' => 0));
                if (!$departmentwithenrollexists) {
                    $departmentwithoutenrollexists = $DB->record_exists('user_course', array('course_id' => $data->courselist[$j - 1], 'department_id' => $department->id, 'deleted' => 0));
                    if ($departmentwithoutenrollexists) {
                        // Update.
                        $courseorder->id = $DB->get_record('user_course', array('course_id' => $data->courselist[$j - 1], 'department_id' => $department->id, 'deleted' => 0))->id;
                        $DB->update_record('user_course', $courseorder);
                        // If enroll is 1 enroll cohort else unenroll.
                        if ($data->enrolled == 1) {
                            $cohortid = $DB->get_record('user_department', array('id' => $department->id))->cohortid;
                            cohort_add_course($data->courselist[$j - 1], $cohortid);
                        }
                        // Else condition Remove cohort was not mentioned.
                    } else {
                        // Insert.
                        $DB->insert_record('user_course', $courseorder);
                        // If enroll is 1 enroll cohort.
                        if ($data->enrolled == 1) {
                            $cohortid = $DB->get_record('user_department', array('id' => $department->id))->cohortid;
                            cohort_add_course($data->courselist[$j - 1], $cohortid);
                        }
                    }
                }
            }
        } else {
            $departmentwithenrollexists = $DB->record_exists('user_course', array('course_id' => $data->courselist[$j - 1], 'department_id' => $data->departmentid, 'enrolled' => $data->enrolled, 'deleted' => 0));
            if (!$departmentwithenrollexists) {
                $departmentwithoutenrollexists = $DB->record_exists('user_course', array('course_id' => $data->courselist[$j - 1], 'department_id' => $data->departmentid, 'deleted' => 0));
                if ($departmentwithoutenrollexists) {
                    // Update.
                    $courseorder->id = $DB->get_record('user_course', array('course_id' => $data->courselist[$j - 1], 'department_id' => $data->departmentid, 'deleted' => 0))->id;
                    $DB->update_record('user_course', $courseorder);
                    // If enroll is 1 enroll cohort else unenroll.
                    if ($data->enrolled == 1) {
                        $cohortid = $DB->get_record('user_department', array('id' => $data->departmentid))->cohortid;
                        cohort_add_course($data->courselist[$j - 1], $cohortid);
                    }
                } else {
                    // Insert.
                    $DB->insert_record('user_course', $courseorder);
                    // If enroll is 1 enroll cohort.
                    if ($data->enrolled == 1) {
                        $cohortid = $DB->get_record('user_department', array('id' => $data->departmentid))->cohortid;
                        cohort_add_course($data->courselist[$j - 1], $cohortid);
                    }
                }
            }
        }
    }
    // Exit.
    return;
}

function add_package($data) {
    global $DB, $USER;
 
    $courseorder = new stdClass();
    $courseorder->companies = $data->parent;
    $courseorder->masterid = $data->masterid;
    $courseorder->childcompany = $data->childcompany;
    $courseorder->department = $data->department;
    $courseorder->assignedby = $USER->id;
    $courseorder->timecreated = time();
    $courseorder->timemodified = $courseorder->timecreated;

    $DB->insert_record('local_coursepackage_massign', $courseorder);
    return;
}

function delete_package($id) {
    global $DB;
    $DB->delete_records('local_coursepackage_massign', array('id' => $id));
}

function cohort_add_course($courseid, $cohortid) {
    global $DB;

    if (!enrol_is_enabled('cohort')) {
        // Not enabled.
        return false;
    }

    if ($DB->record_exists('enrol', array('courseid' => $courseid, 'enrol' => 'cohort'))) {
        // The course already has a cohort enrol method.
        return false;
    }
    // Get the cohort enrol plugin.
    $enrol = enrol_get_plugin('cohort');

    // Get the course record.
    $course = $DB->get_record('course', array('id' => $courseid));

    // Add a cohort instance to the course.
    $instance = array();
    $instance['name'] = '';
    $instance['status'] = ENROL_INSTANCE_ENABLED; // Enable it.
    $instance['customint1'] = $cohortid; // Used to store the cohort id.
    $instance['roleid'] = $DB->get_record('role', array('shortname' => 'learner'))->id; // Default role for cohort enrol which is usually student.
    $instance['customint2'] = 0; // Optional group id.
    $enrol->add_instance($course, $instance);

    // Sync the existing cohort members.
    $trace = new null_progress_trace();
    enrol_cohort_sync($trace, $course->id);
    $trace->finished();

    return;
}

function delete_assign_course($del, $cid) {
    global $DB, $USER;
	
    $courserecord = new stdClass();
    $courserecord->id = $del;
    $courserecord->deleted = 1;
    $courserecord->usermodified = $USER->id;
    $courserecord->timemodified = time();
    $DB->update_record('user_course', $courserecord);
    $url = new moodle_url('/local/user_management/show_user_course.php?cId='.$cid);
    redirect($url);
}
function delete_assign_class($del, $cid) {

    global $DB, $USER;

    $classrecord = new stdClass();
    $classrecord->id = $del;
    $classrecord->deleted = 1;
    $classrecord->usermodified = $USER->id;
    $classrecord->timemodified = time();
    $DB->update_record('user_assign_class', $classrecord);
    $url = new moodle_url('/local/user_management/show_user_class.php?cId='.$cid);
    redirect($url);
}

function cohort_add_class($courseid , $cohortid, $classroomcontentid) {

    global $DB, $USER;
    // Get classroom id from local_classroom_content table using $classroomcontentid.
    $classid = $DB->get_record('local_classroom_content', array('id' => $classroomcontentid))->classroomid;
    $cohortmembers = $DB->get_records('cohort_members', array('cohortid' => $cohortid));
    foreach ($cohortmembers as $cohortmember) {
        // Check whether local_classroom_users table have these record.
        $classroomuserrecord = $DB->record_exists('local_classroom_users', array('classroomid' => $classid, 'contentid' => $classroomcontentid, 'userid' => $cohortmember->userid, 'cohortid' => $cohortid, 'courseid' => $courseid));
        if (!$classroomuserrecord) {

            $classrequest = new stdClass();
            $classrequest->classid = $classid; // Find out using contentid.
            $classrequest->contentid = $classroomcontentid;
            $classrequest->userid = $cohortmember->userid; // Using cohortid find users.
            $classrequest->approvestatus = 1;
            $classrequest->timemodified = time();
            $classrequest->usermodified = $USER->id;
            $classrequest->approvedby = $USER->id;
            $classrequest->timecreated = time();
            $classrequest->cohortid = $cohortid;

            $DB->insert_record('local_classroom_request', $classrequest);

            $classusers = new stdClass();
            $classusers->classroomid = $classid;
            $classusers->contentid = $classroomcontentid;
            $classusers->courseid = $courseid;
            $classusers->userid = $cohortmember->userid;
            $classusers->supervisorid = 0;
            $classusers->hours = 0;
            $classusers->usercreated = $USER->id;
            $classusers->timecreated = time();
            $classusers->cohortid = $cohortid;

            $DB->insert_record('local_classroom_users', $classusers);
        }
    }
    return;
}

function get_user_details($userid) {

    global $DB;
    $localuserdetailssql = <<<SQL
                    SELECT * FROM {local_user_details}
                     WHERE userid = ?
                    SQL;

    return $DB->get_records_sql($localuserdetailssql, array($userid));
}

function get_user_schools($userid) {

    global $DB;
    $userschoolssql = <<<SQL
                    SELECT * FROM {local_schools}
                     WHERE hos = ?
                    SQL;

    return $DB->get_records_sql($userschoolssql, array($userid));
}


function get_positions_by_ids($positionids) {

    global $DB;

    if (!empty($positionids)) {
        $positionssql = <<<SQL
                    SELECT * FROM {local_school_positions}
                     WHERE id IN ($positionids)
                    SQL;

        return $DB->get_records_sql($positionssql);
    }
}

function get_subjects_by_ids($subjectsids) {

    global $DB;

    if (!empty($subjectsids)) {
        $subjectssql = <<<SQL
                    SELECT * FROM {local_subjects}
                    WHERE id IN ($subjectsids)
                    SQL;

        return $DB->get_records_sql($subjectssql);
    }
}

function get_schools_by_ids($schoolids) {

    global $DB;

    if (!empty($schoolids)) {
        $schoolssql = <<<SQL
                    SELECT * FROM {local_schools}
                    WHERE id IN ($schoolids)
                    SQL;

        return $DB->get_records_sql($schoolssql);
    }
}

function get_departments_by_ids($deparatmentids) {

    global $DB;

    if (!empty($deparatmentids)) {
        $departmentssql = <<<SQL
                    SELECT * FROM {local_departments}
                    WHERE id IN ($deparatmentids)
                    SQL;

        return $DB->get_records_sql($departmentssql);
    }
}

function get_grades_by_ids($gradeids) {

    global $DB;

    if (!empty($gradeids)) {
        $gradessql = <<<SQL
                    SELECT * FROM {local_grades}
                    WHERE id IN ($gradeids)
                    SQL;

        return $DB->get_records_sql($gradessql);
    }
}

function get_posts_by_ids($postids) {

    global $DB;

    if (!empty($postids)) {
        $postssql = <<<SQL
                    SELECT * FROM {local_posts}
                    WHERE id IN ($postids)
                    SQL;

        return $DB->get_records_sql($postssql);
    }
}

// FIXME: This function is already define in modality management. We need to fix this.
function get_castes_by_ids($castid) {

    global $DB;

    if (!empty($castid)) {
        $castesql = <<<SQL
                    SELECT * FROM {local_castes}
                    WHERE id IN ($castid)
                    SQL;

        return $DB->get_records_sql($castesql);
    }
}

function get_hierachy_by_school_ids($schoolids) {

    global $DB;

    if (!empty($schoolids)) {
        $hierachysql = <<<SQL
                    SELECT * FROM {local_districts} dis
                    INNER JOIN {local_diets} diet ON diet.district_id = dis.id
                    INNER JOIN {local_zones} z ON z.diet = diet.id
                    INNER JOIN {local_schools} s ON s.zone_id = z.id
                    WHERE s.id IN ($schoolids)
                    SQL;

        return $DB->get_records_sql($hierachysql);
    }
}

function get_user_profile_details($user, $include = []) {

    global $DB, $USER;

    $userdata = [];

    if ($user->id > 0 && $USER->id > 0) {

        $usersql = <<<SQL
                    SELECT u.* FROM {user} u where id = ?
                   SQL;

        $userdata = $DB->get_records_sql($usersql, [$user->id]);

        foreach ($userdata as $index => $dbuser) {
            $userid = $dbuser->id;

            // User details.
            if (in_array('details', $include) || in_array('all', $include)) {
                $userdetails = get_user_details($userid);
                $userdata[$index]->details = $userdetails;

                foreach ($userdetails as $userdetail) {
                    if (in_array('hierarchy', $include) || in_array('all', $include)) {
                        $userdata[$index]->hierarchy_details = get_hierachy_by_school_ids($userdetail->schoolid);
                    }
                    if (in_array('position', $include) || in_array('all', $include)) {
                        $userdata[$index]->position_details = get_positions_by_ids($userdetail->position);
                    }
                    if (in_array('subject', $include) || in_array('all', $include)) {
                        $userdata[$index]->subject_details = get_subjects_by_ids($userdetail->subject);
                    }
                    if (in_array('school', $include) || in_array('all', $include)) {
                        $userdata[$index]->school_details = get_schools_by_ids($userdetail->schoolid);
                    }
                    if (in_array('department', $include) || in_array('all', $include)) {
                        $userdata[$index]->department_details = get_departments_by_ids($userdetail->department);
                    }
                    if (in_array('grade', $include) || in_array('all', $include)) {
                        $userdata[$index]->grade_details = get_grades_by_ids($userdetail->grade);
                    }
                    if (in_array('post', $include) || in_array('all', $include)) {
                        $userdata[$index]->post_details = get_posts_by_ids($userdetail->post);
                    }
                    if (in_array('caste', $include) || in_array('all', $include)) {
                        $userdata[$index]->caste_details = get_castes_by_ids($userdetail->caste);
                    }
                }

                if (in_array('hos', $include) || in_array('all', $include)) {
                    $userdata[$index]->hos = get_user_schools($userid);
                }

                $userdata[$index]->achievements = \block_user_achievements_external::get_user_achievements();
            }
        }
    }

    return $userdata;

}

function custom_money_format($number) {
    $decimal = (string)($number - floor($number));
    $money = floor($number);
    $length = strlen($money);
    $delimiter = '';
    $money = strrev($money);

    for ($i = 0; $i < $length; $i++) {
        if (( $i == 3 || ($i > 3 && ($i - 1) % 2 == 0)) && $i != $length) {
            $delimiter .= ',';
        }
        $delimiter .= $money[$i];
    }

    $result = strrev($delimiter);
    $decimal = preg_replace("/0\./i", ".", $decimal);
    $decimal = substr($decimal, 0, 3);

    if ( $decimal != '0') {
        $result = $result.$decimal;
    }

    return 'Rs.' . $result;
}

function get_users_with_role($role, $include = []) {

    global $DB, $USER;

    $userdata = [];

    if ($USER->id > 0) {

        $usersql = <<<SQL
                select u.* from {user} u
                 INNER JOIN {role_assignments} ra ON u.id = ra.userid
                INNER JOIN {role} r ON r.id = ra.roleid
                WHERE r.shortname = ?
               SQL;

        $userdata = $DB->get_records_sql($usersql, [$role]);

        foreach ($userdata as $index => $dbuser) {
            $userid = $dbuser->id;

            // User details.
            if (in_array('details', $include) || in_array('all', $include)) {
                $userdetails = get_user_details($userid);
                $userdata[$index]->details = $userdetails;

                foreach ($userdetails as $userdetail) {
                    if (in_array('hierarchy', $include) || in_array('all', $include)) {
                        $userdata[$index]->hierarchy_details = get_hierachy_by_school_ids($userdetail->schoolid);
                    }
                    if (in_array('position', $include) || in_array('all', $include)) {
                        $userdata[$index]->position_details = get_positions_by_ids($userdetail->position);
                    }
                    if (in_array('subject', $include) || in_array('all', $include)) {
                        $userdata[$index]->subject_details = get_subjects_by_ids($userdetail->subject);
                    }
                    if (in_array('school', $include) || in_array('all', $include)) {
                        $userdata[$index]->school_details = get_schools_by_ids($userdetail->schoolid);
                    }
                    if (in_array('department', $include) || in_array('all', $include)) {
                        $userdata[$index]->department_details = get_departments_by_ids($userdetail->department);
                    }
                    if (in_array('grade', $include) || in_array('all', $include)) {
                        $userdata[$index]->grade_details = get_grades_by_ids($userdetail->grade);
                    }
                    if (in_array('post', $include) || in_array('all', $include)) {
                        $userdata[$index]->post_details = get_posts_by_ids($userdetail->post);
                    }
                    if (in_array('caste', $include) || in_array('all', $include)) {
                        $userdata[$index]->caste_details = get_castes_by_ids($userdetail->caste);
                    }
                }

                if (in_array('hos', $include) || in_array('all', $include)) {
                    $userdata[$index]->hos = get_user_schools($userid);
                }

                $userdata[$index]->achievements = \block_user_achievements_external::get_user_achievements();
            }
        }
    }

    return $userdata;
}

function soft_delete_user_relation($userid) {
    global $DB, $USER;
    
    $userprofilerecords = $DB->get_records('local_user_profile_details', array('userid' => $userid));
    if (!empty($userprofilerecords)) {
        foreach ($userprofilerecords as $user) {
            $userprofiledate = (object)[
                'id' => $user->id,
                'deleted' => 1,
                'userdeleted' => $USER->id,
                'timemodified' => time()
                ];
            $DB->update_record('local_user_profile_details', $userprofiledate);
        }
    }

    $userdetailecords = $DB->get_records('local_user_details', array('userid' => $userid));
    if (!empty($userdetailecords)) {
        foreach ($userdetailecords as $user) {
            $userprofiledate = (object)[
                'id' => $user->id,
                'deleted' => 1,
                'userdeleted' => $USER->id,
                'timemodified' => time()
            ];
            $DB->update_record('local_user_details', $userprofiledate);
        }
    }
}
