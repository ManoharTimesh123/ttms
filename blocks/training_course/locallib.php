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

function get_course_list($coursetype = null, $courselimit = null) {
    global $DB;

    $currenttimestamp = time();
    $sql = <<<SQL
            SELECT *
            FROM {local_course_details} lcd
            LEFT JOIN {course} c ON  c.id = lcd.course
            WHERE c.visible = 1
            SQL;
    if ($coursetype == 'past') {
        $sql .= ' AND c.enddate <  ' . $currenttimestamp . ' ';
    }

    if ($coursetype == 'ongoing') {
        $sql .= ' AND (c.startdate < '. $currenttimestamp. ' AND c.enddate >'. $currenttimestamp .')';
    }

    if ($coursetype == 'upcoming') {
        $sql .= ' AND c.startdate >  ' . $currenttimestamp . ' ';
    }

    if ($courselimit) {
        $sql .= ' ORDER BY c.id DESC LIMIT '.$courselimit;
    } else {
        $sql .= ' ORDER BY c.id DESC';
    }
    $allcourselist = $DB->get_records_sql($sql);

    return $allcourselist;
}
