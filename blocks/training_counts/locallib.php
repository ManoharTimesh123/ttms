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
 * Training Counts
 *
 * @package    block_training_counts
 */

function get_upcoming_training_count() {
    global $DB;

    $currenttimestamp = time();

    $upcomingtrainingsql = '
            SELECT c.id, c.*, lcd.batching FROM {course} c
            JOIN {local_course_details} lcd ON lcd.course = c.id
            JOIN {local_modality} lm ON lm.id = lcd.modality
            WHERE c.visible = 1  AND lm.shortname IN ("online", "offline")
            ';
    $upcomingtrainingsql .= ' AND  startdate >  ' . $currenttimestamp . ' ';

    $upcomingtrainingdata = $DB->get_records_sql($upcomingtrainingsql);

    $upcomingtrainingcount = 0;
    if (!empty($upcomingtrainingdata)) {
        return count($upcomingtrainingdata);
    }

    return $upcomingtrainingcount;
}

function get_completed_training_count() {
    $filters = new stdClass();
    $filters->trainingtype[] = 'past';
    $completedtrainingdata = get_annual_training_calendar($filters);

    $completedtrainingcount = 0;
    if (!empty($completedtrainingdata)) {
        return count($completedtrainingdata);
    }

    return $completedtrainingcount;
}

function get_ongoing_training_count() {
    global $DB;

    $currenttimestamp = time();

    $ongoingtrainingsql = '
            SELECT c.id, c.*, lcd.batching FROM {course} c
            JOIN {local_course_details} lcd ON lcd.course = c.id
            JOIN {local_modality} lm ON lm.id = lcd.modality
            WHERE c.visible = 1 AND lm.shortname IN ("online", "offline")
            ';

    $ongoingtrainingsql .= ' AND ( ' . $currenttimestamp . ' BETWEEN startdate AND enddate )' . ' ';

    $ongoingtrainingdata = $DB->get_records_sql($ongoingtrainingsql);

    $ongoingtrainingcount = 0;
    if (!empty($ongoingtrainingdata)) {
        return count($ongoingtrainingdata);
    }

    return $ongoingtrainingcount;
}

function get_total_courses_count() {
    global $DB;

    $coursecountgsql = '
            SELECT count(*) FROM {course} c
            JOIN {local_course_details} lcd ON lcd.course = c.id
            JOIN {local_modality} lm ON lm.id = lcd.modality
            WHERE visible = 1 AND lm.shortname = "mooc"
            ';

    return $DB->count_records_sql($coursecountgsql);

}

function get_created_training_count() {
    $createdtraining = get_training_by_status(['approved', 'launched', 'batched', 'proposed']);
    if (!empty($createdtraining)) {
        return count($createdtraining);
    }
    return 0;
}

function get_approved_training_count() {
    $approvedtraining = get_training_by_status(['approved']);
    if (!empty($approvedtraining)) {
        return count($approvedtraining);
    }
    return 0;
}

function get_proposed_training_count() {
    $proposedtraining = get_training_by_status(['proposed']);
    if (!empty($proposedtraining)) {
        return count($proposedtraining);
    }
    return 0;
}

function get_training_given_count() {
    global $DB;

    $currenttimestamp = time();

    $traininggivensql = '
            SELECT c.id, c.*, lcd.batching FROM {course} c
            JOIN {local_course_details} lcd ON lcd.course = c.id
            JOIN {local_modality} lm ON lm.id = lcd.modality
            JOIN {local_batching} lb ON lb.course = c.id
            WHERE c.visible = 1 AND lm.shortname IN ("online", "offline")
            AND lb.status = "Launched"
            ';

    $traininggivensql .= ' AND  enddate < ' . $currenttimestamp . ' ';

    $traininggivendata = $DB->get_records_sql($traininggivensql);

    $traininggivengcount = 0;
    if (!empty($traininggivendata)) {
        return count($traininggivendata);
    }

    return $traininggivengcount;
}

function get_training_by_status($status) {
    global $DB;

    $trainingcountbystatusssql = '
                    SELECT c.id, c.* FROM {local_batching} lb
                    JOIN {course} c ON c.id = lb.course
                    JOIN {local_course_details} lcd ON lcd.course = c.id
                    JOIN {local_modality} lm ON lm.id = lcd.modality
                    WHERE c.visible = 1
                    ';

    $implodedstatus = "'" . implode("', '", $status) . "'";

    $trainingcountbystatusssql .= ' AND lb.status IN (' . $implodedstatus . ')';

    $trainingcountbystatusdata = $DB->get_records_sql($trainingcountbystatusssql);

    return $trainingcountbystatusdata;
}
