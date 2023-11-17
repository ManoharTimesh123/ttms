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
 * User Overall ratings
 * @package    block_user_overall_ratings
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/user_overall_ratings/locallib.php');
require_once($CFG->libdir . '/enrollib.php');

function render_user_overall_ratings($courselimit) {
    $data = '';
    $usercourseinfo = get_user_course_details($courselimit);
    $averageoverallcourserating = $usercourseinfo['averageoverallcourserating'];
    $allcourserating = $usercourseinfo['allcourserating'];
    if (!empty($allcourserating)) {
        $data .= get_course_ratings_popup_info();
        $data .= get_course_ratings_data($averageoverallcourserating, $allcourserating);
    }
    return $data;
}

function get_user_course_details($courselimit = null) {
    global $USER, $COURSE, $DB;
 
    $context = context_course::instance($COURSE->id);
    $userroleid = '';
    // If user has switched role.
    if (is_role_switched($COURSE->id)) {
        $role = $DB->get_record('role', array('id' => $USER->access['rsw'][$context->path]));
        $userroleid = $role->id;
    } 

    // Find all enrolled courses.
    $userallenrolledcourses = enrol_get_all_users_courses($USER->id, $onlyactive = false, $fields = null, $sort = null);

    if ($courselimit != null) {
        $enrolledcourses = array_slice($userallenrolledcourses, 0, $courselimit);
    } else {
        $enrolledcourses = $userallenrolledcourses;
    }

    $allcourserating = array();
    $overalltotalcourserating = 0;
    $overalltotalusercount = 0;
    $minimumrating = 0;
    $block = block_instance('cocoon_course_rating');
    $totalcoursecount = 0;
    foreach ($enrolledcourses as $userenrolledsingcourse) {
        $totalcoursecount++;
        if (empty($userroleid)) {
            $coursecontext = context_course::instance($userenrolledsingcourse->id);
            $userroleid = get_user_role_id($coursecontext, $USER->id);
        }
        $coursename = $userenrolledsingcourse->fullname;
        $courserating = get_course_overall_rating($userenrolledsingcourse, $userroleid);

        if ($courserating['courserating'] == 'nan') {
            $courseratingdata = $minimumrating;
            $totalcourserounddata = 0;
        } else {
            $courseratingdata = $courserating['courserating'];
            $totalcourserounddata = round($courseratingdata * 2) / 2;
        }
        $overalltotalcourserating = $overalltotalcourserating + $courseratingdata;
        $overalltotalusercount = $overalltotalusercount + $courserating['totalusercount'];
        $courseratingdata = $block->external_star_rating(null, $totalcourserounddata, $courserating['totalusercount']);
        $allcourserating[] = ['coursename' => $coursename, 'courserating' => $courseratingdata];
    }
    $overallrating = $overalltotalcourserating / $totalcoursecount;
    $overalltotalroundcourserating = round($overallrating * 2) / 2;
    $averageoverallcourserating = $block->external_star_rating(null, $overalltotalroundcourserating, $overalltotalusercount);

    $returndata = array('averageoverallcourserating' => $averageoverallcourserating , 'allcourserating' => $allcourserating);
    return $returndata;
}

function get_course_ratings_data($averageoverallcourserating, $allcourserating) {
    $data = '';
    // Overall block headings etc.
    $data .= html_writer::start_tag('div',
    array('class' => 'courseratings profile-course-ratings bg-white shadow-sm py-3 px-4 mx-2')
    );
    $data .= html_writer::start_tag('div', array('class' => 'overall-ratings p-2 rounded text-center mb-2 mt-3'));

    // Show overall rating.
    $data .= get_string('overallcourserating', 'block_user_overall_ratings');
    $data .= $averageoverallcourserating;
    $data .= html_writer::end_tag('div');

    // Now loop through each course and set the data.
    foreach ($allcourserating as $coursedetail) {
        $data .= html_writer::start_tag('div', array('class' => 'row mb-1'));
        $data .= html_writer::start_tag('div', array('class' => 'col-md-5'));
        $data .= $coursedetail['coursename'];
        $data .= html_writer::end_tag('div');
        $data .= html_writer::start_tag('div', array('class' => 'col-md-7 text-right'));
        $data .= $coursedetail['courserating'];
        $data .= html_writer::end_tag('div');
        $data .= html_writer::end_tag('div');
    }
    $data .= html_writer::start_tag('a', array('id' => 'hidecourse', 'name' => 'coursehidedata', 'value' => 'coursehidedata', 'class' => 'w-100 text-center btn btn-secondary'));
    $data .= get_string('seeall', 'block_user_overall_ratings');
    $data .= html_writer::end_tag('a');
    $data .= html_writer::end_tag('div');

    return $data;
}

function get_course_ratings_popup_info() {
    $data = '';
    // Overall block headings etc.
    $data .= html_writer::start_tag('div',
    array('id' => 'courseratingspopup', 'class' => 'modal fade customized-modal profile-course-ratings shadow-sm py-3 px-4 mx-2')
    );
    $data .= html_writer::start_tag('div', array('class' => 'modal-dialog modal-lg', 'role' => 'document'));
    $data .= html_writer::start_tag('div', array('class' => 'modal-content'));
    $data .= html_writer::start_tag('div', array('class' => 'modal-header'));
    $data .= html_writer::start_tag('h4', array('class' => 'modal-title w-100 text-center'));
    $data .= get_string('overallcourserating', 'block_user_overall_ratings');
    $data .= html_writer::end_tag('h4');
    $data .= html_writer::end_tag('div');
    $data .= html_writer::start_tag('div', array('id' => 'courserating-details', 'class' => 'modal-body'));
    $data .= html_writer::end_tag('div');
    $data .= html_writer::start_tag('div', array('class' => 'modal-footer'));
    $data .= html_writer::start_tag('button', array('data-dismiss' => 'modal' ,'class' => 'w-100 text-center btn btn-secondary'));
    $data .= get_string('close', 'block_user_overall_ratings');
    $data .= html_writer::end_tag('button');
    $data .= html_writer::end_tag('div');
    $data .= html_writer::end_tag('div');
    $data .= html_writer::end_tag('div');
    $data .= html_writer::end_tag('div');

    return $data;
}
function get_course_ratings_popup_data($averageoverallcourserating, $allcourserating) {
   
    $data = '';
    $data .= html_writer::start_tag('div', array('class' => 'overall-ratings p-2 rounded text-center mb-2 mt-3'));
    // Show overall rating.
    $data .= get_string('overallcourserating', 'block_user_overall_ratings');
    $data .= $averageoverallcourserating;
    $data .= html_writer::end_tag('div');
    // Now loop through each course and set the data.
    foreach ($allcourserating as $coursedetail) {
        $classinfo = array('class' => 'row mb-1');
        $data .= html_writer::start_tag('div', $classinfo);
        $data .= html_writer::start_tag('div', array('class' => 'col-md-5'));
        $data .= $coursedetail['coursename'];
        $data .= html_writer::end_tag('div');
        $data .= html_writer::start_tag('div', array('class' => 'col-md-7 text-right'));
        $data .= $coursedetail['courserating'];
        $data .= html_writer::end_tag('div');
        $data .= html_writer::end_tag('div');
    }
    return $data;
}
function render_user_overall_ratings_grid(){
    $coursedetails = get_user_course_details();
    $averageoverallcourserating = $coursedetails['averageoverallcourserating'];
    $allcourserating = $coursedetails['allcourserating'];
    $data = '';
    if (!empty($coursedetails)) {
        $data .= get_course_ratings_popup_data($averageoverallcourserating, $allcourserating);
    }
    return $data;
}
