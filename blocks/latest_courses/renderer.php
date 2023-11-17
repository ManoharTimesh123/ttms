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
 * @package    block_latest_courses
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/latest_courses/locallib.php');

function render_latest_course() {
    global $CFG;
    $alllatestcourse = get_all_latest_courses();
    $data = '<ul class="nav">';
    foreach ($alllatestcourse as $course) {
        $courseviewurl = new moodle_url('/course/view.php', array('id' => $course->cid));
        $data .= '<li><a href='. $courseviewurl .'>' . $course->coursename . '</a></li>';
    }
    $data .= ' </ul>';

    return $data;
}
