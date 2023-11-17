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
 * User Achievements
 *
 * @package    block_user_achievements
 */


function get_certificates() {
    global $DB, $USER;
    return $DB->count_records('certificate_issues', array('userid' => $USER->id));
}
function get_mooccourses() {
    global $DB, $USER;
    $enrolcourses = enrol_get_all_users_courses($USER->id);
    $mooccourses = 0;
    $sql = 'SELECT * FROM {local_course_details} lcd
     JOIN {local_modality} lm ON lcd.modality=lm.id
     WHERE lcd.course=:course AND lm.shortname = :shortname';

    foreach ($enrolcourses as $enrolcourse) {
        $params = [
            'course' => $enrolcourse->id,
            'shortname' => 'mooc'
        ];
        if ($DB->record_exists_sql($sql, $params)) {
            $mooccourses++;
        }
    }
    return $mooccourses;
}

function get_trainings() {
    global $DB, $USER;
    $enrolcourses = enrol_get_all_users_courses($USER->id);
    $trainings = 0;
    $sql = 'SELECT * FROM {local_course_details} lcd
     JOIN {local_modality} lm ON lcd.modality=lm.id
     WHERE lcd.course=:course AND (lm.shortname = :online OR lm.shortname = :offline)';

    foreach ($enrolcourses as $enrolcourse) {
        $params = [
            'course' => $enrolcourse->id,
            'online' => 'online',
            'offline' => 'offline'
        ];
        if ($DB->record_exists_sql($sql, $params)) {
            $trainings++;
        }
    }
    return $trainings;
}

function get_user_overall_achievements() {

    $useracievement = array();
    $useracievement['badges'] = get_badges();
    $useracievement['medals'] = get_medals();
    $useracievement['certificates'] = get_certificates();
    $useracievement['courses'] = get_mooccourses();
    $useracievement['trainings'] = get_trainings();

    return $useracievement;
}
