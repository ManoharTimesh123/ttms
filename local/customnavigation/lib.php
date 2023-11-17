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
 * Remove options from Navigation Menu.
 *
 * @package local_customnavigation
 * @author Sangita Kumari
 */

/**
 * Extend global navigation and remove options
 *
 * @param global_navigation $navigation {@link global_navigation}
 * @return void
 */
function local_customnavigation_extend_navigation(global_navigation $navigation) {
    global $CFG, $COURSE, $DB;
  
    if ($coursesnode = $navigation->find('courses', global_navigation::TYPE_ROOTNODE)) {
        // Find course belong to training or mooc.
        $course = $COURSE->id;

        $query = <<<SQL
            SELECT * from {local_course_details} lcd
            JOIN  {local_modality} m ON m.id = lcd.modality
            WHERE lcd.course =:course;
        SQL;

        $params = ['course' => $course];
        $coursemodality = $DB->get_record_sql($query, $params);

        if ($coursemodality->shortname != 'mooc') {
            $courseurl = new moodle_url('/local/personal_training_calendar/index.php');
        } else {
            $courseurl = new moodle_url('/blocks/course_catalog/listing.php');
        }
        
        // Change course url according to course type.
        $coursesnode->action = $courseurl;
    }

    if ($calendarnode = $navigation->find('calendar', global_navigation::TYPE_CUSTOM)) {
        // Hide calendar node.
        $calendarnode->showinflatnavigation = false;
    }

    if ($privatefilesnode = $navigation->find('privatefiles', global_navigation::TYPE_SETTING)) {
        // Hide privatefiles node.
        $privatefilesnode->showinflatnavigation = false;
    }

    if ($contentbanknode = $navigation->find('contentbank', global_navigation::TYPE_CUSTOM)) {
        // Hide privatefiles node.
        $contentbanknode->showinflatnavigation = false;
    }

    $mycoursesnode = $navigation->find('mycourses', global_navigation::TYPE_ROOTNODE);
    $mycourseschildrennodeskeys = $mycoursesnode->get_children_key_list();

    // If yes, do it.
    if ($mycoursesnode) {
        // Hide mycourses node.
        $mycoursesnode->showinflatnavigation = false;

        // Hide all courses below the mycourses node.
        foreach ($mycourseschildrennodeskeys as $k) {
            // If the admin decided to display categories, things get slightly complicated.
            if ($CFG->navshowmycoursecategories) {
                // We need to find all children nodes first.
                $allchildrennodes = local_boostnavigation_get_all_childrenkeys($mycoursesnode->get($k));
                // Then we can hide each children node.
                // Unfortunately, the children nodes have navigation_node type TYPE_MY_CATEGORY or navigation_node type
                // TYPE_COURSE, thus we need to search without a specific navigation_node type.
                foreach ($allchildrennodes as $cn) {
                    $mycoursesnode->find($cn, null)->showinflatnavigation = false;
                }
                // Otherwise we have a flat navigation tree and hiding the courses is easy.
            } else {
                $mycoursesnode->get($k)->showinflatnavigation = false;
            }
        }
    }
}
