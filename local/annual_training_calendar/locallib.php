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
 * Annual Training Calendar
 */

function get_annual_training_calendar($filters, $id = null) {
    global $DB, $CFG;

    $currenttimestamp = time();

    $annualtrainingcalendarsql = '
    SELECT c.id, c.*, lm.name coursetype, lcd.batching, lcd.days, lcd.venue, ls.name as venue
    FROM {course} c
    JOIN {local_course_details} lcd ON lcd.course = c.id
    LEFT JOIN {local_schools} ls ON ls.id = lcd.venue
    LEFT JOIN {local_modality} lm ON lm.id = lcd.modality
    WHERE c.visible = 1
    ';
    if (isset($filters->keyword) && !empty($filters->keyword)) {
        $annualtrainingcalendarsql .= ' AND c.fullname LIKE "%' . $filters->keyword . '%"';
    }

    if (isset($filters->startdate) &&
        isset($filters->enddate)  &&
        !empty($filters->startdate)  &&
        !empty($filters->enddate) &&
        $filters->startdate_enabled &&
        $filters->enddate_enabled
    ) {
        if ($filters->startdate > $filters->enddate) {
            throw new moodle_exception('The start date cannot be greater than end date.');
        }
        $annualtrainingcalendarsql .= ' AND ( c.startdate BETWEEN  ' . $filters->startdate . ' AND  ' . $filters->enddate . ' )';
    }

    if (isset($filters->trainingtype)) {
        $trainingtypeconditions = [];
        foreach ($filters->trainingtype as $trainingtype) {

            if (!empty($trainingtype)  && $trainingtype == 'past') {
                $trainingtypeconditions[] = ' c.enddate <  ' . $currenttimestamp . ' ';
            }

            if (!empty($trainingtype)  && $trainingtype == 'ongoing') {
                $trainingtypeconditions[] = $currenttimestamp . ' BETWEEN c.startdate AND c.enddate ';
            }

            if (!empty($trainingtype)  && $trainingtype == 'upcoming') {
                $trainingtypeconditions[] = ' c.startdate >  ' . $currenttimestamp . ' ';
            }
        }

        if (count($trainingtypeconditions) > 0) {
            $annualtrainingcalendarsql .= ' AND (' . implode(' OR ', $trainingtypeconditions) . ')';
        }
    }

    if (isset($filters->trainingmode)) {
        $trainingmodecondition = array();
        foreach ($filters->trainingmode as $trainingmode) {
            if (!empty($trainingmode)  && $trainingtype == 'ongoing') {
                $trainingmodecondition[] = ' lm.name LIKE "%' . $trainingmode . '%"';
            }
        }

        if (count($trainingmodecondition) > 0) {
            $annualtrainingcalendarsql .= ' AND (' . implode(' OR ', $trainingmodecondition) . ' ) ';
        }
    }

    if ($id) {
        $annualtrainingcalendarsql .= ' AND c.id = '. $id .' ';
    }

    $annualtrainingcalendarsql .= ' ORDER BY c.id DESC';

    if ($filters->page > -1) {
        $offset = $CFG->itemperpage;
        $limit = ($filters->page + 1 - 1) * $offset;
        $annualtrainingcalendarsql .= ' LIMIT '. $limit .',  '. $offset .' ';
    }

    $annualtrainingcalendarsdata = $DB->get_records_sql($annualtrainingcalendarsql);

    $annualtrainingcalendars = array();

    if (!empty($annualtrainingcalendarsdata)) {
        foreach ($annualtrainingcalendarsdata as $annualtrainingcalendar) {
            $courseid = $annualtrainingcalendar->id;

            $course = new stdClass();
            $course->id = $courseid;

            $status = ($annualtrainingcalendar->batchingstatus) ? ucfirst($annualtrainingcalendar->batchingstatus) : get_string('hyphen', 'local_annual_training_calendar');

            $participants = (count_user_enrolment_by_course($courseid, 'student'))
                ? count_user_enrolment_by_course($courseid, 'student')
                : get_string('hyphen', 'local_annual_training_calendar');
            $facilitators = (count_user_enrolment_by_course($courseid, 'facilitator'))
                ? count_user_enrolment_by_course($courseid, 'facilitator')
                : get_string('hyphen', 'local_annual_training_calendar');
            $coordinates = (count_user_enrolment_by_course($courseid, 'coordinator'))
                ? count_user_enrolment_by_course($courseid, 'coordinator')
                : get_string('hyphen', 'local_annual_training_calendar');
            $startdate = customdateformat('DATE_WITHOUT_TIME', $annualtrainingcalendar->startdate);
            $enddate = customdateformat('DATE_WITHOUT_TIME', $annualtrainingcalendar->enddate);

            $imageurl = \core_course\external\course_summary_exporter::get_course_image($course);
            if (!$imageurl) {
                $imageurl = $CFG->wwwroot . '/theme/edumy/pix/default-image.jpg';
            }

            // Status from training.
            if (intval($annualtrainingcalendar->enddate) < $currenttimestamp) {
                $status = get_string('past', 'local_annual_training_calendar');
            } else if (intval($annualtrainingcalendar->startdate) > $currenttimestamp) {
                $status = get_string('upcoming', 'local_annual_training_calendar');
            } else if (intval($annualtrainingcalendar->startdate) < $currenttimestamp &&
                intval($annualtrainingcalendar->enddate) > $currenttimestamp
            ) {
                $status = get_string('ongoing', 'local_annual_training_calendar');
            } else {
                $status = get_string('hyphen', 'local_annual_training_calendar');
            }

            $detailurl = $CFG->wwwroot . '/local/annual_training_calendar/detail.php?id=' . $annualtrainingcalendar->id;

            $trainingdetaillink =
                "<a class='annual-training btn btn-secondary'
                    id='".$annualtrainingcalendar->id."'
                    title='" . get_string('viewtrainingdetails', 'local_annual_training_calendar') . "'>" .
                    get_string('viewdetailbutton', 'local_annual_training_calendar') .
                "</a>";

            // Course created from batching get start and end date from group detail.
            if ($annualtrainingcalendar->batching == 1) {

                $groupdetail = get_course_group_detail_by_course($courseid, $filters);

                $finacial = get_financial_details_of_trianing($courseid);

                    $totalcycles = $groupdetail['total_cycles'] ?? 0;
                    $totalbatches = $groupdetail['total_batches'] ?? 0;
                    $totalvenues = $groupdetail['total_venues'] ?? 0;

                    $traininglink =
                        "<a href='" . new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $annualtrainingcalendar->id) . "'
                            title='" . get_string('gototraining', 'local_annual_training_calendar') . "' target='_blank'>" .
                            $annualtrainingcalendar->fullname .
                        "</a>";

                    $annualcalendar = new stdClass();
                    $annualcalendar->trainingid = $annualtrainingcalendar->id;
                    $annualcalendar->trainingname = $annualtrainingcalendar->fullname;
                    $annualcalendar->trainingimage = $imageurl;
                    $annualcalendar->traininglink = $traininglink;
                    $annualcalendar->trainingtype = $annualtrainingcalendar->coursetype;
                    $annualcalendar->trainingstartdate = $startdate;
                    $annualcalendar->trainingenddate = $enddate;
                    $annualcalendar->trainingnoofdays = $annualtrainingcalendar->days;
                    $annualcalendar->totalparticipants = $participants;
                    $annualcalendar->totalcycles = $totalcycles;
                    $annualcalendar->totalbatches = $totalbatches;
                    $annualcalendar->status = $status;
                    $annualcalendar->totalcost = $finacial->total_cost;
                    $annualcalendar->totalvenueinvolved = $totalvenues;
                    $annualcalendar->totalfacilitators = $facilitators;
                    $annualcalendar->totalcoordinators = $coordinates;
                    $annualcalendar->trainindetaillink = $trainingdetaillink;
                    $annualcalendar->description = $annualtrainingcalendar->summary;
                    $annualtrainingcalendars[] = $annualcalendar;
            } else {
                $totalcycles = get_string('hyphen', 'local_annual_training_calendar');
                $totalbatches = get_string('hyphen', 'local_annual_training_calendar');
                $totalvenues = get_string('hyphen', 'local_annual_training_calendar');

                $annualcalendar = new stdClass();
                $annualcalendar->trainingid = $annualtrainingcalendar->id;
                $annualcalendar->trainingname = $annualtrainingcalendar->fullname;
                $annualcalendar->trainingimage = $imageurl;
                $annualcalendar->traininglink = $traininglink;
                $annualcalendar->trainingtype = $annualtrainingcalendar->coursetype;
                $annualcalendar->trainingstartdate = $startdate;
                $annualcalendar->trainingenddate = $enddate;
                $annualcalendar->trainingnoofdays = $annualtrainingcalendar->days;
                $annualcalendar->totalparticipants = $participants;
                $annualcalendar->totalcycles = $totalcycles;
                $annualcalendar->totalbatches = $totalbatches;
                $annualcalendar->status = $status;
                $annualcalendar->totalcost = $finacial->total_cost;
                $annualcalendar->totalvenueinvolved = $totalvenues;
                $annualcalendar->totalfacilitators = $facilitators;
                $annualcalendar->totalcoordinators = $coordinates;
                $annualcalendar->trainindetaillink = $trainingdetaillink;
                $annualcalendar->description = $annualtrainingcalendar->summary;
                $annualtrainingcalendars[] = $annualcalendar;
            }
        }
    }

    return $annualtrainingcalendars;
}

function get_financial_details_of_trianing($courseid) {
    global $DB;

    $financialsql = '
                    SELECT SUM(cost) as total_cost FROM {local_batching_financials} bf
                    INNER JOIN {local_batching} b ON b.id = bf.batching
                    WHERE b.course = :courseid
                    ';
    $params = [
        'courseid' => $courseid
    ];

    $financialcount = $DB->get_record_sql($financialsql, $params);

    return $financialcount;
}

function get_course_group_detail_by_course($courseid) {
    global $DB;

    $currenttimestamp = time();

    $coursegroupdetailssql = '
                    SELECT * FROM {local_group_details} lgd
                    JOIN {groups} g ON g.id = lgd.groupid
                    WHERE g.courseid = :courseid
                    ';
    $params = [
        'courseid' => $courseid
    ];

    $coursegroupdetails = $DB->get_records_sql($coursegroupdetailssql, $params);

    $coursegroupdetailformateddata = array();

    if (!empty($coursegroupdetails)) {
        $venues = array();

        foreach ($coursegroupdetails as $groupdetail) {
            $venues[$groupdetail->venue] = $groupdetail->venue;
        }

        $coursegroupdetailformateddata['total_cycles'] = count_total_cycle_by_course($courseid);
        $coursegroupdetailformateddata['total_batches'] = count($coursegroupdetails);
        $coursegroupdetailformateddata['total_venues'] = count($venues);
    } else {
        $coursegroupdetailformateddata['total_cycles'] = count_total_cycle_by_course($courseid);
        $coursegroupdetailformateddata['total_batches'] = count($coursegroupdetails);
        $coursegroupdetailformateddata['total_venues'] = 0;
    }

    return $coursegroupdetailformateddata;
}

function count_total_cycle_by_course($courseid) {
    global $DB;

    $coursetotalcyclesql = '
                    SELECT count(*) FROM {groupings}
                    WHERE courseid = :courseid
                    ';
    $params = [
        'courseid' => $courseid
    ];

    return $DB->count_records_sql($coursetotalcyclesql, $params);
}

function count_user_enrolment_by_course($courseid, $role) {
    global $DB;

    $course = get_course($courseid);
    $context = context_course::instance($course->id);

    $userrole = $DB->get_record('role', array('shortname' => $role));

    $users = get_role_users($userrole->id, $context);

    return count($users);
}
