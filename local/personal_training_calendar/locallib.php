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
 * The personaltrainingcalendar Management
 *
 * @package    local_personaltrainingcalendar
 * @author     Lalit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

function get_personal_training_calendar($filtersql, $userid, $id = null) {
    global $DB, $CFG;

    $systemcontext = \context_system::instance();

    $groupextrasql = '';
    $currentdate = (int) date('U');
    $extrasql = '';

    if (!empty($filtersql->freetextsearch)) {
        $extrasql .= ' AND (c.fullname LIKE "%' . $filtersql->freetextsearch . '%" or ls.name LIKE "%' . $filtersql->freetextsearch . '%")';
        $groupextrasql .= ' AND (c.fullname LIKE "%' . $filtersql->freetextsearch . '%" or ls.name LIKE "%' . $filtersql->freetextsearch . '%")';
    }

    if (!empty($filtersql->trainingdate)) {
        $trainingdatesql = [];
        $trainingdategroupsql = [];
        if (is_array($filtersql->trainingdate)) {
            foreach ($filtersql->trainingdate as $randomdvalue => $trainingdate) {
                if ($trainingdate == 'past') {
                    $trainingdategroupsql[] = 'lgd.enddate < ' . $currentdate . '';
                    $trainingdatesql[] = 'c.enddate < ' . $currentdate . '';
                }

                if ($trainingdate == 'ongoing') {
                    $trainingdategroupsql[] = '(' . $currentdate. ' BETWEEN lgd.startdate AND lgd.enddate)';
                    $trainingdatesql[] = '(' . $currentdate. ' BETWEEN c.startdate AND c.enddate)';
                }

                if ($trainingdate == 'upcoming') {
                    $trainingdategroupsql[] = 'lgd.startdate > ' . $currentdate . '';
                    $trainingdatesql[] = 'c.startdate > ' . $currentdate . '';
                }
            }
        } else {
            if ($filtersql->trainingdate == 'past') {
                $trainingdategroupsql[] = 'lgd.enddate < ' . $currentdate . '';
                $trainingdatesql[] = 'c.enddate < ' . $currentdate . '';
            } else if ($filtersql->trainingdate == 'ongoing') {
                $trainingdategroupsql[] = '(' . $currentdate. ' BETWEEN lgd.startdate AND lgd.enddate)';
                $trainingdatesql[] = '(' . $currentdate. ' BETWEEN c.startdate AND c.enddate)';
            } else if ($filtersql->trainingdate == 'upcoming') {
                $trainingdategroupsql[] = 'lgd.startdate > ' . $currentdate . '';
                $trainingdatesql[] = 'c.startdate > ' . $currentdate . '';
            }
        }

        $trainingdategroupsql = '(' . implode(' OR ', $trainingdategroupsql) . ')';
        $trainingdatesql = '(' . implode(' OR ', $trainingdatesql) . ')';

        $extrasql .= ' AND '.$trainingdatesql;
        $groupextrasql .= ' AND '.$trainingdategroupsql;
    }

    if (!empty($filtersql->coursemode)) {
        $coursemodsql = [];

        if (is_array($filtersql->coursemode)) {
            foreach ($filtersql->coursemode as $randomdvalue => $coursemod) {
                $coursemodsql[] = 'lm.name LIKE "%' . $coursemod. '%"';
            }
        } else {
            $coursemodsql[] = 'lm.name LIKE "%' . $filtersql->coursemode . '%"';
        }

        $coursemodsql = '(' . implode(' OR ', $coursemodsql) . ')';
        $extrasql .= ' AND '.$coursemodsql;
    }

    if (!empty($filtersql->startdate) &&
        !empty($filtersql->enddate) &&
        $filtersql->startdate_enabled &&
        $filtersql->enddate_enabled
    ) {
        if ($filtersql->startdate > $filtersql->enddate) {
            throw new moodle_exception(get_string('startdateerrormsg', 'local_personal_training_calendar'));
        }

        $groupextrasql .= ' AND (lgd.startdate BETWEEN ' . $filtersql->startdate . ' AND ' . $filtersql->enddate . ')';
        $extrasql .= ' AND (c.startdate BETWEEN ' . $filtersql->startdate . ' AND ' . $filtersql->enddate . ')';
    }

    if ($id) {
        $extrasql .= ' AND c.id = '. $id .' ';
    }

    $extrasql .= ' ORDER BY c.startdate ASC';

    if ($filtersql->page > -1) {
        $offset = $CFG->itemperpage;
        $limit = ($filtersql->page + 1 - 1) * $offset;
        $extrasql .= ' LIMIT '. $limit .',  '. $offset .' ';
    }

    // End filter query and parameters for groups.
    $tabledatasql = 'SELECT c.id, c.*, lm.name as coursetype, lcd.batching, lcd.days, ls.name as venue, ls.description as address FROM {course} c
    Left JOIN {local_course_details} lcd ON lcd.course = c.id
    Left JOIN {local_schools} ls ON ls.id = lcd.venue
    Left JOIN {local_modality} lm ON lm.id = lcd.modality
    JOIN {enrol} e ON e.courseid = c.id
    JOIN {user_enrolments} ue ON ue.enrolid = e.id
    JOIN {user} u ON u.id = ue.userid WHERE u.id = :userid AND c.visible = :visible ' . $extrasql . ' ';


    if (has_capability('local/personal_training_calendar:viewall', $systemcontext) &&
        isset($filtersql->user) &&
        !empty($filtersql->user)
    ) {
        $userid = $filtersql->user;
    }
    $params = [
        'userid' => $userid,
        'visible' => 1
    ];

    $personaltrainingcalendar = $DB->get_records_sql($tabledatasql, $params);

    $finalarray = array();
    if (!empty($personaltrainingcalendar)) {
        foreach ($personaltrainingcalendar as $personaltrainingcalendardata) {

            $course = new stdClass();
            $course->id = $personaltrainingcalendardata->id;
            $hyphen = get_string('hyphen', 'local_batching');

            $venue = [];
            $startdatearr = [];
            $enddatearr = [];
            $noofdays = [];
            $cycle = [];
            $batched = [];
            $traningstatus = [];
            $trainingdays = [];
            $trainingname = ($personaltrainingcalendardata->fullname) ? $personaltrainingcalendardata->fullname : $hyphen;
            $trainingtype = ($personaltrainingcalendardata->coursetype) ? $personaltrainingcalendardata->coursetype : $hyphen;

            $personaltrainingcalendardatastd = new stdClass();

            $traininglink =
                "<a href='" . new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $personaltrainingcalendardata->id) . "' title='Go to Training' target='_blank'>" .
                    $trainingname .
                "</a>";

            $trainingdetaillink =
                "<a class='personal-training btn btn-secondary'
                    id='".$personaltrainingcalendardata->id."'
                    title='View Training Detail'>" .
                    get_string('viewdetailbutton', 'local_personal_training_calendar') .
                "</a>";

            $imageurl = \core_course\external\course_summary_exporter::get_course_image($course);
            if (!$imageurl) {
                $imageurl = $CFG->wwwroot . '/theme/edumy/pix/default-image.jpg';
            }

            $personaltrainingcalendardatastd->trainingid = $personaltrainingcalendardata->id;
            $personaltrainingcalendardatastd->trainingname = $trainingname;
            $personaltrainingcalendardatastd->trainingimage = $imageurl;
            $personaltrainingcalendardatastd->traininglink = $traininglink;
            $personaltrainingcalendardatastd->trainingtype = $trainingtype;

            if ($personaltrainingcalendardata->batching != 1) {
                $startdate = ($personaltrainingcalendardata->startdate) ? customdateformat('DATE_WITHOUT_TIME', $personaltrainingcalendardata->startdate) : $hyphen;
                $enddate = ($personaltrainingcalendardata->enddate) ? customdateformat('DATE_WITHOUT_TIME', $personaltrainingcalendardata->enddate) : $hyphen;
                $days = ($personaltrainingcalendardata->days) ? $personaltrainingcalendardata->days : $hyphen;
                $trainingday = $hyphen;
                $summary = ($personaltrainingcalendardata->summary) ? $personaltrainingcalendardata->summary : $hyphen;

                if ($personaltrainingcalendardata->venue) {
                    if ($personaltrainingcalendardata->address) {
                        $venues = $personaltrainingcalendardata->venue.' <br/> address:'.$personaltrainingcalendardata->address;
                    } else {
                        $venues = $personaltrainingcalendardata->venue;
                    }
                } else {
                    $venues = $hyphen;
                }

                $cycles = $hyphen;

                // Status from training.
                if (intval($personaltrainingcalendardata->enddate) < $currentdate) {
                    $status = get_string('past', 'local_personal_training_calendar');
                } else if (intval($personaltrainingcalendardata->startdate) > $currentdate) {
                    $status = get_string('upcoming', 'local_personal_training_calendar');
                } else if (intval($personaltrainingcalendardata->startdate) < $currentdate && intval($personaltrainingcalendardata->enddate) > $currentdate) {
                    $status = get_string('ongoing', 'local_personal_training_calendar');
                } else {
                    $status = $hyphen;
                }

            } else {
                $personaltrainingcalendarwithgroup = get_group_details($personaltrainingcalendardata->id, $userid, $groupextrasql);
                foreach ($personaltrainingcalendarwithgroup as $personaltrainingcalendarwithgroupdata) {
                    if ($personaltrainingcalendarwithgroupdata->name) {
                        $groupstartdate = customdateformat('DATE_WITHOUT_TIME', $personaltrainingcalendarwithgroupdata->startdate);
                        $groupenddate = customdateformat('DATE_WITHOUT_TIME', $personaltrainingcalendarwithgroupdata->enddate);
                        $trainingdays[] = customdateformat('DAY_NAME', $personaltrainingcalendarwithgroupdata->startdate).' '.
                        customdateformat('DATE_WITH_TIME', $personaltrainingcalendarwithgroupdata->startdate) .' - '.
                        customdateformat('DAY_NAME', $personaltrainingcalendarwithgroupdata->enddate) . ' ' . customdateformat('DATE_WITH_TIME', $personaltrainingcalendarwithgroupdata->enddate);
                        $noofdays[] = $personaltrainingcalendarwithgroupdata->days;
                        $startdatearr[] = $groupstartdate;
                        $enddatearr[] = $groupenddate;

                        if ($personaltrainingcalendarwithgroupdata->name) {

                            if ($personaltrainingcalendarwithgroupdata->address) {
                                $venue[] = $personaltrainingcalendarwithgroupdata->name.'<br/> address:'.$personaltrainingcalendarwithgroupdata->address;
                            } else {
                                $venue[] = $personaltrainingcalendarwithgroupdata->name;
                            }
                        }

                        $cycle[] = $personaltrainingcalendarwithgroupdata->cyclename;
                        $batched[] = $personaltrainingcalendarwithgroupdata->batchname;
                    }
                }

                $cyclestartdate = reset($startdatearr);
                $cycleenddate = end($enddatearr);
                $trainingstartdate = date('Y-m-d', strtotime($cyclestartdate));
                $trainingendatedate = date('Y-m-d', strtotime($cycleenddate));

                // Get training status.
                $traningstatus = get_training_status($trainingstartdate, $trainingendatedate);


                $startdate = ($startdatearr) ? split_data($startdatearr) : $hyphen;
                $enddate = ($enddatearr) ? split_data($enddatearr) : $hyphen;

                $days = ($noofdays) ? max($noofdays) : $hyphen;
                $trainingday = ($trainingdays) ? split_data($trainingdays) : $hyphen;
                $summary = ($personaltrainingcalendardata->summary) ? $personaltrainingcalendardata->summary : $hyphen;
                $venues = ($venue) ? split_data($venue) : $hyphen;
                $trainingcycle = '';
                if ($cycle) {
                    $cycle = array_filter($cycle);
                    $trainingcycle = split_data($batched);
                }

                $trainingbatched = '';
                if ($batched) {
                    $trainingbatched = split_data($batched);
                }

                if ($trainingcycle && $trainingbatched) {
                    $cycles = $trainingcycle.'/'.$trainingbatched;
                } else if ($trainingbatched && !$trainingcycle) {
                    $cycles = $trainingbatched;
                } else {
                    $cycles = $hyphen;
                }

                $status = ($traningstatus) ? split_data($traningstatus) : $hyphen;
            }
            $course = get_course($personaltrainingcalendardata->id);
            $context = context_course::instance($course->id);

            // Get all facilitator by its role.
            $facilitatorname = get_users_by_roleinfo('facilitator', $context);
            $facilitator = ($facilitatorname) ? split_data($facilitatorname) : $hyphen;

            // Get all coordinators by its role.
            $coordinatorname = get_users_by_roleinfo('coordinator', $context);
            $coordinator = ($coordinatorname) ? split_data($coordinatorname) : $hyphen;

            $personaltrainingcalendardatastd->startdate = $startdate;
            $personaltrainingcalendardatastd->enddate = $enddate;
            $personaltrainingcalendardatastd->days = $days;
            $personaltrainingcalendardatastd->trainingdays = $trainingday;
            $personaltrainingcalendardatastd->summary = $summary;
            $personaltrainingcalendardatastd->venue = $venues;
            $personaltrainingcalendardatastd->cycle = $cycles;
            $personaltrainingcalendardatastd->status = $status;
            $personaltrainingcalendardatastd->facilitator = $facilitator;
            $personaltrainingcalendardatastd->coordinator = $coordinator;
            $personaltrainingcalendardatastd->trainindetaillink = $trainingdetaillink;

            $finalarray[] = $personaltrainingcalendardatastd;
        }

    }
    return $finalarray;
}

function get_group_details($courseid, $userid, $groupfiltersql = '') {
    global $USER, $DB;
    $groupdetailsparams = [
        'userid' => $userid,
        'courseid' => $courseid
    ];

    // Sql query for getting group details.
    $groupdetailssql = 'SELECT lgd.id, g.id as groupid, gs.name as cyclename, g.name as batchname, ls.name, ls.description as address, lgd.startdate, lgd.enddate, lgd.days
    FROM {local_schools} ls
    JOIN {local_group_details} lgd ON ls.id = lgd.venue
    JOIN {groups} g ON g.id = lgd.groupid
    JOIN {groups_members} gm ON gm.groupid = g.id
    LEFT JOIN {groupings_groups} gg ON gg.groupid = g.id
    LEFT JOIN {groupings} gs ON gs.id = gg.groupingid
    JOIN {course} c ON c.id = g.courseid
    WHERE c.id = :courseid and gm.userid = :userid and c.visible = 1 ' . $groupfiltersql . ' ORDER by lgd.days';
    // Insert venue information according to group.
    $groupdetails = $DB->get_records_sql($groupdetailssql, $groupdetailsparams);
    return $groupdetails;
}

function split_data($data) {
    if (count($data) > 1) {
        return '<span class="data_wrapper">' . implode('</span> <span class="inner_data">', $data) . '</span>';
    } else {
        return $data[0];
    }
}

function get_users_by_roleinfo($rolename, $context) {
    global $DB;
    $role = $DB->get_record('role', array('shortname' => $rolename));
    $roleid = $role->id;
    $users = get_role_users($roleid, $context);
    $allusers = array();
    foreach ($users as $user) {
        $allusers[] = $user->username;
    }
    return $allusers;
}

function get_training_status($trainingstartdate, $trainingenddate) {
    $currentdate = date('Y-m-d');

    if ($trainingstartdate < $currentdate) {
        $traningstatus[] = get_string('past', 'local_personal_training_calendar');
    }

    if ($trainingstartdate > $currentdate) {
        $traningstatus[] = get_string('upcoming', 'local_personal_training_calendar');
    }

    if ($trainingstartdate <= $currentdate && $trainingenddate >= $currentdate) {
        $traningstatus[] = get_string('ongoing', 'local_personal_training_calendar');
    }


    return $traningstatus;
}
