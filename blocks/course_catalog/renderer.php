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
 * Course Catalog
 * @package    block_course_catalog
 */
defined('MOODLE_INTERNAL') || die();
require($CFG->dirroot . '/blocks/course_catalog/locallib.php');

function render_course_catalog_grid() {
    global $DB, $CFG;
    $get_course_sql = <<<SQL_QUERY
            SELECT c.id, c.fullname, cc.name as cat_name
            FROM {course} c
            JOIN {course_categories} cc ON cc.id = c.category
            LIMIT 6
            SQL_QUERY;

    $courses = $DB->get_records_sql($get_course_sql);
    $course_listings = '';
    foreach ($courses as $course) {
        /* INTG Customization Start : Adding course rating in all courses in course catalog page */
        $cid = $course->id;
        $block = block_instance('cocoon_course_rating');
        $ratingdata = $block->external_star_rating($cid);
        $courseimgurl = \core_course\external\course_summary_exporter::get_course_image($course);
        if (empty($courseimgurl)) {
            $courseimgurl = $CFG->wwwroot . '/blocks/course_catalog/pix/no-image.png';
        }
        $course_listings .= '
                    <div class="col-md-4 mb-4">
                              <a class="top_courses box-shadow p-0 border-0 h-100 top_courses_wrapper bg-white d-block" target="_blank" href="'. $CFG->wwwroot .'/course/view.php?id=' . $course->id . '">
                                <div class="top_courses_img w-100">
                                    <img class="img-fluid w-100 h-100" src="' . $courseimgurl . '" />
                                </div>
                                <div class="top_courses_content p-3">
                                    <h6 class="">6 weeks</h6>
                                    <p class="mb-1">'. $course->cat_name .'</p>
                                    <h4 class="font-weight-bold">'. $course->fullname .'</h4>
                                    <div class = "course-rating d-flex justify-content-between">
                                     <div>'.$ratingdata.'</div>
                                     <div>493 <i class="fa fa-user" aria-hidden="true"></i></div>
                                    </div>
                                </div>                                
                                <div class="course-join-btn font-weight-bold d-flex justify-content-center align-items-center w-100">
                                    <span class="px-3 py-2">Join course 
                                        <i class="flaticon-right-arrow"></i>
                                    </span>
                                </div>
                              </a>
                    </div>';
    /* INTG Customization End */
    }

    $course_categories = build_category_hierarchy();
    $category_listings = build_category_tree($course_categories);

    $output = '<div class="bg-white rounded-lg box-shadow">
        <div class="row h-100">
            <div class="col-md-3 category_list_items">
             <div class="shadow rounded h-100">
                <h4 class="border-bottom mx-3 pb-2 pt-3">Course Categories</h4>
                    '. $category_listings .'
             </div>
            </div>
            <div class="col-md-9">
               <div class="px-4">
               <h4 class="pt-4 pb-3">Teacher Training Level <span>6</span></h4>
               <div class="row" id="course_catalog">
               '. $course_listings .'
               </div>
               </div>
            </div>
        </div>
    </div>';

    return $output;
}