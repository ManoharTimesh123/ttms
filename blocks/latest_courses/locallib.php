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
 * latest courses
 *
 * @package    block_latest_courses
 */

function get_all_latest_courses() {
    global $DB;

    $sql = <<<SQL
            SELECT c.id as cid, c.fullname as coursename,
            c.timecreated as coursecreated
            FROM {course} c GROUP BY c.id
            ORDER BY  c.timecreated desc LIMIT 7
            SQL;
    $alllatestcourses = $DB->get_records_sql($sql);

    return $alllatestcourses;
}
