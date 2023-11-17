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
 * Training Transcript
 */

function get_training_transcript($filters, $id = null) {
    global $DB, $USER, $CFG;

    $systemcontext = \context_system::instance();

    $trainingtranscriptssql = '
            SELECT c.id, c.*, lm.name as coursetype, lcd.batching, lcd.days, ls.name as venue, ls.description as address
            FROM {course} c
            LEFT JOIN {local_course_details} lcd ON lcd.course = c.id
            LEFT JOIN {local_schools} ls ON ls.id = lcd.venue
            LEFT JOIN {local_modality} lm ON lm.id = lcd.modality
            JOIN {enrol} e ON e.courseid = c.id
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            JOIN {user} u ON u.id = ue.userid
            WHERE u.id = :userid AND c.visible = 1
            AND c.enddate < :enddate
            ';

    if ($filters->keyword) {
        $trainingtranscriptssql .= ' AND c.fullname LIKE "%' . $filters->keyword . '%"';
    }

    if ($filters->startdate &&
        $filters->enddate &&
        $filters->startdate_enabled &&
        $filters->enddate_enabled
    ) {
        if ($filters->startdate > $filters->enddate) {
            throw new moodle_exception(get_string('startdateissue', 'local_training_transcript'));
        }
        $trainingtranscriptssql .= ' AND ( c.startdate BETWEEN  ' . $filters->startdate . ' AND  ' . $filters->enddate . ' )';
    }

    if ($filters->trainingmode) {
        $trainingmodecondition = array();
        foreach ($filters->trainingmode as $trainingmode) {
            $trainingmodecondition[] = ' lm.name LIKE "%' . $trainingmode . '%"';
        }

        if (count($trainingmodecondition) > 0) {
            $trainingtranscriptssql .= ' AND (' . implode(' OR ', $trainingmodecondition) . ' ) ';
        }
    }

    if ($id) {
        $trainingtranscriptssql .= ' AND c.id = '. $id .' ';
    }

    if ($filters->page > -1) {
        $offset = $CFG->itemperpage;
        $limit = ($filters->page + 1 - 1) * $offset;
        $trainingtranscriptssql .= ' LIMIT '. $limit .',  '. $offset .' ';
    }

    $userid = $USER->id;

    if (has_capability('local/training_transcript:viewall', $systemcontext) &&
        isset($filters->user) &&
        !empty($filters->user)
    ) {
        $userid = $filters->user;
    }

    $params = [
        'userid' => $userid,
        'enddate' => time(),
    ];

    $trainingtranscriptsdata = $DB->get_records_sql($trainingtranscriptssql, $params);

    $trainingtranscripts = array();

    if (!empty($trainingtranscriptsdata)) {
        foreach ($trainingtranscriptsdata as $trainingtranscript) {
            $courseid = $trainingtranscript->id;

            $hyphen = get_string('hyphen', 'local_batching');

            $traininglink =
                "<a href='" . new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $trainingtranscript->id) . "'
                        title='Go to Training' target='_blank'>" . $trainingtranscript->fullname . "
                   </a>";

            $trainingdetaillink =
                "<a class='training-transcript btn btn-secondary'
                    id='".$trainingtranscript->id."'
                    title='View Training Detail'>" .
                    get_string('viewdetailbutton', 'local_training_transcript') .
                "</a>
                <input type='hidden' id='userid' value = $userid>";

            $course = new stdClass();
            $course->id = $courseid;

            $imageurl = \core_course\external\course_summary_exporter::get_course_image($course);
            if (!$imageurl) {
                $imageurl = $CFG->wwwroot . '/theme/edumy/pix/default-image.jpg';
            }

            if ($trainingtranscript->batching == 1) {
                $coursegroupdetail = get_course_group_detail($courseid, $filters);

                if (!empty($coursegroupdetail)) {
                    $venues = array();
                    $batchnames = array();
                    $startdates = array();
                    $enddatedates = array();
                    $attendance = 0;
                    $individualhours = 0;
                    foreach ($coursegroupdetail as $groupdetail) {
                        $individualhours = $individualhours + date_difference_in_hours($groupdetail->startdate, $groupdetail->enddate);
                        $venues[$groupdetail->venue] = $groupdetail->venue;
                        $userattendances = get_user_attendance_based_on_group($groupdetail->groupid, $courseid);

                        if (!empty($userattendances)) {
                            foreach ($userattendances as $userattendance) {
                                if ($userattendance->acronym == 'P') {
                                    $attendance += 1;
                                }
                            }
                        }

                        $batchname = $groupdetail->batchname;
                        $pos = strpos($batchname, '/');
                        if ($pos !== false) {
                            $batchname = substr($batchname, $pos + 1);
                        }

                        $batchnames[] = $batchname;
                        $startdates[] = customdateformat('DATE_WITHOUT_TIME', $groupdetail->startdate);
                        $enddatedates[] = customdateformat('DATE_WITHOUT_TIME', $groupdetail->enddate);
                    }

                    $transcript = new stdClass();
                    $transcript->trainingid = $trainingtranscript->id;
                    $transcript->trainingname = $trainingtranscript->fullname;
                    $transcript->traininglink = $traininglink;
                    $transcript->trainingimage = $imageurl;
                    $transcript->trainingtype = $trainingtranscript->coursetype;
                    $transcript->trainingstartdate = '<span class="dates_wrapper">'
                                                        . implode('</span>,<span class="inner_dates">', $startdates) .
                                                    '</span>';
                    $transcript->trainingenddate = '<span class="dates_wrapper">'
                                                        . implode('</span>,<span class="inner_dates">', $enddatedates) .
                                                    '</span>';
                    $transcript->trainingnoofdays = $trainingtranscript->days;
                    $transcript->attendingdays = ($attendance > 0) ? $attendance : $hyphen;
                    $transcript->trainingdescription = $trainingtranscript->summary;
                    $transcript->venue = implode(',', $venues);
                    $transcript->cycleandbatch = implode(',', $batchnames);

                    $module = $DB->get_field('modules', 'id', array('name' => 'certificate'));
                    $cm = $DB->get_record('course_modules', array('module' => $module, 'course' => $courseid));

                    $available = check_user_certificate_availability($courseid, $cm->instance, $USER->id);
                    if ($cm) {
                        if ($available == 1) {
                            $transcript->certificate = '<span class="btn btn-success">
                                                            <a href="'.$CFG->wwwroot.'/mod/certificate/view.php?id='.$cm->id.'
                                                                &action=get&userid='.$USER->id.'" target="_blank"
                                                                style="font-size:20px;">
                                                                <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                                            </a>
                                                        </span>';
                        } else {
                            $transcript->certificate = '<span class="">' . get_string('startdateerrormsg', 'local_training_transcript') . '</span>';
                        }
                    } else {
                        $transcript->certificate = '<span class="">' . get_string('certificatemissingerrormsg', 'local_training_transcript') . '</span>';
                    }

                    $transcript->trainindetaillink = $trainingdetaillink;
                    $transcript->traininghours = $individualhours;
                    $trainingtranscripts[] = $transcript;
                }
            } else {
                $userattendances = get_user_attendance_without_group($courseid);

                $attendance = 0;
                if (!empty($userattendances)) {
                    foreach ($userattendances as $userattendance) {
                        if ($userattendance->acronym == 'P') {
                            $attendance += 1;
                        }
                    }
                }

                $individualhours = 0;
                $enddate = $hyphen;
                if ($trainingtranscript->enddate) {
                    $individualhours = date_difference_in_hours($trainingtranscript->startdate, $trainingtranscript->enddate);
                    $enddate = customdateformat('DATE_WITHOUT_TIME', $trainingtranscript->enddate);
                }

                $transcript = new stdClass();
                $transcript->trainingid = $trainingtranscript->id;
                $transcript->trainingname = $trainingtranscript->fullname;
                $transcript->trainingimage = $imageurl;
                $transcript->traininglink = $traininglink;
                $transcript->trainingtype = ($trainingtranscript->coursetype) ?? $hyphen;
                $transcript->trainingstartdate = customdateformat('DATE_WITHOUT_TIME', $trainingtranscript->startdate);
                $transcript->trainingenddate = $enddate;
                $transcript->trainingnoofdays = ($trainingtranscript->days) ?? $hyphen;
                $transcript->attendingdays = ($attendance > 0) ? $attendance : $hyphen;
                $transcript->trainingdescription = $trainingtranscript->summary;
                $transcript->venue = ($trainingtranscript->venue) ?? $hyphen;
                $transcript->cycleandbatch = '-';

                $module = $DB->get_field('modules', 'id', array('name' => 'certificate'));
                $cm = $DB->get_record('course_modules', array('module' => $module, 'course' => $courseid));

                $available = check_user_certificate_availability($courseid, $cm->instance, $USER->id);
                if ($cm) {
                    if ($available == 1) {
                        $transcript->certificate = '<span class="btn btn-success">
                                                        <a href="'.$CFG->wwwroot.'/mod/certificate/view.php?id='.$cm->id.'&
                                                            action=get&userid='.$USER->id.'" target="_blank"
                                                            style="font-size:20px;">
                                                                <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                                        </a>
                                                    </span>';
                    } else {
                        $transcript->certificate = '<span class="">' . get_string('courseactivitiesnotcompletederrormsg', 'local_training_transcript') . '</span>';
                    }
                } else {
                    $transcript->certificate = '<span class="">' . get_string('certificatemissingerrormsg', 'local_training_transcript') . '</span>';
                }

                $transcript->trainindetaillink = $trainingdetaillink;
                $transcript->traininghours = $individualhours;
                $trainingtranscripts[] = $transcript;
            }
        }
    }

    return $trainingtranscripts;
}

function get_course_group_detail($courseid, $filters) {
    global $DB, $USER;

    $systemcontext = \context_system::instance();
    $userid = $USER->id;
    if (has_capability('local/training_transcript:viewall', $systemcontext) &&
    isset($filters->user) &&
    !empty($filters->user)
    ) {
        $userid = $filters->user;
    }

    $coursegroupdetailssql = '
            SELECT lgd.*, ls.name venue, ls.description address, g.name batchname,
            gs.name cyclename
            FROM {local_group_details} lgd
            JOIN {local_schools} ls ON ls.id = lgd.venue
            JOIN {groups} g ON g.id = lgd.groupid
            JOIN {groups_members} gm ON gm.groupid = g.id
            JOIN {groupings_groups} gg ON gg.groupid = g.id
            JOIN {groupings} gs ON gs.id = gg.groupingid
            WHERE g.courseid = :courseid AND gm.userid = :userid
            AND lgd.enddate < :enddate
            ';

    if ($filters->startdate &&
        $filters->enddate &&
        $filters->startdate_enabled &&
        $filters->enddate_enabled
    ) {
        $coursegroupdetailssql .= ' AND ( lgd.startdate BETWEEN  ' . $filters->startdate . ' AND  ' . $filters->enddate . ' ) ';
    }
    $params = [
        'courseid' => $courseid,
        'userid' => $userid,
        'enddate' => time()
    ];

    return  $DB->get_records_sql($coursegroupdetailssql, $params);
}

function get_user_attendance_based_on_group($groupid, $courseid) {
    global $DB, $USER;

    $userattnedancessql = '
            SELECT * FROM {attendance_log} attl
            JOIN {attendance_sessions} atts ON atts.id = attl.sessionid
            JOIN {attendance_statuses} attst ON attst.id = attl.statusid
            JOIN {attendance} att ON att.id = atts.attendanceid
            JOIN {groups} g ON g.id = atts.groupid
            WHERE attl.studentid = :userid
            AND att.course = :courseid AND atts.groupid = :groupid
            ';

    $params = [
        'courseid' => $courseid,
        'userid' => $USER->id,
        'groupid' => $groupid
    ];

    return  $DB->get_records_sql($userattnedancessql, $params);

}

function get_user_attendance_without_group($courseid) {
    global $DB, $USER;

    $userattnedancessql = '
            SELECT * FROM {attendance_log} attl
            JOIN {attendance_sessions} atts ON atts.id = attl.sessionid
            JOIN {attendance_statuses} attst ON attst.id = attl.statusid
            JOIN {attendance} att ON att.id = atts.attendanceid
            WHERE attl.studentid = :userid
            AND att.course = :courseid
            ';

    $params = [
        'courseid' => $courseid,
        'userid' => $USER->id,
    ];

    return  $DB->get_records_sql($userattnedancessql, $params);
}

function check_user_certificate_availability($courseid, $certificateid, $userid) {
    global $DB;
    $paricipantcertificatesql = "SELECT certi.* FROM {certificate_issues} AS certi
                                JOIN {certificate} AS cert ON cert.id = certi.certificateid
                                WHERE cert.course = :courseid AND cert.id = :certificateid AND certi.userid = :userid";

    $certificateparams = [
                            'courseid' => $courseid,
                            'certificateid' => $certificateid,
                            'userid' => $userid
                        ];

    $certificaterecord = $DB->get_record_sql($paricipantcertificatesql, $certificateparams);

    $available = 0;
    if (!empty($certificaterecord->certificateid)) {
        $certavailability = "SELECT cm.availability FROM {course_modules} AS cm
                                JOIN {modules} AS m ON m.id = cm.module
                                WHERE cm.course = :courseid AND instance = :certificateid AND m.name = 'certificate'";

        $availabiltyparams = [
                                'courseid' => $courseid,
                                'certificateid' => $certificaterecord->certificateid
                            ];

        $cmrecord = $DB->get_field_sql($certavailability, $availabiltyparams);

        $usercompletiondata = json_decode($cmrecord);

        $activitycompletions = $usercompletiondata->c;
        $cmcompleted = array();

        foreach ($activitycompletions as $activitycompletion) {
            $dependentcm = $activitycompletion->cm;
            $cmcompleted[] = $DB->record_exists('course_modules_completion',
                                        array('coursemoduleid' => $dependentcm, 'userid' => $participant->id,
                                                'completionstate' => 1));
        }

        if ($usercompletiondata->op == '&') {
            if (in_array('0', $cmcompleted)) {
                $available = 0;
            } else {
                $available = 1;
            }
        } else if ($usercompletiondata->op == '|') {
            if (in_array('1', $cmcompleted)) {
                $available = 1;
            }
        }

    }
    return $available;
}

function date_difference_in_hours($starttimestamp, $endtimestamp) {
    $difference = round(($endtimestamp - $starttimestamp)/3600, 1);
    return $difference;
}

function get_all_users() {
    global $DB;

    $userssql = '
            SELECT id, firstname, lastname FROM {user} 
            WHERE firstname != "" AND confirmed = 1
            AND deleted = 0                        
            ORDER BY firstname
            ';

    return  $DB->get_records_sql($userssql);
}
