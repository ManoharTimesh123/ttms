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
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/renderer.php');
require_once($CFG->dirroot . '/theme/edumy/ccn/block_handler/ccn_block_handler.php');
require_once($CFG->dirroot . '/theme/edumy/ccn/course_handler/ccn_course_handler.php');

class block_courses_popular_slider extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_courses_popular_slider');
    }
    public function specialization() {
        global $CFG, $DB;
        include_once($CFG->dirroot . '/theme/edumy/ccn/block_handler/specialization.php');
        $ccncoursehandler = new ccnCourseHandler();
        $ccncourses = $ccncoursehandler->ccnGetExampleCoursesIds(8);
        if (empty($this->config)) {
            $this->config = new \stdClass();
            $this->config->title = 'Browse Our Top Courses';
            $this->config->subtitle = 'Cum doctus civibus efficiantur in imperdiet deterruisCum doctus civibus
             efficiantur in imperdiet deterruisset.';
            $this->config->button_text = 'View all courses';
            $this->config->button_link = $CFG->wwwroot . '/course';
            $this->config->course_image = '1';
            $this->config->description = '0';
            $this->config->enrol_btn = '0';
            $this->config->enrol_btn_text = 'Join Course';
            $this->config->courses = $ccncourses;
        }
    }
    public function get_content() {
        global $CFG, $DB, $COURSE, $USER;
        if ($this->content !== null) {
            return $this->content;
        }
        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }
        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        if (!empty($this->config->title)) {
            $this->content->title = $this->config->title;
        } else {
            $this->content->title = '';
        }
        if (!empty($this->config->subtitle)) {
            $this->content->subtitle = $this->config->subtitle;
        } else {
            $this->content->subtitle = '';
        }

        /* INTG Customization Start : course limit view */
        $courselimit = $this->config->course_view_limit ?? 12;
        /* INTG Customization End */

        if (!empty($this->config->description)) {
            $this->content->description = $this->config->description;
        } else {
            $this->content->description = '0';
        }
        if (!empty($this->config->course_image)) {
            $this->content->course_image = $this->config->course_image;
        } else {
            $this->content->course_image = '';
        }
        if (!empty($this->config->enrol_btn)) {
            $this->content->enrol_btn = $this->config->enrol_btn;
        } else {
            $this->content->enrol_btn = '0';
        }
        if (!empty($this->config->enrol_btn_text)) {
            $this->content->enrol_btn_text = $this->config->enrol_btn_text;
        } else {
            $this->content->enrol_btn_text = '';
        }
        if (isset($this->content->description) && $this->content->description != '0') {
            $ccnblockshowdesc = 1;
        } else {
            $ccnblockshowdesc = 0;
        }
        if (isset($this->content->course_image) && $this->content->course_image == '1') {
            $ccnblockshowimg = 1;
        } else {
            $ccnblockshowimg = 0;
        }
        if (isset($this->content->enrol_btn) && isset($this->content->enrol_btn_text) && $this->content->enrol_btn == '1') {
            $ccnblockshowenrolbtn = 1;
        } else {
            $ccnblockshowenrolbtn = 0;
        }
        if ($this->page->theme->settings->coursecat_enrolments != 1 ||
        $this->page->theme->settings->coursecat_announcements != 1 ||
        isset($this->content->enrol_btn_text) && ($this->content->enrol_btn == '1')) {
            $ccnblockshowbottombar = 1;
            $topcoursesclass = 'ccnWithFoot';
        } else {
            $ccnblockshowbottombar = 0;
            $topcoursesclass = '';
        }
        if (!empty($this->content->description) && $this->content->description == '7') {
            $maxlength = 500;
        } else if (!empty($this->content->description) && $this->content->description == '6') {
            $maxlength = 350;
        } else if (!empty($this->content->description) && $this->content->description == '5') {
            $maxlength = 200;
        } else if (!empty($this->content->description) && $this->content->description == '4') {
            $maxlength = 150;
        } else if (!empty($this->content->description) && $this->content->description == '3') {
            $maxlength = 100;
        } else if (!empty($this->content->description) && $this->content->description == '2') {
            $maxlength = 50;
        } else {
            $maxlength = null;
        }
        /* INTG Customization Start : Get courses by rating */
        $allratedcourses = $this->get_courses_based_on_ratings($courselimit);

        $displaycontent = '';
        $displaycontent .= '
			<section class="features-course pb20 Popular-Online-Course">
		  	<div class="container">
          <div class="bg-white box-shadow px-3 pt-5 pb-3">
            <div class="row">
              <div class="col-lg-2">
                <img src="' . $CFG->wwwroot . '/theme/edumy/pix/arrow-img.png" width="100%">';
        if (!empty($this->content->title)) {
            $displaycontent .= '<h2 class="" data-ccn="title">
            ' . format_text($this->content->title, FORMAT_HTML, array('filter' => true)) . '
            </h2>';
        }
        $displaycontent .= '
              </div>
              <div class="col-lg-10">
                <div class="shop_product_slider">';
        if (!empty($allratedcourses)) {
            $chelper = new coursecat_helper();
            foreach ($allratedcourses as $course) {
                /* Category Name.*/
                $cname = explode(" ", $course->catname);
                $categoryname = $cname[0] . '...';
                /* Subcategory Name.*/
                $subcatid = $course->parent;
                $subcatdetails = $DB->get_record('course_categories', array('id' => $subcatid), 'name');
                $sname = get_string('nosubcategory', 'block_courses_popular_slider');
                if (!empty($subcatdetails)) {
                    $sname = $subcatdetails->name;
                }
                $subcategoryname = substr($sname, 0, 4) . '...';
                /* Enrolled user count.*/
                $allenroluserscount = $this->all_user_enroll_into_course($course->cid);
                /* Total Course duaration.*/
                if ($course->courseenddate != 0) {
                    $coursedifferencetime = $course->courseenddate - $course->coursestartdate;
                    $coursetotalhourse = floor($coursedifferencetime / 3600);
                    $coursetotalminuts = intval(($coursedifferencetime / 60) % 60);
                    if ($coursetotalminuts != 0) {
                        $coursetotalduaration = $coursetotalhourse . ' h ' . $coursetotalminuts . ' m ';
                    } else {
                        $coursetotalduaration = $coursetotalhourse . ' h ';
                    }
                } else {
                    $coursetotalduaration = get_string('alwaysavailable', 'block_courses_popular_slider');
                }
                $ccncoursehandler = new ccnCourseHandler();
                $ccncourse = $ccncoursehandler->ccnGetCourseDetails($course->cid);
                $ccncoursedescription = $ccncoursehandler->ccnGetCourseDescription($course->cid, $maxlength);
                $displaycontent .= '
                      <div class="item flip-card pt-4 mt-3 position-relative">
                        <div class="flip-card-front position-absolute bg-white box-shadow rounded-lg p-2">';
                if ($ccnblockshowimg) {
                    $displaycontent .= $ccncourse->ccnRender->coverImage;
                }
                $displaycontent .= '
                          <span class="position-absolute px-2 py-1 rounded-lg">' . $categoryname . '</span>
                          <div class="d-flex justify-content-between pt-2r">
                              <h4 class="m-0">';
                $displaycontent .= $subcategoryname;
                $displaycontent .= '</h4>';
                $displaycontent .= $ccncourse->ccnRender->starRating;
                $displaycontent .= '</div><div>';
                $displaycontent .= $ccncourse->ccnRender->title;
                $displaycontent .= '
                          </div>
                          <div class="d-flex justify-content-between pt-2r">
                          <h4 class="m-0"><i class="fa fa-clock-o" aria-hidden="true"></i> '. $coursetotalduaration.'</h4>
                          <h4 class="m-0"><i class="fa fa-user-o" aria-hidden="true"></i>'. $allenroluserscount.'</h4>
                          </div>';
                if ($ccnblockshowbottombar == 1) {
                    if ($ccnblockshowenrolbtn) {
                        $displaycontent .= '<a href="'. new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $course->cid)
                        .'" class="join-btn d-block h5
                        join-btn text-center text-white py-2 px-4 rounded-pill mt-3"
                         data-ccn="enrol_btn_text">'.$this->content->enrol_btn_text .'</a>';
                    }
                }
                $displaycontent .= '</div><div class="flip-card-back position-absolute box-shadow text-white rounded-lg">';
                if ($ccnblockshowdesc) {
                    $displaycontent .= '<div class="overflow-auto p-3">' . $ccncoursedescription . '</div>';
                }
                if ($ccnblockshowbottombar == 1) {
                    if ($ccnblockshowenrolbtn) {
                          $displaycontent .= '<a href="'. new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $course->cid) . '"
                          class="join-btn flp-btn position-absolute bg-white d-block h5 px-4 py-2 rounded-pill
                          text-center text-light-blue"
                          data-ccn="enrol_btn_text">'.$this->content->enrol_btn_text.'</a>';
                    }
                }
                $displaycontent .= '
                        </div>
                      </div>';
            }
        }
        $displaycontent .= '</div></div></div></div></div></section>';
        $this->content->text .= $displaycontent;
        /* INTG Customization End */
        return $this->content;
    }
    public function applicable_formats() {
        $ccnblockhandler = new ccnBlockHandler();
        return $ccnblockhandler->ccnGetBlockApplicability(array('all'));
    }
    public function html_attributes() {
        global $CFG;
        include_once($CFG->dirroot . '/theme/edumy/ccn/block_handler/attributes.php');
        $attributes = parent::html_attributes();
        return $attributes;
    }
    public function instance_allow_multiple() {
        return true;
    }
    public function has_config() {
        return false;
    }
    public function cron() {
        return true;
    }
    /* INTG Customization Start : Finding all enrolled user from the course */
    public function all_user_enroll_into_course($course) {
        global $DB;

        $sql = <<<SQL
                SELECT count(c.id) as count
                FROM {course} c
                JOIN {context} ct ON c.id = ct.instanceid
                JOIN  {role_assignments} ra ON ra.contextid = ct.id
                JOIN {user} u ON u.id = ra.userid
                JOIN {role} r ON r.id = ra.roleid
                where c.id = :course
                SQL;

        $params = ['course' => $course];

        $userenrollmentcount = $DB->get_record_sql($sql, $params);

        $totalusers = $userenrollmentcount->count;
        return $totalusers;
    }
    /* INTG Customization End */

    private function get_courses_based_on_ratings($limit = '') {
        global $DB;

        $sql = <<<SQL
                SELECT
                c.id as cid,
                c.fullname as coursename,
                AVG(cr.rating) as average,
                c.startdate as coursestartdate,
                c.enddate as courseenddate,
                cc.id as catid,
                cc.name as catname,
                cc.parent as catparent
                FROM {course} c
                LEFT JOIN {theme_edumy_courserate} cr ON c.id = cr.course
                LEFT JOIN {course_categories} cc ON cc.id = c.category
                GROUP BY c.id
                ORDER BY AVG(cr.rating) desc, c.timecreated
                SQL;

        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        $allratedcourses = $DB->get_records_sql($sql);

        return $allratedcourses;

    }
}
