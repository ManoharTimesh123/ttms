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

namespace local_customapi\helper;

require_once($CFG->dirroot . '/course/externallib.php');

use local_customapi\exception\customapiException;
use core_course_external;

class mooccourseshelper {

    public static function read_get_user_mooc_courses($classification, $limit, $offset, $sort, $customfieldvalue) {

        $coursedetails = \core_course_external::get_enrolled_courses_by_timeline_classification($classification, $limit, $offset, $sort, $customfieldvalue);
        $mooccourses = self::get_mooc_course($coursedetails);
        return [
            'records' => $mooccourses
        ];
    }

    public static function get_mooc_course($coursedetails) {
        global $DB;
        $enrolledcourses = $coursedetails['courses'];
        $shortname = 'mooc';
        $mooccourses = array();
        $nextoffset = 0;
        foreach ($enrolledcourses as $key => $enrolledcourse) {
            $course = $enrolledcourse->id;

            $query = <<<SQL
                SELECT * from {local_course_details} lcd
                JOIN  {local_modality} m ON m.id = lcd.modality
                WHERE lcd.course =:course AND m.shortname != :shortname;
            SQL;
            $params = ['course' => $course, 'shortname' => $shortname];
            $coursemodality = $DB->get_record_sql($query, $params);
            if (empty($coursemodality) ) {
                $mooccourses['courses'][] = $enrolledcourse;
                $nextoffset = $nextoffset + 1;
            }
        }
        $nextoffset = array('nextoffset' => $nextoffset);
        $allmooccoursesdata = $mooccourses + $nextoffset;

        return $allmooccoursesdata;
    }
}
