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
 * Training Course
 *
 * @package    block_training_course
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot. '/theme/edumy/ccn/course_handler/ccn_course_handler.php');
require_once($CFG->dirroot. '/course/renderer.php');
require_once($CFG->dirroot. '/theme/edumy/ccn/block_handler/ccn_block_handler.php');
require_once('locallib.php');

class block_training_course extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_training_course');
    }

    /*Hide block title in frontend*/
    public function hide_header() {
        return true;
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $DB;

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

        if (!empty($this->config->title)) {
            $this->content->title = $this->config->title;
        } else {
            $this->content->title = '';
        }

        if (!empty($this->config->description)) {
            $this->content->description = $this->config->description;
        } else {
            $this->content->description = '';
        }
        $courselimit = $this->config->course_limit ?? 6;

        $this->content->text = '
         <section id="our-top-courses" class="featured-wrap">
            <div class="container d-flex featured-main">
                <ul class="featured-tab nav mt-5 w-25 position-relative d-inline-block">
                    <li
                        class="w-100 position-relative d-flex justify-content-start align-items-center px-4 active"
                        data-tab="couse-one">
                        <a class="tab-txt position-relative">
                            <i class="flaticon-pencil mr-2"> </i>
                            ' . get_string('upcoming_trainings', 'block_training_course') . '
                        </a>
                    </li>
                    <li
                        class="w-100 position-relative d-flex justify-content-start align-items-center px-4"
                        data-tab="couse-two">
                        <a class="tab-txt position-relative">
                            <i class="flaticon-graduation-cap mr-2"> </i>
                            ' . get_string('completed_trainings', 'block_training_course') . '
                        </a>
                    </li>
                    <li
                        class="w-100 position-relative d-flex justify-content-start align-items-center px-4"
                        data-tab="couse-three">
                        <a class="tab-txt position-relative">
                            <i class="flaticon-cap mr-2"> </i>
                            ' . get_string('ongoing_trainings', 'block_training_course') . '
                        </a>
                    </li>
                </ul>
                    <div class="w-75 bg-white py-4 px-3 feature-work-area position-relative">
                        <div class="course-box w-100" id="couse-one">
                            <div class="top-area text-center mb-5 position-relative">
                                <h2>Upcoming <span> Trainings </span> </h2>
                                <p>' . get_string('description', 'block_training_course') . ',</p>
                                <img src="../theme/edumy/pix/training-arrow-img.png" alt="training-arrow-img" width="90">
                            </div>
                            <div class="row">';
        $coursetype = 'upcoming';
        $upcomingtrainingcourse = get_course_list($coursetype, $courselimit);
        if (!empty($upcomingtrainingcourse)) {
            foreach ($upcomingtrainingcourse as $key => $ccncourseid) {
                if ($ccncourseid->id) {
                    $ccncoursehandler = new ccnCourseHandler();
                    $ccncourse = $ccncoursehandler->ccnGetCourseDetails($ccncourseid->id);
                    $this->content->text .= '
                    <div class="col-md-4">
                        <div class="box1 tooltip-hover">
                            <div class="tooltip-content p-4 w-100 rounded text-white">
                                <h3 class="text-white"> '. $ccncourse->fullName .'</h3>
                                <p>';
                                $this->content->text .= $ccncourse->summary;
                                $this->content->text .= '</p>
                            </div>
                            <div class="Course-component box-shadow bg-white p-2 mt-3 mb-3">';
                                $this->content->text .= $ccncourse->ccnRender->coverImage;
                                $this->content->text .= '
                                <div class="card-footer p-0 border-0 bg-transparent mt-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                        <span>';
                                        $cname = explode(" ", $ccncourse->categoryName);
                                        $categoryname = $cname[0] . '...';
                                        $this->content->text .= $categoryname;
                                        $this->content->text .= '</span>
                                        </div>
                                    <div class="col-md-6 text-right">';
                                        $this->content->text .= $ccncourse->ccnRender->starRating;
                                        $this->content->text .= '
                                        </div>
                                    </div>
                                </div>
                                <div class="course-name">';
                                    $this->content->text .= $ccncourse->ccnRender->title;
                                    $this->content->text .= '
                                </div>';
                                $this->content->text .= '
                            </div>
                        </div>
                    </div>';
                }
            }
        } else {
            $this->content->text .= '
            <div
                class="col-12 content-area align-items-center d-flex flex-column justify-content-center text-center">
                <img class="mb-3" src="theme/edumy/pix/online-training.png" alt=" ">
                <h5>' . get_string('notrainingcourseavailable', 'block_training_course') . '</h5>
            </div>';
        }
        $this->content->text .= '
                            </div>
                        </div>

                        <div class="course-box w-100" id="couse-two">
                            <div class="top-area text-center mb-5 position-relative">
                                <h2>Completed <span> Trainings </span> </h2>
                                <p>' . get_string('description', 'block_training_course') . '</p>
                                 <img src="../theme/edumy/pix/training-arrow-img.png" alt="training-arrow-img" width="90">
                            </div>
                            <div class="row">';
        $coursetype = 'past';
        $completedtrainingcourse = get_course_list($coursetype, $courselimit);
        if (!empty($completedtrainingcourse)) {
            foreach ($completedtrainingcourse as $key => $ccncourseid) {
                if ($ccncourseid->id) {
                    $ccncoursehandler = new ccnCourseHandler();
                    $ccncourse = $ccncoursehandler->ccnGetCourseDetails($ccncourseid->id);

                    $this->content->text .= '
                    <div class="col-md-4">
                        <div class="box1 tooltip-hover">
                            <div class="tooltip-content p-4 w-100 rounded text-white">
                                <h3 class="text-white">'. $ccncourse->fullName .'</h3>
                                <p>';
                                $this->content->text .= $ccncourse->summary;
                                $this->content->text .= '</p>
                            </div>
                            <div class="Course-component box-shadow bg-white p-2 mt-3 mb-3">';
                                $this->content->text .= $ccncourse->ccnRender->coverImage;
                                $this->content->text .= '
                                <div class="card-footer p-0 border-0 bg-transparent mt-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                        <span>';
                                        $cname = explode(" ", $ccncourse->categoryName);
                                        $categoryname = $cname[0] . '...';
                                        $this->content->text .= $categoryname;
                                        $this->content->text .= '</span>
                                        </div>
                                    <div class="col-md-6 text-right">';
                                        $this->content->text .= $ccncourse->ccnRender->starRating;
                                        $this->content->text .= '
                                        </div>
                                    </div>
                                </div>
                                <div class="course-name">';
                                    $this->content->text .= $ccncourse->ccnRender->title;
                                    $this->content->text .= '
                                </div>';
                                $this->content->text .= '
                            </div>
                        </div>
                    </div>';
                }
            }
        } else {
            $this->content->text .= '
            <div
                class="col-12 content-area align-items-center d-flex flex-column justify-content-center text-center">
                <img class="mb-3" src="theme/edumy/pix/online-training.png" alt=" ">
                <h5>' . get_string('notrainingcourseavailable', 'block_training_course') . '</h5>
            </div>';
        }
        $this->content->text .= '
                            </div>
                        </div>

                        <div class="course-box w-100" id="couse-three">
                            <div class="top-area text-center mb-5 position-relative">
                                <h2>Ongoing <span> Trainings </span> </h2>
                                <p>' . get_string('description', 'block_training_course') . ',</p>
                                 <img src="../theme/edumy/pix/training-arrow-img.png" alt="training-arrow-img" width="90">
                            </div>
                            <div class="row">';
        $coursetype = 'ongoing';
        $ongoingtrainingcourse = get_course_list($coursetype, $courselimit);
        if (!empty($ongoingtrainingcourse)) {
            foreach ($ongoingtrainingcourse as $key => $ccncourseid) {
                if ($ccncourseid->id) {
                    $ccncoursehandler = new ccnCourseHandler();
                    $ccncourse = $ccncoursehandler->ccnGetCourseDetails($ccncourseid->id);
                    $this->content->text .= '
                    <div class="col-md-4 tooltip-main">
                        <div class="box1 tooltip-hover">
                            <div class="tooltip-content p-4 w-100 rounded text-white">
                                <h3 class="text-white"> '. $ccncourse->fullName .'</h3>
                                <div class="tooltip-content-inner">
                                <p>';
                                $this->content->text .= $ccncourse->summary;
                                $this->content->text .= '</p>
                                </div>
                            </div>
                            <div class="Course-component box-shadow bg-white p-2 mt-3 mb-3">';
                                $this->content->text .= $ccncourse->ccnRender->coverImage;
                                $this->content->text .= '
                                <div class="card-footer p-0 border-0 bg-transparent mt-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                        <span>';
                                        $cname = explode(" ", $ccncourse->categoryName);
                                        $categoryname = $cname[0] . '...';
                                        $this->content->text .= $categoryname;
                                        $this->content->text .= '</span>
                                        </div>
                                    <div class="col-md-6 text-right">';
                                        $this->content->text .= $ccncourse->ccnRender->starRating;
                                        $this->content->text .= '
                                        </div>
                                    </div>
                                </div>
                                <div class="course-name">';
                                    $this->content->text .= $ccncourse->ccnRender->title;
                                    $this->content->text .= '
                                </div>';
                                $this->content->text .= '
                            </div>
                        </div>
                    </div>';
                }
            }
        } else {
            $this->content->text .= '
            <div
                class="col-12 content-area align-items-center d-flex flex-column justify-content-center text-center">
                <img class="mb-3" src="theme/edumy/pix/online-training.png" alt=" ">
                <h5>' . get_string('notrainingcourseavailable', 'block_training_course') . '</h5>
            </div>';
        }
        $this->content->text .= '
                        </div>
                    </div>
                </div>
            </div>
        </section>';
        $this->page->requires->js('/blocks/training_course/js/training_course.js');
        return $this->content;
    }

    /**
     * Allow multiple instances in a single course?
     *
     * @return bool True if multiple instances are allowed, false otherwise.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        $ccnblockhandler = new ccnBlockHandler();
        return $ccnblockhandler->ccnGetBlockApplicability(array('all'));
    }

    public function html_attributes() {
        global $CFG;
        $attributes = parent::html_attributes();
        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/attributes.php');
        return $attributes;
    }

}
