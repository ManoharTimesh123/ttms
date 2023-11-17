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
 * The batching Management
 *
 * @package    local_batching
 * @author     Tarun Upadhyay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */


function delete_batching($id) {
    global $DB;
    return $DB->delete_records('local_batching', array('id' => $id));
}

/*
 * Get batching dashborad data.
 *
 */
function get_batchings($id = null, $status = []) {
    global $DB, $USER;

    $systemcontext = context_system::instance();

    $params = [];
    $batchingsql = <<<SQL
                    SELECT b.id as batchingid, b.*, c.*, lcd.modality FROM {local_batching} b
                     INNER JOIN {course} c ON c.id = b.course
                     JOIN {local_course_details} lcd ON lcd.course = b.course
                    SQL;

    if ($id) {
        $batchingsql .= <<<SQL
                         AND b.id = $id
                        SQL;
    }

    if (!empty($status)) {
        $implodedstatus = "'" . implode("', '", $status) . "'";

        $batchingsql .= <<<SQL
                         AND b.status IN ($implodedstatus)
                        SQL;
    }

    if (!is_siteadmin() && has_capability('local/batching:perform', $systemcontext)) {
        $batchingsql .= <<<SQL
                         AND (b.nodal_officers IN ($USER->id) OR b.diet_heads IN ($USER->id))
                        SQL;
    }

    $batchings = $DB->get_records_sql($batchingsql, $params);

    foreach ($batchings as $index => $batching) {
        $batchingid = $batching->batchingid;

        $batchingmodality = <<<SQL
                    SELECT * FROM {local_modality}
                    WHERE id = ?
                    SQL;

        $batchingmodality = $DB->get_record_sql($batchingmodality, array($batching->modality));
        $batchings[$index]->modalitydetails = $batchingmodality;

        $batchingnodalofficers = array();
        if ($batching->nodal_officers) {
            $batchingnodalofficerssql = <<<SQL
                    SELECT id, concat(firstname,' ',lastname) name FROM {user}
                    WHERE id IN ($batching->nodal_officers)
                    SQL;

            $batchingnodalofficers = $DB->get_records_sql_menu($batchingnodalofficerssql);
        }
        $batchings[$index]->nodalofficers = $batchingnodalofficers;

        $batchingdietheads = array();
        if ($batching->diet_heads) {
            $batchingdietheadssql = <<<SQL
                    SELECT id, concat(firstname,' ',lastname) name FROM {user}
                    WHERE id IN ($batching->diet_heads)
                    SQL;

            $batchingdietheads = $DB->get_records_sql_menu($batchingdietheadssql);
        }

        $batchings[$index]->dietheads = $batchingdietheads;

        $batchingcyclessql = <<<SQL
                    SELECT * FROM {local_batching_cycles}
                    WHERE batching = ?
                    SQL;

        $batchingcycles = $DB->get_records_sql($batchingcyclessql, array($batchingid));
        $batchings[$index]->cycles = $batchingcycles;

        $totalbatches = 0;

        foreach ($batchings[$index]->cycles as $cycleindex => $cycle) {

            $cycleid = $cycle->id;

            $totalbatches += $DB->count_records('local_batching_batches', ['cycle' => $cycleid]);

            $batchingbatchessql = <<<SQL
                    SELECT * FROM {local_batching_batches}
                    WHERE cycle = ?
                    SQL;

            $batchingbatches = $DB->get_records_sql($batchingbatchessql, array($cycleid));
            $batchings[$index]->cycles[$cycleindex]->batches = $batchingbatches;
        }

        $batchings[$index]->totalbatches = $totalbatches;
    }

    return $batchings;
}

/*
 * Get filters for a particular batching
 *
 */
function get_filters($id = null) {
    global $DB;

    $filters = $DB->get_records('local_batching_filters', ['batching' => $id]);

    $data = [];
    foreach ($filters as $index => $filter) {
        $data[$filter->name] = $filter->value;
    }

    return $data;
}

/*
 * Get venues based on the filters applied in the batching
 *
 */
function get_venues_based_on_filters($id = null) {
    global $DB;

    $venuesql = '
                SELECT s.*, dis.name as districtname FROM {local_schools} s
                INNER JOIN {local_zones} z ON z.id = s.zone_id
                INNER JOIN {local_diets} d ON d.id = z.diet
                INNER JOIN {local_districts} dis ON dis.id = d.district_id
                ';

    $venuesql .= ' WHERE s.isvenue = 1';

    $filters = get_filters($id);

    if (!empty($filters)) {
        foreach ($filters as $index => $filter) {
            if ($index == 'zones' && $filter != null) {
                $venuesql .= ' AND z.id IN (' . $filter . ')';
            }
            if ($index == 'diets' && $filter != null) {
                $venuesql .= ' AND d.id IN (' . $filter . ')';
            }
            if ($index == 'participantsperbatch' && $filter != null) {
                $venuesql .= ' AND s.venue_capacity >= ' . $filter;
            }
        }
    }

    $data = $DB->get_records_sql($venuesql);

    return $data;
}

/*
 * Get venues based on batching
 *
 */
function get_batching_venues($id = null) {
    global $DB;

    $venuesql = '
                SELECT s.*, dis.name as districtname, v.status as venuestatus FROM {local_schools} s
                INNER JOIN {local_zones} z ON z.id = s.zone_id
                INNER JOIN {local_diets} d ON d.id = z.diet
                INNER JOIN {local_districts} dis ON dis.id = d.district_id
                INNER JOIN {local_batching_venue} v ON v.school = s.id
                ';

    $venuesql .= ' WHERE s.isvenue = 1 AND batching = ' . $id;

    $data = $DB->get_records_sql($venuesql);

    return $data;
}

/*
 * Get participants based on the filters applied in the batching and school
 *
 */
function get_participants_based_on_filters($id = null, $school = null, $getall = null, $limit = null) {
    global $DB;

    $usersql = '
                SELECT u.*, s.id as schoolid FROM {user} u
                INNER JOIN {local_user_details} ud ON ud.userid = u.id
                INNER JOIN {local_schools} s ON s.id = ud.schoolid
                INNER JOIN {local_zones} z ON z.id = s.zone_id
                INNER JOIN {local_diets} d ON d.id = z.diet
                INNER JOIN {local_districts} dis ON dis.id = d.district_id
                LEFT JOIN {local_batching_participants} bp ON bp.user = u.id
                ';

    $usersql .= ' WHERE u.confirmed = 1 AND u.deleted = 0';

    if ($getall == null) {
        $usersql .= ' AND u.id NOT IN (SELECT user FROM lms_local_batching_participants WHERE batching = ' . $id . ')';
    }

    if ($school) {
        $usersql .= ' AND ud.schoolid = ' . $school;
    }

    $filters = get_filters($id);

    if (!empty($filters)) {
        foreach ($filters as $index => $filter) {
            if ($index == 'subjects' && $filter != null) {
                $usersql .= ' AND ud.subject IN (' . $filter . ')';
            }
            if ($index == 'position' && $filter != null) {
                $usersql .= ' AND ud.position IN (' . $filter . ')';
            }
            if ($index == 'grades' && $filter != null) {
                $schoolsql .= ' AND ud.grade IN (' . $filter . ')';
            }
            if ($index == 'posts' && $filter != null) {
                $schoolsql .= ' AND ud.post IN (' . $filter . ')';
            }
            if ($index == 'zones' && $filter != null) {
                $usersql .= ' AND z.id IN (' . $filter . ')';
            }
            if ($index == 'diets' && $filter != null) {
                $usersql .= ' AND d.id IN (' . $filter . ')';
            }
        }

        // Filter check for DOJ.
        if (array_key_exists('dojstartdate_enabled',  $filters) || array_key_exists('dojenddate_enabled',  $filters)) {

            if (
                array_key_exists('dojstartdate_enabled',  $filters) &&
                $filters['dojstartdate_enabled'] == 1
            ) {
                $dojstartset = true;
                $dojstartdate = $filters['dojstartdate'];
            } else {
                $dojstartset = false;
                // Default to 1900.
                $dojstartdate = strtotime('1/1/1900');
            }

            if (
                array_key_exists('dojenddate_enabled',  $filters) &&
                $filters['dojenddate_enabled'] == 1
            ) {
                $dojendset = true;
                $dojenddate = $filters['dojenddate'];
            } else {
                $dojendset = false;
                $dojenddate = time();
            }

            if ($dojstartset || $dojendset) {
                $usersql .= " AND doj >= " . $dojstartdate;
                $usersql .= " AND doj <= " . $dojenddate;
            }
        }
    }

    $usersql .= ' GROUP BY u.id';

    if ($limit) {
        $usersql .= ' LIMIT ' . $limit;
    }

    $data = $DB->get_records_sql($usersql);

    return $data;
}


/*
 * Get schools based on the filters applied in the batching
 *
 */
function get_schools_based_on_filters($id = null) {
    global $DB;

    $schoolsql = '
                SELECT s.* FROM {user} u
                INNER JOIN {local_user_details} ud ON ud.userid = u.id
                INNER JOIN {local_schools} s ON s.id = ud.schoolid
                INNER JOIN {local_zones} z ON z.id = s.zone_id
                INNER JOIN {local_diets} d ON d.id = z.diet
                INNER JOIN {local_districts} dis ON dis.id = d.district_id
                LEFT JOIN {local_batching_participants} bp ON bp.user = u.id
                ';

    $schoolsql .= ' WHERE u.confirmed = 1 AND u.deleted = 0';

    $filters = get_filters($id);

    if (!empty($filters)) {
        foreach ($filters as $index => $filter) {
            if ($index == 'subjects' && $filter != null) {
                $schoolsql .= ' AND ud.subject IN (' . $filter . ')';
            }
            if ($index == 'position' && $filter != null) {
                $schoolsql .= ' AND ud.position IN (' . $filter . ')';
            }
            if ($index == 'grades' && $filter != null) {
                $schoolsql .= ' AND ud.grade IN (' . $filter . ')';
            }
            if ($index == 'posts' && $filter != null) {
                $schoolsql .= ' AND ud.post IN (' . $filter . ')';
            }
            if ($index == 'zones' && $filter != null) {
                $schoolsql .= ' AND z.id IN (' . $filter . ')';
            }
            if ($index == 'diets' && $filter != null) {
                $schoolsql .= ' AND d.id IN (' . $filter . ')';
            }
        }

        // Filter check for DOJ.
        if (array_key_exists('dojstartdate_enabled',  $filters) || array_key_exists('dojenddate_enabled',  $filters)) {

            if (
                array_key_exists('dojstartdate_enabled',  $filters) &&
                $filters['dojstartdate_enabled'] == 1
            ) {
                $dojstartset = true;
                $dojstartdate = $filters['dojstartdate'];
            } else {
                $dojstartset = false;
                // Default to 1900.
                $dojstartdate = strtotime('1/1/1900');
            }

            if (
                array_key_exists('dojenddate_enabled',  $filters) &&
                $filters['dojenddate_enabled'] == 1
            ) {
                $dojendset = true;
                $dojenddate = $filters['dojenddate'];
            } else {
                $dojendset = false;
                $dojenddate = time();
            }

            if ($dojstartset || $dojendset) {
                $schoolsql .= " AND doj >= " . $dojstartdate;
                $schoolsql .= " AND doj <= " . $dojenddate;
            }
        }
    }

    $schoolsql .= ' GROUP BY s.id';

    $data = $DB->get_records_sql($schoolsql);

    return $data;
}

/*
 * Get cycles based on the filters applied in the batching
 *
 */
function get_cycles_based_on_filters($id = null) {
    global $DB;

    $schools = get_schools_based_on_filters($id);
    $filters = get_filters($id);

    $totalparticipantsofschoolsasperpercentage = 0;
    $totalparticipantsofschoolswithoutpercentage = 0;

    foreach ($schools as $index => $school) {

        $participants = get_participants_based_on_filters($id, $school->id, 'all');
        $schooltotalparticipants = count($participants);

        if (array_key_exists('percentage', $filters)) {
            $percentage = $filters['percentage'];
        } else {
            $percentage = 100;
        }

        $schoolparticipantstopick = ($schooltotalparticipants * $percentage) / 100;

        $totalparticipantsofschoolsasperpercentage = $totalparticipantsofschoolsasperpercentage + $schoolparticipantstopick;
        $totalparticipantsofschoolswithoutpercentage = $totalparticipantsofschoolswithoutpercentage + $schooltotalparticipants;
    }

    $totalparticipantsofschoolsasperpercentage = ceil($totalparticipantsofschoolsasperpercentage);

    $totalcycles = $totalparticipantsofschoolswithoutpercentage / $totalparticipantsofschoolsasperpercentage;

    $totalcycles = (int) ceil($totalcycles);

    return [
        'totalcycles' => $totalcycles,
        'totalschools' => count($schools),
        'totalparticipantsofschoolswithoutpercentage' => $totalparticipantsofschoolswithoutpercentage,
        'totalparticipantsofschoolsasperpercentage' => $totalparticipantsofschoolsasperpercentage,
    ];
}


/*
 * Get batches based on the filters applied in the batching
 *
 */
function get_batches_based_on_filters($id = null) {
    global $DB;

    $cycles = get_cycles_based_on_filters($id);

    $filters = get_filters($id);

    $totalparticipantsofschoolsasperpercentage = $cycles['totalparticipantsofschoolsasperpercentage'];

    if (array_key_exists('participantsperbatch', $filters)) {
        $participantsperbatch = $filters['participantsperbatch'];
    } else {
        $warningmsg = get_string('warningmsg', 'local_batching');
        die($warningmsg);
    }

    if ($totalparticipantsofschoolsasperpercentage < $participantsperbatch) {
        $participantsavailableperbatch = $totalparticipantsofschoolsasperpercentage;
    } else {
        $participantsavailableperbatch = $participantsperbatch;
    }

    $totalbatches = $totalparticipantsofschoolsasperpercentage / $participantsperbatch;

    $totalbatches = ceil($totalbatches);

    $totalparticipantsdiff = $totalparticipantsofschoolsasperpercentage % $participantsperbatch;

    // If there is some number of particiants left in the batch.
    if ($totalparticipantsdiff > 0) {

        $participantsperbatchadjusted = $cycles['totalparticipantsofschoolswithoutpercentage'] / ($totalbatches * $cycles['totalcycles']);
        $participantsperbatchadjusted = ceil($participantsperbatchadjusted);

        // Add this to filter.
        $filterobject = new stdClass();
        $filterobject->id = $id;
        $filterobject->participantsperbatchadjusted = $participantsperbatchadjusted;
        add_update_filter_data($filterobject);
    }

    return [
        'totalbatches' => $totalbatches,
        'cycles' => $cycles,
        'participantsavailableperbatch' => $participantsavailableperbatch,
        'participantsperbatch' => $participantsperbatch,
        'participantsperbatchadjusted' => $participantsperbatchadjusted,
    ];
}



/*
 * Get participants from temp table
 *
 */
function get_participants_from_temp($id = null, $school = null, $getall = null, $limit = null) {
    global $DB;

    $usersql = '
                SELECT tu.* FROM {local_batching_temp_users} tu
                ';

    $usersql .= ' WHERE tu.batching = ' . $id;

    if ($school) {
        $usersql .= ' AND tu.school = ' . $school;
    }

    // Remove users already batched.
    if ($getall == null) {
        $usersql .= ' AND tu.user NOT IN (SELECT user from lms_local_batching_participants bp1 where batching = ' . $id . ' AND bp1.user = tu.user)';
    }

    if ($limit) {
        $usersql .= ' LIMIT ' . $limit;
    }

    $data = $DB->get_records_sql($usersql);

    return $data;
}

/*
 * Distribute Participants based on filters.
 *
 */
function distribute_participants_based_on_filters($id = null) {
    global $DB, $USER, $CFG;

    // Delete already added participants.
    $DB->delete_records('local_batching_participants', ['batching' => $id]);
    $DB->delete_records('local_batching_temp_users', ['batching' => $id]);

    $cycles = get_cycle_by_batching($id);
    $schools = get_schools_based_on_filters($id);
    $filters = get_filters($id);

    if (array_key_exists('participantsperbatchadjusted', $filters)) {
        $participantsperbatch = $filters['participantsperbatchadjusted'];
    } else if (array_key_exists('participantsperbatch', $filters)) {
        $participantsperbatch = $filters['participantsperbatch'];
    } else {
        $participantsperbatch = 50;
    }

    foreach ($schools as $school) {
        $participants = get_participants_based_on_filters($id, $school->id, 'all');

        $tempuser = new stdClass();
        foreach ($participants as $participant) {

            $tempuser->batching = $id;
            $tempuser->school = $participant->schoolid;
            $tempuser->user = $participant->id;

            if (!$DB->record_exists('local_batching_temp_users', array('batching' => $id, 'user' => $participant->id))) {
                $DB->insert_record('local_batching_temp_users', $tempuser);
            }
        }
    }

    $cyclecount = 0;
    $lastcycle = false;
    foreach ($cycles as $c => $cycle) {
        $batches = get_batch_by_batching_and_cycle($id, $cycle->id);

        if (++$cyclecount === count($cycles)) {
            $lastcycle = true;
        }

        $batchcount = 0;
        $lastbatch = false;
        foreach ($batches as $b => $batch) {

            if (++$batchcount === count($batches)) {
                $lastbatch = true;
            }

            $participantscountinbatch = 0;

            $sql = 'SELECT school, count(id) as total from {local_batching_temp_users} where batching = ' . $id;
            $sql .= ' GROUP BY school ORDER BY total';

            $schools = $DB->get_records_sql($sql);

            $schooldistribution = [
                'schools' => $schools,
                'lastcycle' => $lastcycle,
                'lastbatch' => $lastbatch,
                'batchesincycle' => count($batches),
                'cycle' => $cycle,
                'batch' => $batch,
            ];

            $iteration = 0;

            if ($lastcycle && $lastbatch) {
                distribute_from_school($id, $schooldistribution, $participantscountinbatch, $iteration);
            } else {
                while ($participantscountinbatch < $participantsperbatch) {
                    if ($iteration > 20) {
                        $url = new moodle_url($CFG->wwwroot.'/local/batching/venues.php', array('id' => $id));
                        redirect($url, 'Something looks wrong. Please contact admin.', null, \core\output\notification::NOTIFY_ERROR);
                    }
                    $iteration++;
                    $participantscountinbatch = distribute_from_school($id, $schooldistribution,
                    $participantscountinbatch, $iteration);
                }
            }
        }
    }
}


function distribute_from_school($id, $schooldistribution, $participantscountinbatch, $iteration) {

    global $DB, $USER;

    $schools = $schooldistribution['schools'];
    $participantscountinbatch = $participantscountinbatch;
    $lastcycle = $schooldistribution['lastcycle'];
    $lastbatch = $schooldistribution['lastbatch'];
    $batchesincycle = $schooldistribution['batchesincycle'];
    $batch = $schooldistribution['batch'];
    $cycle = $schooldistribution['cycle'];

    $filters = get_filters($id);
    $lastbatchinsert = false;

    if (array_key_exists('participantsperbatchadjusted', $filters)) {
        $participantsperbatch = $filters['participantsperbatchadjusted'];
    } else if (array_key_exists('participantsperbatch', $filters)) {
        $participantsperbatch = $filters['participantsperbatch'];
    } else {
        $participantsperbatch = 50;
    }

    if (array_key_exists('percentage', $filters)) {
        $percentage = $filters['percentage'];
    } else {
        $percentage = 100;
    }

    foreach ($schools as $index => $school) {

        $participants = get_participants_from_temp($id, $school->school, 'all');

        $schooltotalparticipants = count($participants);

        $schoolparticipantslimit = ($schooltotalparticipants * $percentage) / 100;
        $schoolparticipantslimit = ceil($schoolparticipantslimit / $batchesincycle);

        $participantscountinbatch = $participantscountinbatch + $schoolparticipantslimit;

        // If the participants in batch have exceeded the batch limit.
        if ($participantscountinbatch > $participantsperbatch) {

            // Get the extra participants.
            $extrausers = $participantscountinbatch - $participantsperbatch;

            // Get less participants by removing extra participants from the batch.
            $schoolparticipantslimit = ($schoolparticipantslimit - $extrausers);

            $lastbatchinsert = true;
        }

        // If the participants in batch have reached the exact batch limit.
        if ($participantscountinbatch == $participantsperbatch) {
            $lastbatchinsert = true;
        }

        if ($lastcycle && $lastbatch) {
            $schoolparticipantslimit = 500;
        }

        if ($schoolparticipantslimit > 0) {
            $participants = get_participants_from_temp($id, $school->school, null, $schoolparticipantslimit);
        } else {
            $participants = [];
        }

        $participantscountinbatch = $participantscountinbatch - ($schoolparticipantslimit - count($participants));

        if ($participantscountinbatch < $participantsperbatch) {
            $lastbatchinsert = false;
        }

        foreach ($participants as $participant) {

            $users = new stdClass();
            $users->batching = $id;
            $users->user = $participant->user;
            $users->batch = $batch->id;
            $users->timecreated = time();
            $users->usercreated = $USER->id;

            $DB->insert_record('local_batching_participants', $users);
        }

        // Break the loop inside the batch if its a last insert in batch.
        if ($lastbatchinsert) {
            break;
        }
    }

    return $participantscountinbatch;
}


/*
 * This function is to a tentative training by AD Admin.
 *
 */
function create_batching_course($data) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot . '/local/course_management/lib.php');
    $coursedata = new stdClass();
    $coursedata->fullname = strip_tags($data->fullname);
    $coursedata->shortname = strip_tags($data->shortname);
    $coursedata->startdate = $data->startdate;
    $coursedata->enddate = $data->enddate;
    $coursedata->summary = $data->summary['text'];
    $coursedata->summaryformat = $data->summary['format'];
    $coursedata->enablecompletion = 1;
    $coursedata->showactivitydates = 1;
    $coursedata->overviewfiles_filemanager = $data->trainingimage;
    $coursedata->showcompletionconditions = 1;
    $coursedata->format = 'onetopic';

    if ($modality = $DB->get_record('local_modality', ['shortname' => $data->modality])) {
        $coursecategory = get_course_category_by_idnumber();
        if (empty($coursecategory->id)) {
            $defaultcoursecategory = $DB->get_field('course_categories', 'id', array());
            $coursecategory->id = $defaultcoursecategory;
        }

        $coursedata->timemodified = time();
        $coursedata->updatedby = $USER->id;
        $coursedata->category = $coursecategory->id;

        if ($course = create_course($coursedata)) {
            $courseid = $course->id;

            // Enabled manual enrollment in course by adding course id in enrol table.
            enable_manual_enrollment_in_course('manual', $courseid);

            $batchingdata = new stdClass();
            $batchingdata->name = strip_tags($data->fullname);
            $batchingdata->course = $courseid;
            $batchingdata->nodal_officers = implode(',', $data->nodalofficers);
            $batchingdata->diet_heads = implode(',', $data->dietheads);
            $batchingdata->status = 'proposed';
            $batchingdata->timecreated = time();
            $batchingdata->createdby = $USER->id;

            $coursedetail = new stdClass();
            $coursedetail->course = $courseid;
            $coursedetail->modality = $modality->id;
            $coursedetail->coursetype = 0;
            $coursedetail->venue = 0;
            $coursedetail->batching = 1;
            $coursedetail->enablegrouping = 1;
            $coursedetail->days = 0;
            $coursedetail->certificatetemplate = $data->certificatetemplate;
            save_course_customdetails($coursedetail);

            $DB->insert_record('local_batching', $batchingdata);
        }
    }

    return $courseid;
}


/*
 * This function is to update the course details if anyone makes changes to the course details.
 *
 */
function update_batching_course($data) {
    global $DB, $USER;

    $batching = get_batchings($data->id)[$data->id];

    $profdata = new stdClass();
    $profdata->fullname = strip_tags($data->fullname);
    $profdata->startdate = $data->startdate;
    $profdata->enddate = $data->enddate;
    $profdata->summary = $data->summary['text'];
    $profdata->summaryformat = $data->summary['format'];

    if ($data->id > 0 && $batching->course > 0) {
        $course = $DB->get_record('course', array('id' => $batching->course));
        $profdata->id = $course->id;
        $profdata->timemodified = time();
        $profdata->updatedby = $USER->id;

        $dataid = $DB->update_record('course', $profdata);
    }

    return $dataid;
}

/*
 * This function is to update the course details if anyone makes changes to the course details.
 *
 */
function add_update_filter_data($data) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $currenttimestamp = time();

    if ($data->id > 0) {

        $batchingid = $data->id;

        foreach ($data as $index => $filterdata) {
            if ($index != 'id' && $index != 'submitbutton') {

                if (is_array($filterdata)) {
                    $filterdata = implode(',', $filterdata);
                }

                $profdata = new stdClass();
                $profdata->batching = $batchingid;
                $profdata->name = $index;
                $profdata->value = $filterdata;

                $conditions = [
                    'batching' => $batchingid,
                    'name' => $index,
                ];

                if ($recordid = $DB->get_record('local_batching_filters', $conditions)->id) {
                    $profdata->id = $recordid;
                    $profdata->timemodified = $currenttimestamp;
                    $profdata->usermodified = $loggedinuserid;
                    $dataid = $DB->update_record('local_batching_filters', $profdata);
                } else {
                    $profdata->timecreated = $currenttimestamp;
                    $profdata->timemodified = $currenttimestamp;
                    $profdata->usercreated = $loggedinuserid;
                    $profdata->usermodified = $loggedinuserid;
                    $dataid = $DB->insert_record('local_batching_filters', $profdata);
                }
            }
        }
    }

    return $dataid;
}


/*
 * This function is to update the venue details.
 *
 */
function add_update_venue_data($data) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $currenttimestamp = time();

    if ($data->id > 0) {
        $batchingid = $data->id;
        $sendforapproval = $data->sendforapproval;

        if ($data->id > 0 && !empty($sendforapproval)) {

            $dataid = $DB->delete_records('local_batching_venue', ['batching' => $batchingid, 'status' => null]);

            foreach ($sendforapproval as $school => $venues) {

                $exists = $DB->record_exists('local_batching_venue', ['batching' => $batchingid, 'school' => $school]);

                if ($venues && !$exists) {
                    $profdata = new stdClass();
                    $profdata->batching = $batchingid;
                    $profdata->school = $school;
                    $profdata->timecreated = $currenttimestamp;
                    $profdata->timemodified = $currenttimestamp;
                    $profdata->usercreated = $loggedinuserid;
                    $profdata->usermodified = $loggedinuserid;
                    // FIXME: This needs to be 'Pending Approval'. Just did this for Demo.
                    $profdata->status = 'Approved';
                    $dataid = $DB->insert_record('local_batching_venue', $profdata);
                }
            }
        }
    }

    return $dataid;
}

/*
 * This function is to get the cycles by batching id.
 *
 */
function get_cycles_by_baching_id($id) {
    global $DB, $USER;

    if ($id > 0) {
        $data = $DB->get_records('local_batching_cycles', ['batching' => $id]);
    }

    return $data;
}

/*
 * This function is to get the batches by batching id.
 *
 */
function get_batches_by_baching_id($id) {
    global $DB, $USER;

    if ($id > 0) {
        $data = $DB->get_records('local_batching_batches', ['batching' => $id]);
    }

    return $data;
}

/*
 * This function is to get approved venues.
 *
 */
function get_approved_venues($id) {
    global $DB;
    $approvedvenuessql = <<<SQL
                    SELECT bv.id, s.name school FROM {local_batching_venue} bv
                    LEFT JOIN {local_schools} s ON s.id = bv.school
                     WHERE bv.batching = :batching
                    AND bv.status = 'Approved'
                    ORDER BY bv.id DESC
                    SQL;
    $param = [
        'batching' => $id
    ];
    $approvedvenues = $DB->get_records_sql($approvedvenuessql, $param);
    $venues = array();
    if (!empty($approvedvenues)) {
        foreach ($approvedvenues as $venue) {
            $venues[$venue->id] = $venue->school;
        }
        return $venues;
    }
    return $venues;
}

/*
 * This function is used to distribute user based on batching and cycling
 *
 */
function distribute_user_based_batching_and_cycling($data) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $timestamp = time();

    foreach ($data->starttime as $cycle => $venuestarttime) {
        foreach ($venuestarttime as $cycletimeid => $starttime) {
            update_batching_cycle_time($cycletimeid, 'starttime', $starttime);
        }
    }
    foreach ($data->endtime as $cycle => $venueendtime) {
        foreach ($venueendtime as $cycletimeid => $endtime) {
            update_batching_cycle_time($cycletimeid, 'endtime', $endtime);
        }
    }
    foreach ($data->venue as $batching => $cycles) {
        foreach ($cycles as $cycle => $batches) {
            foreach ($batches as $batch => $venues) {
                delete_batching_venue($batch);
                if (!empty($venues)) {
                    foreach ($venues as $venue) {
                        add_venue_in_batching($venue,  $batch);
                    }
                }
            }
        }
    }
    foreach ($data->facilitator as $batching => $cycles) {
        foreach ($cycles as $cycle => $batches) {
            foreach ($batches as $batch => $facilitators) {
                delete_batching_batch_user('facilitators', $batching, $batch);
                if (!empty($facilitators)) {
                    foreach ($facilitators as $facilitator) {
                        add_user_in_batching('facilitators', $batching, $facilitator, $batch);
                    }
                }
            }
        }
    }
    foreach ($data->coordinator as $batching => $cycles) {
        foreach ($cycles as $cycle => $batches) {
            foreach ($batches as $batch => $coordinators) {
                delete_batching_batch_user('coordinators', $batching, $batch);
                if (!empty($coordinators)) {
                    foreach ($coordinators as $coordinator) {
                        add_user_in_batching('coordinators', $batching, $coordinator, $batch);
                    }
                }
            }
        }
    }
    foreach ($data->observer as $batching => $cycles) {
        foreach ($cycles as $cycle => $batches) {
            foreach ($batches as $batch => $observers) {
                delete_batching_batch_user('observers', $batching, $batch);
                if (!empty($observers)) {
                    foreach ($observers as $observer) {
                        add_user_in_batching('observers', $batching, $observer, $batch);
                    }
                }
            }
        }
    }
}

function add_venue_in_batching($venueid, $batch) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $timestamp = time();
    $batchingvenuedata = new stdClass();
    $batchingvenuedata->batchingvenueid = $venueid;
    $batchingvenuedata->batch = $batch;
    $batchingvenuedata->timecreated = $timestamp;
    $batchingvenuedata->timemodified = $timestamp;
    $batchingvenuedata->usercreated = $loggedinuserid;
    $batchingvenuedata->usermodified = $loggedinuserid;
    $DB->insert_record('local_batching_venue_final', $batchingvenuedata);
}

function add_user_in_batching($tablename, $batching, $userid, $batchid) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $timestamp = time();
    $tablename = 'local_batching_'.$tablename;
    $batchinguseradded = $DB->record_exists($tablename, array('batching' => $batching, 'user' => $userid, 'batch' => $batchid));
    if (!$batchinguseradded) {
        $batchingdata = new stdClass();
        $batchingdata->batching = $batching;
        $batchingdata->user = $userid;
        $batchingdata->batch = $batchid;
        $batchingdata->timecreated = $timestamp;
        $batchingdata->timemodified = $timestamp;
        $batchingdata->usercreated = $loggedinuserid;
        $batchingdata->usermodified = $loggedinuserid;
        $DB->insert_record($tablename, $batchingdata);
    }
}

function get_users_in_batching($tablename, $batchingid, $cycle, $batchid) {
    global $DB;

    $batchinguserssql = <<<SQL
                    SELECT bu.user FROM {local_batching_$tablename} bu
                    LEFT JOIN {local_batching_batches} bb ON bb.id = bu.batch
                    WHERE bu.batching = :batching
                    AND bu.batch = :batch
                    AND bb.cycle = :cycle
                    SQL;
    $params = [
        'batching' => $batchingid,
        'cycle' => $cycle,
        'batch' => $batchid,
    ];
    $batchingusers = $DB->get_records_sql($batchinguserssql, $params);

    $batchingusersarray = array();
    if ($batchingusers) {
        foreach ($batchingusers as $user) {
            $batchingusersarray[$user->user] = $user->user;
        }
    }
    return $batchingusersarray;
}

function get_venue_in_batching($batchid) {
    global $DB;

    $venueinbatching = $DB->get_records('local_batching_venue_final', array('batch' => $batchid));
    $venueinbatchingarray = array();
    if (!empty($venueinbatching)) {
        foreach ($venueinbatching as $venue) {
            $venueinbatchingarray[] = $venue->batchingvenueid;
        }
    }
    return $venueinbatchingarray;
}

function get_batching_training_days($batching) {

    $filters = get_filters($batching);
    if (!empty($filters)) {
        foreach ($filters as $index => $filter) {
            if ($index == 'trainingnoofdays' && $filter != null) {
                $trainingnoofdays = $filter;
            }
        }
    }

    return $trainingnoofdays;

}

function get_cycle_times($cycle) {
    global $DB;

    $params = [
        'cycle' => $cycle,
    ];

    $cycletimes = $DB->get_records('local_batching_cycle_times', $params);

    return $cycletimes;

}

function get_cycle_times_for_the_day($cycle, $day) {
    global $DB;

    $params = [
       'cycle' => $cycle,
       'day' => $day
       ];

    $cycletimes = $DB->get_records('local_batching_cycle_times', $params);

    return $cycletimes;

}

function add_batching_cycle_and_batches($data) {
    global $DB, $USER;

    $batchingid = $data->id;
    $loggedinuserid = $USER->id;
    $timestamp = time();

    $daystart = '';
    $batching = get_batchings($batchingid);
    $coursestartdate = $batching[$batchingid]->startdate;

    $trainingdays = get_batching_training_days($batchingid);

    if (!empty($data->cycles) && $trainingdays > 0) {
        $cyclecollection = [];
        $batchcollection = [];

        for ($c = 1; $c <= $data->cycles; $c++) {
            $cyclecode = 'C' . $c;
            $cycledata = new stdClass();
            $cycledata->code = $cyclecode;
            $cycledata->batching = $batchingid;
            $cycledata->status = 'Pending';
            $cycledata->timecreated = $timestamp;
            $cycledata->timemodified = $timestamp;
            $cycledata->usercreated = $loggedinuserid;
            $cycledata->usermodified = $loggedinuserid;

            $cycle = $DB->get_record('local_batching_cycles', array('batching' => $batchingid, 'code' => $cyclecode));

            if ($cycle) {
                $cycleid = $cycle->id;
            } else {
                $cycleid = $DB->insert_record('local_batching_cycles', $cycledata);
            }

            $cyclecollection[] = $cycleid;

            if ($trainingdays > 0) {
                $DB->delete_records('local_batching_cycle_times', ['cycle' => $cycleid]);

                for ($td = 1; $td <= $trainingdays; $td++) {

                    $cycletimes = get_cycle_incremental_times($daystart, $coursestartdate);

                    $cycledata->cycle = $cycleid;
                    $cycledata->starttime = $cycletimes['daystarttimestamp'];
                    $cycledata->endtime = $cycletimes['dayendtimestamp'];
                    $cycledata->day = $td;
                    $cycledata->timecreated = $timestamp;
                    $cycledata->timemodified = $timestamp;
                    $cycledata->usercreated = $loggedinuserid;
                    $cycledata->usermodified = $loggedinuserid;

                    $DB->insert_record('local_batching_cycle_times', $cycledata);

                    $daystart = strtotime('+1 day', $cycletimes['daystarttimestamp']);
                }
            }

            if (!empty($data->batches)) {

                for ($b = 1; $b <= $data->batches; $b++) {
                    $batchcode = 'B'. $b;
                    $batchedata = new stdClass();
                    $batchedata->code = $batchcode;
                    $batchedata->batching = $batchingid;
                    $batchedata->status = 'Pending';
                    $batchedata->cycle = $cycleid;
                    $batchedata->venue = 1;
                    $batchedata->timecreated = $timestamp;
                    $batchedata->timemodified = $timestamp;
                    $batchedata->usercreated = $loggedinuserid;
                    $batchedata->usermodified = $loggedinuserid;

                    $batch = $DB->get_record('local_batching_batches',
                    array('batching' => $batchingid, 'code' => $batchcode, 'cycle' => $cycleid));

                    if ($batch) {
                        $batchid = $batch->id;
                    } else {
                        $batchid = $DB->insert_record('local_batching_batches', $batchedata);
                    }

                    $batchcollection[] = $batchid;
                }
            }
        }
    }

    // Remove junk cycles.
    $junkcycles = $DB->get_records_select('local_batching_cycles', 'batching = ' . $batchingid . '
                  AND id NOT IN(' . implode(',', $cyclecollection) . ')');
    foreach ($junkcycles as $junkcycle) {
        $DB->delete_records('local_batching_cycle_times', ['cycle' => $junkcycle->id]);
        $DB->delete_records('local_batching_cycles', ['id' => $junkcycle->id]);
    }

    // Remove junk batches.
    $junkbatches = $DB->get_records_select('local_batching_batches', 'batching = ' . $batchingid . '
                   AND id NOT IN(' . implode(',', $batchcollection) . ')');
    foreach ($junkbatches as $junkbatch) {
        $DB->delete_records('local_batching_batches', ['id' => $junkbatch->id]);
    }

    distribute_participants_based_on_filters($batchingid);

}

function add_update_batching_financial($financialdata) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $timestamp = time();

    if ($financialdata->batchingstatus == 'corrigendum' || $financialdata->batchingstatus == 'addendum') {
        $proposallog = get_or_add_record_in_proposal_log_by_batching($financialdata->idbatching, $financialdata->batchingstatus);
        $postdata = new stdClass();
        $postdata->batching = $financialdata->idbatching;
        $postdata->title = $financialdata->itemtitle;
        $postdata->category = $financialdata->category;
        $postdata->proposallog = $proposallog;
        $postdata->cost = $financialdata->itemcost;
        $postdata->unit = $financialdata->itemunit;
        if ($financialdata->itemid > 0) {
            $financial = $DB->get_record('local_batching_financials', array('id' => $financialdata->itemid));

            if ($financial->proposallog == $proposallog) {
                $postdata->id = $financial->id;
                $postdata->timemodified = $timestamp;
                $postdata->usermodified = $loggedinuserid;
                $dataid = $DB->update_record('local_batching_financials', $postdata);
                return $financial->id;
            } else {
                $postdata->timecreated = $timestamp;
                $postdata->timemodified = $timestamp;
                $postdata->usercreated = $loggedinuserid;
                $postdata->usermodified = $loggedinuserid;
                $financialid = $DB->insert_record('local_batching_financials', $postdata);
                return $financialid;
            }
        } else {
            $postdata->timecreated = $timestamp;
            $postdata->timemodified = $timestamp;
            $postdata->usercreated = $loggedinuserid;
            $postdata->usermodified = $loggedinuserid;
            $financialid = $DB->insert_record('local_batching_financials', $postdata);
            return $financialid;
        }
    }
    $postdata = new stdClass();
    $postdata->batching = $financialdata->idbatching;
    $postdata->title = $financialdata->itemtitle;
    $postdata->category = $financialdata->category;
    $postdata->cost = $financialdata->itemcost;
    $postdata->unit = $financialdata->itemunit;
    if ($financialdata->itemid > 0) {
        $financial = $DB->get_record('local_batching_financials', array('id' => $financialdata->itemid));
        $postdata->id = $financial->id;
        $postdata->timemodified = $timestamp;
        $postdata->usermodified = $loggedinuserid;
        $dataid = $DB->update_record('local_batching_financials', $postdata);
        return $financial->id;
    } else {
        $postdata->timecreated = $timestamp;
        $postdata->timemodified = $timestamp;
        $postdata->usercreated = $loggedinuserid;
        $postdata->usermodified = $loggedinuserid;
        $financialid = $DB->insert_record('local_batching_financials', $postdata);
        return $financialid;
    }
}

function get_cycle_by_batching($batching) {
    global $DB;

    $cycles = $DB->get_records('local_batching_cycles', array('batching' => $batching));
    if ($cycles) {
        return $cycles;
    }
    return false;
}

function get_batch_by_batching_and_cycle($batching, $cycleid) {
    global $DB;

    $batches = $DB->get_records('local_batching_batches', array('batching' => $batching, 'cycle' => $cycleid));
    if ($batches) {
        return $batches;
    }
    return false;
}

function delete_batching_batch_user($tablename, $batching, $batch) {
    global $DB;

    $tablename = 'local_batching_' . $tablename;
    $DB->delete_records($tablename, array('batching' => $batching, 'batch' => $batch));
}

function delete_batching_venue($batch) {
    global $DB;

    $DB->delete_records('local_batching_venue_final', array('batch' => $batch));
}

function update_batching_cycle_time($cycletimeid, $timecolumnname, $cycledatetime) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $timestamp = time();

    $cycletimedata = new stdClass();
    $cycletimedata->id = $cycletimeid;
    $cycletimedata->$timecolumnname = $cycledatetime;
    $cycletimedata->usermodified = $loggedinuserid;
    $cycletimedata->timemodified = $timestamp;
    $DB->update_record('local_batching_cycle_times', $cycletimedata);

}

function get_batching_proposal_by_batching_id($id) {
    global $DB;

    $batchings = get_batchings($id);

    foreach ($batchings as $index => $batching) {

        foreach ($batching->cycles as $cycle => $batchs) {

            $cycletimes = $DB->get_records('local_batching_cycle_times', array('cycle' => $cycle));

            $batchings[$index]->cycles[$cycle]->cycletime = $cycletimes;

             // Get batching participants.
            $participantssql = <<<SQL
                                SELECT bu.*, concat(u.firstname, ' ', u.lastname) uname, s.name schoolname,
                                 s.address address, s.code schoolcode, z.name zonename, d.name dietname
                                 FROM {local_batching_participants} bu
                                 LEFT JOIN {user} u on u.id = bu.user
                                 LEFT JOIN {local_batching_batches} bb ON bb.id = bu.batch
                                 LEFT JOIN {local_batching_venue_final} vf ON vf.batch = bb.id
                                 LEFT JOIN {local_batching_venue} bv ON bv.id = vf.batchingvenueid
                                 LEFT JOIN {local_schools} s ON s.id = bv.school
                                 LEFT JOIN {local_zones} z ON z.id = s.zone_id
                                 LEFT JOIN {local_diets} d ON d.id = z.diet
                                 WHERE bb.cycle = :cycle
                                SQL;
            $params = [
                'cycle' => $cycle,
            ];

            $batchingparticipants = $DB->get_records_sql($participantssql, $params);

            $batchingparticipantsarray = array();

            foreach ($batchingparticipants as $participant) {
                $participantdata = array();
                $participantdata['id'] = $participant->user;
                $participantdata['name'] = $participant->uname;
                $participantdata['schoolname'] = $participant->schoolname;
                $participantdata['schooladdress'] = $participant->address;
                $participantdata['schoolcode'] = $participant->schoolcode;
                $batchingparticipantsarray[$participant->dietname][] = $participantdata;
            }

            $batchings[$index]->participants[$cycle] = $batchingparticipantsarray;

            // Get venue.
            $venuesql = <<<SQL
                SELECT bb.*, s.name schoolname, s.address address, z.name zonename, d.name dietname
                 FROM {local_batching_venue_final} vf
                JOIN {local_batching_batches} bb ON bb.id = vf.batch
                JOIN {local_batching_venue} bv ON bv.id = vf.batchingvenueid
                JOIN {local_schools} s ON s.id = bv.school
                 JOIN {local_zones} z ON z.id = s.zone_id
                 Join {local_diets} d ON d.id = z.diet
                 WHERE  bv.batching = :batching
                 AND  bb.cycle = :cycle
                SQL;

            $params = [
                'batching' => $index,
                'cycle' => $cycle
            ];

            $venuesdata = $DB->get_records_sql($venuesql, $params);

            $batchingvenuesarray = array();

            foreach ($venuesdata as $venue) {
                $batchingvenuesarray[$venue->dietname][$venue->zonename][] = $venue;
            }

            $batchings[$index]->venues[$cycle] = $batchingvenuesarray;
        }

        $beneficiaries = '';

        $filters = get_filters($index);

        if (!empty($filters)) {
            foreach ($filters as $filtervalue => $filter) {
                if ($filtervalue == 'roles' && $filter != null) {
                    $positionsql = 'SELECT name FROM {local_school_positions}';
                    $positionsql .= ' WHERE id IN (' . $filter . ')';
                    $positiondata = $DB->get_records_sql($positionsql);
                    foreach ($positiondata as $position) {
                        $beneficiaries .= $position->name.',';
                    }
                }
            }
        }
        $batchings[$index]->beneficiaries = $beneficiaries;

        // Get batching financial.
        $financials = get_financial_by_batching($index);
        $batchings[$index]->financials = $financials;
    }
    return $batchings;
}

function update_proposal($data) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $timestamp = time();

    $batchingdata = new stdClass();
    $batchingdata->id = $data->id;
    $batchingdata->usermodified = $loggedinuserid;
    $batchingdata->timemodified = $timestamp;

    if ($data->status) {
        $batchingdetail = $DB->get_record('local_batching', array('id' => $data->id));

        if ($batchingdetail->status == 'rejected' || $batchingdetail->status == 'approved') {
            add_record_in_batching_log($data);
        }

        $batchingdata->status = $data->status;
        $batchingdata->file_number = $data->filenumber;
        $batchingdata->comment = $data->comment;
        $DB->update_record('local_batching', $batchingdata);
    }
}

function launch_batching($batching) {
    // Create course group.

    return create_course_group_by_batching($batching);
}

function create_course_group_by_batching($id) {
    global $DB, $CFG, $USER;

    $batchinglaunched = false;
    $systemcontext = context_system::instance();

    // Get batching.
    $batching = get_batchings($id)[$id];
    $facilitatorrole = get_facilitator_role();
    $participantrole = get_participant_role();
    $coordinatorrole = get_coordinate_role();
    $observerrole = get_observer_role();

    $trainingdays = get_training_days_by_batching($id);
    $courseid = $batching->course;

    // If batching exists.
    if (!empty($batching)) {

        // Start cycle loop.
        $cyclecount = 0;
        $cycletimedata = [];
        foreach ($batching->cycles as $cycleid => $batchingcycles) {

            $cyclecount++;

            // Create grouping from cycle.
            $grouping = 'INSET' . $courseid . '/' . $batchingcycles->code;

            $groupingdata = new stdClass();
            $groupingdata->courseid = $courseid;
            $groupingdata->name = $grouping;
            $groupingdata->idnumber = $grouping;
            $groupingid = groups_create_grouping($groupingdata);
            // Update total days in lms_local_course_details table.
            $cycletime = get_cycle_times($cycleid);
            foreach ($cycletime as $record) {
                $cycletimedata[] = $record->starttime;
                $cycletimedata[] = $record->endtime;
            }
            $totalcycleinbatching = count($batching->cycles);

            // When we are in the last cycle, update in course details.
            if ($totalcycleinbatching == $cyclecount) {
                $cyclestartdate = reset($cycletimedata);
                $cycleenddate = end($cycletimedata);
                $days = get_day_difference_between_date($cyclestartdate, $cycleenddate);

                $coursedata = new stdClass();
                $coursedata->id = $courseid;
                $coursedata->startdate = $cyclestartdate;
                $coursedata->enddate = $cycleenddate;
                $coursedata->timemodified = time();
                $coursedata->updatedby = $USER->id;
                $DB->update_record('course', $coursedata);

                $coursedetail = get_course_detail_by_course($courseid);

                $coursedetaildata = new stdClass();
                $coursedetaildata->id = $coursedetail->id;
                $coursedetaildata->days = $days;
                add_update_record_in_course_detail($coursedetaildata);
            }

            foreach ($batchingcycles->batches as $batch) {

                // Create groups from batches.
                $group = $grouping . '/' . $batch->code;

                $groupdata = new stdClass();
                $groupdata->courseid = $courseid;
                $groupdata->idnumber = $group;
                $groupdata->name = $group;

                $groupid = groups_create_group($groupdata);
                get_participants_by_batch($batch->id);
                // Assign group to grouping.
                groups_assign_grouping($groupingid, $groupid);

                // Add group details record for each batch.
                add_record_in_group_detail($groupid, $cycletime, $batch->id);

                // Get participants and enroll them.
                $participants = get_users_in_batching('participants', $id, $cycleid, $batch->id);
                foreach ($participants as $participantid => $participant) {
                    enrol_try_internal_enrol($courseid, $participantid, $participantrole->id, time());
                    groups_add_member($groupid, $participantid);
                }

                // Get facilitators and enroll them.
                $facilitators = get_users_in_batching('facilitators', $id, $cycleid, $batch->id);
                foreach ($facilitators as $facilitatorid => $facilitator) {
                    enrol_try_internal_enrol($courseid, $facilitatorid, $facilitatorrole->id, time());
                    groups_add_member($groupid, $facilitatorid);
                }

                // Get observers and enroll them.
                $observers = get_users_in_batching('observers', $id, $cycleid, $batch->id);
                foreach ($observers as $observerid => $observer) {
                    enrol_try_internal_enrol($courseid, $observerid, $observerrole->id, time());
                    groups_add_member($groupid, $observerid);
                }

                // Get coordinators and enroll them.
                $coordinators = get_users_in_batching('coordinators', $id, $cycleid, $batch->id);
                foreach ($coordinators as $coordinatorid => $coordinator) {
                    enrol_try_internal_enrol($courseid, $coordinatorid, $coordinatorrole->id, time());
                    groups_add_member($groupid, $coordinatorid);
                }
            }
            // Batching loop ends.
        }
        // Cycle loop ends.

        // Loop through each day of the cycle.
        for ($day = 1; $day <= $trainingdays->value; $day++) {

            // Create new training day.
            $section = course_create_section($courseid);

            // Update section name.
            course_update_section($courseid, $section, ['name' => 'Training Day ' . $day]);

            // Add modules in course.
            add_module_to_course_via_batching($id, $courseid, $day);
        }

        // Loop through each day of the cycle and add cycle restrction now.
        for ($day = 1; $day <= $trainingdays->value; $day++) {
            $dayavailabiltiy = '';

            foreach ($batching->cycles as $cycleid => $batchingcycles) {

                $cycletimes = get_cycle_times_for_the_day($cycleid, $day);

                $params = [
                    'idnumber' => 'INSET' . $courseid . '/' . $batchingcycles->code,
                ];

                $grouping = $DB->get_record('groupings', $params);

                foreach ($cycletimes as $t => $cycletime) {
                    $dayavailabiltiy .= '{"op":"&","c":[
                     {"type":"date","d":">=","t":' . $cycletime->starttime . '},
					 {"type":"date","d":"<","t":' . $cycletime->endtime . '},
                     {"type":"grouping","id":' . $grouping->id . '}
                    ]}' . ',';
                }
            }

            $coursesections = get_course_section_by_course_id($batching->course, $day);

            foreach ($coursesections as $coursesection) {
                $sectiondata = [];
                $sectiondata['availability'] = '{"op":"|","show":true,"c":[' . rtrim($dayavailabiltiy, ',') . ']}';
                course_update_section($courseid, $coursesection, $sectiondata);
                $section++;
            }
        }

        // TODO: Need to trigger an email for HOS of approved venue with attachment of proposal.
        $approvedvenuehos = get_hos_of_approved_venue_by_batching($id);
        if (!empty($approvedvenuehos)) {
            $proposalattachment = $CFG->wwwroot . "/pluginfile.php/" . $systemcontext->id . "/local_batching/attachment/" . $id .
             '/' . $batching->proposal_file;
            foreach ($approvedvenuehos as $hos) {
                $email = $hos->email;
            }
        }

        $batchinglaunched = true;
    }

    return $batchinglaunched;
}

function get_course_section_by_course_id($courseid, $section = null) {
    global $DB;

    $coursesection = $DB->get_records('course_sections', ['course' => $courseid, 'section' => $section], 'id');
    return $coursesection;
}

function add_module_to_course_via_batching($batching, $courseid, $day) {

    global $DB, $CFG;
    $batchingday = get_max_day_in_cycle_time_by_batching($batching);

    $batchingdata = get_batchings($batching)[$batching];

    $lastday = false;

    // If this is the last day, then go in if condition.
    if ($day == $batchingday->maxday) {
        $completionstatus = 1;
        $lastday = true;
    } else {
        $completionstatus = 2;
    }

    $courselabel = check_record_exist_in_label_by_course_id($courseid);
    if (!$courselabel) {
        $course = (object)[
            'id' => $courseid
        ];
        $courseimgurl = \core_course\external\course_summary_exporter::get_course_image($course);
        if (empty($courseimgurl)) {
            $courseimgurl = $CFG->wwwroot . '/blocks/course_catalog/pix/no-image.png';
        }

        // Add label in course.
        if ($day == 1) {
            $contextid = context_course::instance($courseid);
            $trainingoverview = new stdClass();
            $trainingoverview->blockname = get_string('systemname', 'block_training_overview');
            $trainingoverview->parentcontextid = $contextid->id;
            $trainingoverview->showinsubcontexts = 0;
            $trainingoverview->pagetypepattern = 'course-view-*';
            $trainingoverview->defaultregion = 'fullwidth-top';
            $trainingoverview->defaultweight = 0;
            $trainingoverview->timecreated = time();
            $trainingoverview->timemodified = time();
            $DB->insert_record('block_instances', $trainingoverview);

            $trainingcompletionprogress = new stdClass();
            $trainingcompletionprogress->blockname = 'completion_progress';
            $trainingcompletionprogress->parentcontextid = $contextid->id;
            $trainingcompletionprogress->showinsubcontexts = 0;
            $trainingcompletionprogress->pagetypepattern = 'course-view-*';
            $trainingcompletionprogress->defaultregion = 'side-pre';
            $trainingcompletionprogress->defaultweight = 1;
            $trainingcompletionprogress->timecreated = time();
            $trainingcompletionprogress->timemodified = time();
            $DB->insert_record('block_instances', $trainingcompletionprogress);

            $trainingfacilitators = new stdClass();
            $trainingfacilitators->blockname = get_string('systemname', 'block_training_facilitators');
            $trainingfacilitators->parentcontextid = $contextid->id;
            $trainingfacilitators->showinsubcontexts = 0;
            $trainingfacilitators->pagetypepattern = 'course-view-*';
            $trainingfacilitators->defaultregion = 'side-pre';
            $trainingfacilitators->defaultweight = 2;
            $trainingfacilitators->timecreated = time();
            $trainingfacilitators->timemodified = time();
            $DB->insert_record('block_instances', $trainingfacilitators);

            $trainingcoordinators = new stdClass();
            $trainingcoordinators->blockname = get_string('systemname', 'block_training_coordinators');
            $trainingcoordinators->parentcontextid = $contextid->id;
            $trainingcoordinators->showinsubcontexts = 0;
            $trainingcoordinators->pagetypepattern = 'course-view-*';
            $trainingcoordinators->defaultregion = 'side-pre';
            $trainingcoordinators->defaultweight = 3;
            $trainingcoordinators->timecreated = time();
            $trainingcoordinators->timemodified = time();
            $DB->insert_record('block_instances', $trainingcoordinators);

            $trainingschedule = new stdClass();
            $trainingschedule->blockname = get_string('systemname', 'block_training_schedule');
            $trainingschedule->parentcontextid = $contextid->id;
            $trainingschedule->showinsubcontexts = 0;
            $trainingschedule->pagetypepattern = 'course-view-*';
            $trainingschedule->defaultregion = 'side-pre';
            $trainingschedule->defaultweight = 4;
            $trainingschedule->timecreated = time();
            $trainingschedule->timemodified = time();
            $DB->insert_record('block_instances', $trainingschedule);
        }

        // Update course section.
        update_course_section_by_course_id($courseid);
    }

    $courseidexist = check_record_exist_in_course_module_by_course_id($courseid);

    // Add morning attendance.
    $morningattendance = new stdClass();
    $morningattendance->course = $courseid;
    $morningattendance->name = 'Day ' . $day . ' Morning Attendance';
    $morningattendanceinstanceid = attendance_add_instance($morningattendance);

    // Add morning attendance object to add in course modules.
    $morningattendanceobject = new stdClass();
    $morningattendanceobject->availability = null;
    $morningattendanceobject->instanceid = $morningattendanceinstanceid;
    $morningattendanceobject->completion = 2;
    $morningattendanceobject->completiongradeitemnumber = 0;
    $morningattendanceobject->completionview = 0;
    $moriningattendancecoursemoduleid = add_module_in_course('attendance', $morningattendanceobject, $courseid, $day);

    // Add pretest.
    $quizintanceid = add_course_pre_test($courseid, $day);

    // Make quiz object to add in course modules.
    $quizintanceobject = new stdClass();
    $quizintanceobject->availability = '{"op":"&","showc":[true],"c":[{"type":"completion","cm":'
     . $moriningattendancecoursemoduleid . ',"e":' . $completionstatus . '}]}';
    $quizintanceobject->instanceid = $quizintanceid;
    $quizintanceobject->completion = 2;
    $quizintanceobject->completionview = 1;
    $quizintanceobject->completiongradeitemnumber = null;
    $quizcoursemoduleid = add_module_in_course('quiz', $quizintanceobject, $courseid, $day);

    // Add study material.
    $bookinstanceid = add_course_study_material($courseid, $day);

    // Make book object to add in course modules.
    $bookrestriction = '{"op":"&","showc":[true],"c":[{"type":"completion","cm":' . $quizcoursemoduleid . ',"e":1}]}';

    if ($lastday) {
        $coursemodulequizrecords = get_course_module_by_course_and_module_id(17, $courseid);
        $availabilty = '';
        foreach ($coursemodulequizrecords as $quizrrecord) {
            $availabilty .= '{"type":"completion","cm":' . $quizrrecord->id . ',"e":1}' .',';
        }
        $bookrestriction = '{"op":"&","showc":[true,true],
         "c":[{"op":"&","c":[{"type":"completion","cm":' . $moriningattendancecoursemoduleid . ',"e":1}]},
         {"op":"|","c":[' . rtrim($availabilty, ',') . ']}
         ]}';
    }

    if ($batchingdata->modalitydetails->shortname == 'online') {
        $zoomobject = new stdClass();
        $zoomobject->name = 'Day ' . $day . ' Online Training';
        $zoomobject->start_time = time() + 3600;
        $zoomobject->duration = 3600;
        $zoomobject->monthly_repeat_option = 0;
        $zoomobject->end_date_option = 1;
        $zoomobject->end_date_time = time() + 7200;
        $zoomobject->show_security = 1;
        $zoomobject->option_host_video = 0;
        $zoomobject->option_audio = 'both';
        $zoomobject->option_mute_upon_entry = 1;
        $zoomobject->host_id = zoom_get_user_id();
        $zoomobject->course = $courseid;
        $zoomobject->introformat = 1;
        $zoominstanceid = zoom_add_instance($zoomobject);

        $zoomintanceobject = new stdClass();
        $zoomintanceobject->availability = $bookrestriction;
        $zoomintanceobject->instanceid = $zoominstanceid;
        $zoomintanceobject->completion = 2;
        $zoomintanceobject->completionview = 1;
        $zoomintanceobject->completiongradeitemnumber = null;

        $studymaterial = add_module_in_course('zoom', $zoomintanceobject, $courseid, $day);
    } else {
        $bookintanceobject = new stdClass();
        $bookintanceobject->availability = $bookrestriction;
        $bookintanceobject->instanceid = $bookinstanceid;
        $bookintanceobject->completion = 2;
        $bookintanceobject->completionview = 1;
        $bookintanceobject->completiongradeitemnumber = null;

        $studymaterial = add_module_in_course('book', $bookintanceobject, $courseid, $day);
    }


    if ($lastday) {
        // Add post-test.
        $quizintanceid = add_course_post_test($courseid, $day);

        // Make quiz object to add in course modules.
        $quizintanceobject = new stdClass();
        $quizintanceobject->availability = '{"op":"&","showc":[true],"c":[{"type":"completion","cm":'
         . $studymaterial . ',"e":' . $completionstatus . '}]}';
        $quizintanceobject->instanceid = $quizintanceid;
        $quizintanceobject->completion = 2;
        $quizintanceobject->completionview = 1;
        $quizintanceobject->completiongradeitemnumber = null;
        $quizcoursemoduleid = add_module_in_course('quiz', $quizintanceobject, $courseid, $day);
    }

    // Add evening attendance.
    $eveningattendance = new stdClass();
    $eveningattendance->course = $courseid;
    $eveningattendance->name = 'Day ' . $day . ' Evening Attendance';
    $eveningattendanceinstanceid = attendance_add_instance($eveningattendance);

    // Make evening attendance object to add in course modules.
    $eveningattendanceobject = new stdClass();
    $eveningattendanceobject->availability = null;
    $eveningattendanceobject->instanceid = $eveningattendanceinstanceid;
    $eveningattendanceobject->completion = 2;
    $eveningattendanceobject->completiongradeitemnumber = null;
    $eveningattendanceobject->completionview = 0;

    $eveningattendancemoduleid = add_module_in_course('attendance', $eveningattendanceobject, $courseid, $day);

    // Add morning attendance session.
    add_attendance_session_in_course($morningattendanceinstanceid, $courseid, $batchingdata, $day, 'morning');

    // Add evening attendance.
    add_attendance_session_in_course($eveningattendanceinstanceid, $courseid, $batchingdata, $day, 'evening');

    if ($lastday) {

        $roles = [
            'student' => get_string('participantsfeedback', 'local_batching'),
            'facilitator' => get_string('facilitatorfeedback', 'local_batching'),
            'coordinator ' => get_string('coordinatorfeedback', 'local_batching')
        ];
        $section = $DB->get_record_sql("SELECT max(id) as id FROM {course_sections} WHERE course = $courseid");
        foreach ($roles as $key => $role) {
            $roleid = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = "'.$key.'"');
            local_add_questionnaire_activity($role, $roleid->id, $courseid, $section->id);
        }

        $coursecertificateintanceid = add_course_completion_certificate($courseid, $day);

        // Make evening attendance object to add in course modules.
        $coursecertificateobject = new stdClass();
        $coursecertificateobject->availability = '{"op":"&","showc":[true],
         "c":[{"type":"completion","cm":' . $eveningattendancemoduleid . ',"e":1}]}';
        $coursecertificateobject->instanceid = $coursecertificateintanceid;
        $coursecertificateobject->completion = 2;
        $coursecertificateobject->completiongradeitemnumber = null;
        $coursecertificateobject->completionview = 1;

        add_module_in_course('certificate', $coursecertificateobject, $courseid, $day);
    }

}

function add_module_in_course($moduletype, $moduleinstance, $courseid, $day) {
    global $DB;

    $moduleinfo = $DB->get_record('modules', array('name' => $moduletype));
    $coursesection = $DB->get_record('course_sections', array('course' => $courseid, 'section' => $day));

    $moduledata = new stdClass();
    $moduledata->course = $courseid;
    $moduledata->section = $coursesection->id;
    $moduledata->module = $moduleinfo->id;
    $moduledata->instance = $moduleinstance->instanceid;
    $moduledata->visibleoncoursepage = 1;
    $moduledata->visible = 1;
    $moduledata->visibleold = 1;
    $moduledata->groupmode = 1;
    $moduledata->completion = $moduleinstance->completion;
    $moduledata->availability = $moduleinstance->availability;
    $moduledata->completiongradeitemnumber = $moduleinstance->completiongradeitemnumber;
    $moduledata->completionview = $moduleinstance->completionview;

    $coursemoduleid = add_course_module($moduledata);

    $coursemoduledata = $DB->get_records('course_modules', array('course' => $courseid, 'section' => $coursesection->id));

    $coursesectiondata = array();
    foreach ($coursemoduledata as $coursemodule) {
        $coursesectiondata[] = $coursemodule->id;
    }
    $coursesectionsequence['sequence'] = implode(',', $coursesectiondata);

    course_update_section($courseid, $coursesection, $coursesectionsequence);

    return $coursemoduleid;
}

function add_course_completion_certificate($courseid, $day) {
    $coursecompletioncertificate = new stdClass();

    $coursecompletioncertificate->course = $courseid;
    $coursecompletioncertificate->name = 'Day ' . $day . ' - Certificate';
    $coursecompletioncertificate->intro = '';
    $coursecompletioncertificate->introformat = 1;
    $coursecompletioncertificate->savecert = 1;
    $coursecompletioncertificate->datefmt = 1;
    $coursecompletioncertificate->certificatetype = 'A4_non_embedded';
    $coursecompletioncertificate->orientation = 'L';
    $certificateinstanceid = certificate_add_instance($coursecompletioncertificate);

    return $certificateinstanceid;
}

function add_course_pre_test($courseid, $day) {
    $pretest = new stdClass();

    $pretest->course = $courseid;
    $pretest->name = 'Day ' . $day . ' -Pre Test';
    $pretest->quizpassword = '';
    $pretest->intro = '';
    $pretest->introformat = 1;
    $pretest->timelimit = 1800;
    $pretest->completionminattempts = 1;
    $pretest->attempts = 1;
    $pretest->preferredbehaviour = 'deferredfeedback';
    $quizinstanceid = add_quiz($pretest);

    return $quizinstanceid;
}

function add_course_post_test($courseid, $day) {
    $posttest = new stdClass();

    $posttest->course = $courseid;
    $posttest->name = 'Day ' . $day . ' -Post Test';
    $posttest->quizpassword = '';
    $posttest->intro = '';
    $posttest->introformat = 1;
    $posttest->timelimit = 1800;
    $posttest->completionminattempts = 1;
    $posttest->attempts = 1;
    $posttest->preferredbehaviour = 'deferredfeedback';
    $quizinstanceid = add_quiz($posttest);

    return $quizinstanceid;
}

function add_quiz($quizobject) {
    global $DB;

    $quizid = $DB->insert_record('quiz', $quizobject);

    // Add record in quiz section.
    add_record_in_quiz_section($quizid);

    return $quizid;
}

function add_course_study_material($courseid, $day) {
    $studymaterial = new stdClass();

    $studymaterial->course = $courseid;
    $studymaterial->name = 'Day ' . $day . ' - Study Material';
    $studymaterial->intro = '';
    $studymaterial->introformat = 1;
    $studymaterial->navstyle = 1;
    $studymaterial->numbering = 1;
    $bookinstanceid = add_book($studymaterial);

    return $bookinstanceid;
}

function add_book($studymaterial) {
    global $DB;

    $bookid = $DB->insert_record('book', $studymaterial);
    return $bookid;
}

function get_facilitator_role() {
    global $DB;

    return $DB->get_record('role', array('shortname' => 'facilitator'));
}

function get_participant_role() {
    global $DB;

    return $DB->get_record('role', array('shortname' => 'student'));
}

function get_coordinate_role() {
    global $DB;

    return $DB->get_record('role', array('shortname' => 'coordinator'));
}

function get_observer_role() {
    global $DB;

    return $DB->get_record('role', array('shortname' => 'observer'));
}

function check_record_exist_in_course_module_by_course_id($courseid) {
    global $DB;

    return $DB->record_exists('course_modules', array('course' => $courseid));
}

function get_course_module_by_course_and_module_id($moduleid, $courseid) {
    global $DB;

    return $DB->get_records('course_modules', array('module' => $moduleid, 'course' => $courseid));
}

function enable_manual_enrollment_in_course($enrollmenttype, $courseid) {
    global $DB;

    $currenttimestamp = time();

    $enablemanualenrollment = new stdClass();
    $enablemanualenrollment->courseid = $courseid;
    $enablemanualenrollment->enrol = $enrollmenttype;
    $enablemanualenrollment->timecreated = $currenttimestamp;
    $enablemanualenrollment->timemodified = $currenttimestamp;

    return $DB->insert_record('enrol', $enablemanualenrollment);

}

function check_venue_already_send_for_approval($venueid, $batching) {
    global $DB;

    return $DB->record_exists('local_batching_venue', array('batching' => $batching, 'school' => $venueid));
}

function add_record_in_group_detail($groupid, $cycletimes, $batchid) {
    global $DB, $USER;

    $currenttimestamp = time();
    $loggedinuserid = $USER->id;

    $finalvenue = get_final_venue_by_batch_id($batchid);

    if (!empty($cycletimes)) {
        foreach ($cycletimes as $cycletime) {
            $day = get_day_difference_between_date($cycletime->starttime, $cycletime->endtime);

            $groupdetailsql = "INSERT INTO {local_group_details} (`groupid`, venue, days, startdate, enddate,
             timecreated, timemodified, usercreated, usermodified) VALUES (:groupid, :batchingvenueid, :days,
             :starttime, :endtime, :timecreated, :timemodified, :usercreated, :usermodified)";

            $params = [
                'groupid' => $groupid,
                'batchingvenueid' => $finalvenue->venueid,
                'days' => $day,
                'starttime' => $cycletime->starttime,
                'endtime' => $cycletime->endtime,
                'timecreated' => $currenttimestamp,
                'timemodified' => $currenttimestamp,
                'usercreated' => $loggedinuserid,
                'usermodified' => $loggedinuserid,
            ];
            $DB->execute($groupdetailsql, $params);
        }
    }

}

function get_final_venue_by_batch_id($batchid) {
    global $DB;

    $venuesql = <<<SQL
            SELECT vf.*, v.school venueid FROM {local_batching_venue_final} vf
            JOIN {local_batching_venue} v on v.id = vf.batchingvenueid
            WHERE vf.batch = :batch
            SQL;

    $params = [
        'batch' => $batchid,
    ];

    $finalvenues = $DB->get_record_sql($venuesql, $params);

    return $finalvenues;
}

function add_update_record_in_course_detail($coursedetail) {
    global $DB, $USER;

    $currenttimestamp = time();
    $loggedinuserid = $USER->id;

    $coursedetailobject = new stdClass();

    if (isset($coursedetail->id) && $coursedetail->id > 0) {
        $coursedetailobject->id = $coursedetail->id;
        $coursedetailobject->days = $coursedetail->days;
        $coursedetailobject->timemodified = $currenttimestamp;
        $coursedetailobject->usermodified = $loggedinuserid;

        return $DB->update_record('local_course_details', $coursedetailobject);
    } else {
        $coursedetailobject->course = $coursedetail->course;
        $coursedetailobject->modality = $coursedetail->modality;
        $coursedetailobject->batching = $coursedetail->batching;
        $coursedetailobject->enablegrouping = $coursedetail->enablegrouping;
        $coursedetailobject->days = $coursedetail->days;
        $coursedetailobject->timecreated = $currenttimestamp;
        $coursedetailobject->timemodified = $currenttimestamp;
        $coursedetailobject->usercreated = $loggedinuserid;
        $coursedetailobject->usermodified = $loggedinuserid;

        return $DB->insert_record('local_course_details', $coursedetailobject);
    }
}

function get_day_difference_between_date($startdate, $enddate) {
    $startdate = new DateTime(customdateformat('DATE_WITHOUT_TIME', $startdate));
    $enddate = new DateTime(customdateformat('DATE_WITHOUT_TIME', $enddate));
    $diff = $enddate->diff($startdate);
    $day = $diff->format('%a');
    $day = ($day > 0) ? $day + 1 : 1;

    return $day;
}

function get_diet_head() {
    global $DB;

    $dietheadssql = <<<SQL
                SELECT DISTINCT ld.head, concat(u.firstname,' ',u.lastname) name
                FROM {local_diets} ld
                JOIN {user} u ON u.id = ld.head
                WHERE u.confirmed = 1 AND u.deleted = 0
                SQL;

    return $DB->get_records_sql($dietheadssql);
}

function get_course_category_by_idnumber($categoryidnumber = 'SCERTTRAININGS') {
    global $DB;

    $coursecategorysql = <<<SQL
                SELECT * FROM {course_categories}
                WHERE idnumber = :idnumber
                SQL;

    $params = [
        'idnumber' => $categoryidnumber,
    ];

    return $DB->get_record_sql($coursecategorysql, $params);
}

function get_course_detail_by_course($courseid) {
    global $DB;

    return $DB->get_record('local_course_details', array('course' => $courseid));
}

function get_financial_category() {
    global $DB;

    return $DB->get_records('local_financial_categories');
}

function get_financial_by_batching($batching) {
    global $DB;

    $batchingfinacialssql = <<<SQL
                             SELECT bf.*, bf.title, lfc.name categoryname FROM {local_batching_financials} bf
                             LEFT JOIN {local_financial_categories} lfc ON lfc.id = bf.category
                             WHERE batching = :batching
                             SQL;
    $params = [
        'batching' => $batching,
    ];

    return $DB->get_records_sql($batchingfinacialssql, $params);
}

function get_facilitator_training_count($userid) {
    global $DB;

    $usertrainingcountsql = 'SELECT count(c.id) FROM {course} c
             JOIN {local_course_details} lcd ON lcd.course = c.id
             JOIN {enrol} e ON e.courseid = c.id
             JOIN {user_enrolments} ue ON ue.enrolid = e.id
             JOIN {user} u ON u.id = ue.userid WHERE u.id = :userid
             AND c.visible = :visible AND u.confirmed = 1 AND u.deleted = 0
            ';

    $params = [
        'userid' => $userid,
        'visible' => 1
    ];

    return $DB->count_records_sql($usertrainingcountsql, $params);
}

function generate_proposal_html_by_batching($batching, $showfinancial) {
    global $DB, $CFG;

    $batchings = get_batching_proposal_by_batching_id($batching);

    $proposalhtml = '';
    $logopath = $CFG->dirroot . '/theme/edumy/pix/logoscert.jpg';

    if (!empty($batchings)) {
        foreach ($batchings as $proposals) {

            $proposalhtml .= '<div class="p-3" style="text-align: center">';
            $proposalhtml .= '<div><img src="' . $logopath . '" width="60" height="65"/></div>';
            $proposalhtml .= '<h4 class="font-weight-bold">' . get_string('statecouncil', 'local_batching') . '</h4>';
            $proposalhtml .= '<h4 class="">
                             (' . get_string('autonomousorganization', 'local_batching') . ')
                             </h4>';
            $proposalhtml .= '<h4 class="">' . get_string('organizationaddress', 'local_batching') . '</h4>';
            $proposalhtml .= '</div>';

            $totalfinancialexpenses = 0;
            if (!empty($proposals->financials)) {
                foreach ($proposals->financials as $financial) {
                    $totalfinancialexpenses += $financial->cost;
                }
            }
            $custommoney = custom_money_format($totalfinancialexpenses);
            $nodalofficerslist = implode(',' , $proposals->nodalofficers);

            $proposalinfo =  ['custommoney' => $custommoney, 'proposalsname' => $proposals->fullname ,'nodalofficerslist' => $nodalofficerslist ];
            $proposalhtml .= '<div class="p-3" style="text-align: center">';
            $proposalhtml .= '<p class="font-weight-bold">' . get_string('administrativeapproval', 'local_batching', $proposalinfo) . '</p>';
            $proposalhtml .= '</div>';

            $proposalhtml .= '<div class="header_text" style="text-align: right">';
            $proposalhtml .= '<div class="header_text">' . get_string('date', 'local_batching') . ' </div>';
            $proposalhtml .= '</div>';

            $proposalhtml .= '<div class="" style="text-align: center">';
            $proposalhtml .= '<h4 class="">' . get_string('filenumber', 'local_batching') . $proposals->file_number . ' </h4>';
            $proposalhtml .= '</div>';

            $proposalhtml .= '<div class="proposal-page pt-1">';
            $proposalhtml .= '<h4 class="font-weight-bold pt-2">' . get_string('subject', 'local_batching') . '</h4>';
            $proposalhtml .= '<div class="light-bg p-3">' . $proposals->fullname . '</div>';
            $proposalhtml .= '<h4 class="font-weight-bold mt-4">' . get_string('objective', 'local_batching') . '</h4>';
            $proposalhtml .= '<div class="light-bg p-3">' . $proposals->summary . '</div>';
            $proposalhtml .= '<h4 class="font-weight-bold mt-4">' . get_string('scheduletraining', 'local_batching') . '</h4>';
            $proposalhtml .= '<div class="light-bg p-3"><table class="schedule_training_program_table simple-table">';
            $proposalhtml .= '<thead><tr role="row">
            <th class"header c0 sorting_asc">' . get_string('cycle', 'local_batching') . '</th>
            <th class"header c0 sorting_asc">' . get_string('startdate', 'local_batching') . '</th>
            <th class"header c0 sorting_asc">' . get_string('enddate', 'local_batching') . '</th>
            <th class"header c0 sorting_asc">' . get_string('starttime', 'local_batching') . '</th>
            <th class"header c0 sorting_asc">' . get_string('endtime', 'local_batching') . ' </th></tr></thead>';
            $venuesdata = [];
            foreach ($proposals->cycles as $cycle => $cycles) {

                foreach ($cycles->cycletime as $index => $cycletime) {
                    $proposalhtml .= '<tr class="lastrow odd">';
                    $proposalhtml .= '<td class="cell c0 sorting_1">' . $cycles->code . '</td>';
                    $proposalhtml .= '<td class="cell c0 sorting_1">' . customdateformat('DATE_WITHOUT_TIME', $cycletime->starttime) . '</td>';
                    $proposalhtml .= '<td class="cell c0 sorting_1">' . customdateformat('DATE_WITHOUT_TIME', $cycletime->endtime) . '</td>';
                    $proposalhtml .= '<td class="cell c0 sorting_1">' . customdateformat('TIME', $cycletime->starttime) . '</td>';
                    $proposalhtml .= '<td class="cell c0 sorting_1">' . customdateformat('TIME', $cycletime->endtime) . '</td>';
                    $proposalhtml .= '</tr>';

                }
                foreach ($proposals->venues[$cycle] as $diet => $zones) {
                    foreach ($zones as $zone => $school) {
                        $totalbateches = count($proposals->cycles) * count($proposals->cycles[$cycle]->batches);
                        $venue = [
                            'diet' => $diet,
                            'zone' => $zone,
                            'cycles' => count($proposals->cycles),
                            'batches' => count($proposals->cycles[$cycle]->batches),
                            'totalbatches' => $totalbateches,
                        ];
                        $venuesdata[$diet] = $venue;
                    }
                }
            }
            $proposalhtml .= '</table></div>';
            $proposalhtml .= '<h4 class="font-weight-bold mt-4">' . get_string('venues', 'local_batching') . '</h4>';
            $proposalhtml .= '<div class="light-bg p-3">
                             <p>' . get_string('venuedescription', 'local_batching') . '</p>';
            $proposalhtml .= '<table class="venue_table simple-table">';
            $proposalhtml .= "<thead>
                                <tr role='row'>
                                    <th class='header c0 sorting_asc'>" . get_string('diets', 'local_batching') . "</th>
                                    <th class='header c0 sorting_asc'>" . get_string('zone', 'local_batching') . "</th>
                                    <th class='header c0 sorting_asc'>" . get_string('noofcycle', 'local_batching') . "</th>
                                    <th class='header c0 sorting_asc'>" . get_string('noofbatchesineachcycle', 'local_batching') . "</th>
                                    <th class='header c0 sorting_asc'>" . get_string('totalbatches', 'local_batching') . "</th>
                                </tr>
                            </thead>";

            $venueshtml = '';
            foreach ($venuesdata as $key => $vanue) {
                $venueshtml .= '<tr class="lastrow odd">';
                $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['diet'] . '</td>';
                $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['zone'] . '</td>';
                $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['cycles'] . '</td>';
                $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['batches'] . '</td>';
                $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['totalbatches'] . '</td>';
                $venueshtml .= '</tr>';
            }
            $proposalhtml .= $venueshtml;
            $proposalhtml .= '</table></div>';

            if ($proposals->beneficiaries) {
                $proposalhtml .= '<h4 class="font-weight-bold mt-4">' . get_string('participantsorbeneficiaries', 'local_batching') . '</h4>';
                $proposalhtml .= '<div class="light-bg p-3">'.rtrim($proposals->beneficiaries, ',').'</div>';
            }

            $proposalhtml .= '<h4 class="font-weight-bold mt-4">' . get_string('financialimplications', 'local_batching') .'</h4>';
            $proposalhtml .= '<div class="light-bg p-3">'. get_string('placedopposite', 'local_batching');
            $proposalhtml .= '<ul class="pl-3">';
            $proposalhtml .= "<li>" . get_string('teaandlunchorganised', 'local_batching') . "</li>";
            $proposalhtml .= "<li>" . get_string('requiredstationaryandtlm', 'local_batching') . "</li>";
            $proposalhtml .= "<li>" . get_string('coordinatorassigned', 'local_batching') . "</li>";
            $proposalhtml .= "<li>" . get_string('filesettledatdiets', 'local_batching') . "</li>";
            $proposalhtml .= '</ul></div>';
            if (!empty($proposals->financials) && $showfinancial) {
                $proposalhtml .= '<h4 class="font-weight-bold mt-4">' . get_string('financials', 'local_batching') . '</h4>';
                $proposalhtml .= '<div class="light-bg p-3">';
                $proposalhtml .= '<table class="financial_table simple-table">';
                $proposalhtml .= '<thead>
                                    <tr role="row">
                                        <th class"header c0 sorting_asc">' . get_string('category', 'local_batching') . '</th>
                                        <th class"header c0 sorting_asc">' . get_string('item', 'local_batching') . '</th>
                                        <th class"header c0 sorting_asc">' . get_string('unit', 'local_batching') . '</th>
                                        <th class"header c0 sorting_asc">' . get_string('cost', 'local_batching') . '</th>
                                    </tr>
                                </thead>';
                $financialhtml = '';
                $totalexpenses = 0;
                foreach ($proposals->financials as $financial) {
                    $totalexpenses += $financial->cost;
                    $financialhtml .= '<tr class="lastrow odd">';
                    $financialhtml .= '<td class="cell c0 sorting_1">' . (($financial->categoryname) ?
                    $financial->categoryname : get_string('hyphen', 'local_batching')) . '</td>';
                    $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->title . '</td>';
                    $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->unit . '</td>';
                    $financialhtml .= '<td class="cell c0 sorting_1">' . custom_money_format($financial->cost) . '</td>';
                    $financialhtml .= '</tr>';
                }
                $financialhtml .= '<tr class="lastrow odd">';
                $financialhtml .= '<td class="cell c0 sorting_1"></td>';
                $financialhtml .= '<td class="cell c0 sorting_1"><b>' . get_string('total', 'local_batching') . '</b></td>';
                $financialhtml .= '<td class="cell c0 sorting_1"></td>';
                $financialhtml .= '<td class="cell c0 sorting_1"><b>' . custom_money_format($totalexpenses) . '</b></td>';
                $financialhtml .= '</tr>';

                $proposalhtml .= $financialhtml;
                $proposalhtml .= '</table></div>';
            }

            $proposalhtml .= '<hr class="my-5" />';
            $proposalhtml .= '<div class="text-center mt-4">';
            $proposalhtml .= '<strong class="text-center">' . get_string('annexure_a', 'local_batching') . '</strong>';
            $proposalhtml .= '</div>';
            $i = 1;
            foreach ($proposals->participants as $index => $diets) {
                $proposalhtml .= '<h4 class="font-weight-bold text-center">'
                                  . $proposals->fullname . get_string('forcycle', 'local_batching') . $i .
                                  '</h4>';
                foreach ($proposals->cycles[$index]->cycletime as $cycletime) {
                    $proposalhtml .= '<div class="font-weight-bold">
                                     Date: ' . customdateformat('DATE_WITHOUT_TIME', $cycletime->starttime) .
                                     '</div>';
                    $proposalhtml .= '<div class="font-weight-bold mb-3">Timings: ' . customdateformat('TIME', $cycletime->starttime) .
                     ' - '. customdateformat('TIME', $cycletime->endtime). '</div>';
                }
                foreach ($diets as $diet => $users) {
                    $proposalhtml .= '<table class="participant_table simple-table">';
                    $proposalhtml .= '<thead>
                                        <tr role="row">
                                            <th class="header c0 sorting_asc font-weight-bold">' . get_string('employeeid', 'local_batching') . '</th>
                                            <th class="header c0 sorting_asc font-weight-bold">' . get_string('employeename', 'local_batching') . '</th>
                                            <th class="header c0 sorting_asc font-weight-bold">' . get_string('schoolidstr', 'local_batching') . '</th>
                                            <th class="header c0 sorting_asc font-weight-bold">' . get_string('schoolname', 'local_batching') . '</th>
                                            <th class="header c0 sorting_asc font-weight-bold">' . get_string('schooladdress', 'local_batching') . '</th>
                                            <th class="header c0 sorting_asc font-weight-bold"></th>
                                        </tr>
                                    </thead>';
                    foreach ($users as $user) {

                        $proposalhtml .= '<tr class="lastrow odd">';
                        $proposalhtml .= '<td class="cell c0 sorting_1">' . $user['id'] . '</td>';
                        $proposalhtml .= '<td class="cell c0 sorting_1">' . $user['name'] . '</td>';
                        $proposalhtml .= '<td class="cell c0 sorting_1">' . $user['schoolcode'] . '</td>';
                        $proposalhtml .= '<td class="cell c0 sorting_1">' . $user['schoolname'] . '</td>';
                        $proposalhtml .= '<td class="cell c0 sorting_1">' . $user['schooladdress'] . '</td>';
                        $proposalhtml .= '</tr>';
                    }
                    $proposalhtml .= '</table>';
                    $proposalhtml .= '</div>';

                }
                $i++;
            }
            $proposalhtml .= '</div>';

            $proposalhtml .= '<div class="" style="text-align: right">';
            $proposalhtml .= '<h4 class="mt-5"> ' . get_string('line', 'local_batching') . ' </h4>';
            $proposalhtml .= '<h4 class="">('. get_string('branchname', 'local_batching') . ') </h4>';
            $proposalhtml .= '<div class="header_text">'. get_string('dateformat', 'local_batching') .' </div>';
            $proposalhtml .= '</div>';
        }
    }
    return $proposalhtml;
}


function generate_pdf_from_html($pdfcontent, $filenamewithpath) {
    global $CFG;

    $fontpath = $CFG->dirroot . '/theme/edumy/fonts/Roboto-Regular.ttf';
    $pdf = new TCPDF('L');     // L for Landscape mode, P for Portrait mode.
    $pdf->AddPage();
    $font = TCPDF_FONTS::addTTFfont($fontpath);
    $pdf->SetFont($font, '', 11); // Font settings.
    $pdf->writeHTML($pdfcontent , true, false, true, false, '');
    $pdf->Output($filenamewithpath, "F");

    return $filenamewithpath;
}

function upload_proposal_file($batching) {
    global $DB, $USER, $CFG;

    $loggedinuserid = $USER->id;
    $author = $USER->firstname.' '.$USER->lastname;
    $currenttimestamp = time();

    $systemcontext = context_system::instance();

    $proposalfiles = [
        'full' => 'proposal-' . $batching . '-file-full-version.pdf',
        'limited' => 'proposal-' . $batching . '-file-limited-version.pdf',
    ];

    foreach ($proposalfiles as $key => $proposalfile) {
        $batchingdata = new stdClass();
        $batchingdata->id = $batching;

        if ($key == 'limited') {
            $proposalhtml = generate_proposal_html_by_batching($batching, false);
            $batchingdata->circular_file = $proposalfile;
        } else {
            $proposalhtml = generate_proposal_html_by_batching($batching, true);
            $batchingdata->proposal_file = $proposalfile;
        }
        $filenamewithpath = $CFG->dataroot . '/temp/filestorage' . '/' . $proposalfile;

        $filepath = generate_pdf_from_html($proposalhtml, $filenamewithpath);
        if (file_exists($filepath)) {
            // Create a file record in Moodle and get the file object.
            $filename = basename($filepath);
            $filemimetype = mime_content_type($filepath);
            $fs = get_file_storage();
            $fileinfo = array(
                'contextid' => $systemcontext->id,
                'component' => 'local_batching',
                'filearea' => 'attachment',
                'itemid' => $batching,
                'userid' => $loggedinuserid,
                'source' => $filename,
                'author' => $author,
                'license' => 'public',
                'filepath' => '/',
                'filename' => $filename,
                'filetype' => $filemimetype,
            );
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
            if ($file) {
                $file->delete();
            }

            $filestatus = $fs->create_file_from_pathname($fileinfo, $filepath);

            $batchingdata->updatedby = $loggedinuserid;
            $batchingdata->timemodified = $currenttimestamp;
            if ($DB->update_record('local_batching', $batchingdata)) {
                unlink($filepath);
            }
        }
    }
}

function get_hos_of_approved_venue_by_batching($batching) {
    global $DB;

    $batchingvenussql = <<<SQL
                    SELECT lbv.*, u.email FROM {local_batching_venue} lbv
                    JOIN {local_schools} ls on ls.id = lbv.school
                    JOIN {user} u on u.id = ls.hos
                    where lbv.batching = :batching AND lbv.status = :status
                    AND u.confirmed = 1 AND u.deleted = 0
                    SQL;
    $params = [
        'batching' => $batching,
        'status' => 'Approved',
    ];

    return $DB->get_records_sql($batchingvenussql, $params);
}

function get_cycle_incremental_times($daystart, $coursestartdate) {

    if (empty($daystart)) {
        $daystart = $coursestartdate;
    }

    $daystartdate = customdateformat('DATE_WITHOUT_TIME', $daystart);
    $daystartdatetime = new DateTime($daystartdate);

    $daystartdate = customdateformat('DATE_WITHOUT_TIME', $daystart);
    $starttime = '10:00:00';
    $endtime = '17:00:00';

    $daystartdatetime = new DateTime($daystartdate . ' ' . $starttime);
    $daystartendtime = new DateTime($daystartdate . ' ' . $endtime);
    $daystarttimestamp = $daystartdatetime->getTimestamp();
    $dayendtimestamp = $daystartendtime->getTimestamp();

    $cycletimes = [
        'daystarttimestamp' => $daystarttimestamp,
        'dayendtimestamp' => $dayendtimestamp
    ];

    return $cycletimes;
}

function get_training_days_by_batching($batching) {
    global $DB;

    $batchingvenussql = <<<SQL
                SELECT * FROM {local_batching_filters}
                where batching = :batching AND name = :name
                SQL;
    $params = [
        'batching' => $batching,
        'name' => 'trainingnoofdays',
    ];

    return $DB->get_record_sql($batchingvenussql, $params);
}

function get_max_day_in_cycle_time_by_batching($batching) {
    global $DB;

    $batchingcycletimemaxdaysql = <<<SQL
                SELECT MAX(lnct.day) maxday  FROM {local_batching_cycles} lbc
                JOIN {local_batching_cycle_times} lnct ON lnct.cycle = lbc.id
                where lbc.batching = :batching
                SQL;
    $params = [
        'batching' => $batching,
    ];

    return $DB->get_record_sql($batchingcycletimemaxdaysql, $params);
}

function get_participants_by_batch($batchid) {
    global $DB;

    $batchingparticipantsql = <<<SQL
                SELECT lbp.*, concat(u.firstname, ' ', u.lastname) uname, s.name schoolname, s.address address, s.code schoolcode
                FROM {local_batching_participants} lbp
                LEFT JOIN {user} u on u.id = lbp.user
                LEFT JOIN {local_batching_batches} bb ON bb.id = lbp.batch
                LEFT JOIN {local_batching_venue_final} vf ON vf.batch = bb.id
                LEFT JOIN {local_batching_venue} bv ON bv.id = vf.batchingvenueid
                LEFT JOIN {local_schools} s ON s.id = bv.school
                WHERE lbp.batch = :batch
                SQL;
    $params = [
        'batch' => $batchid,
    ];

    return $DB->get_records_sql($batchingparticipantsql, $params);
}


function get_co_ordinators_by_batching($batchingid, $batch = null) {
    global $DB;

    $sql = <<<SQL
                SELECT c.* FROM {local_batching_coordinators} c
                JOIN {user} u ON u.id = c.user
                where c.batching = :batching
                AND u.confirmed = 1 AND u.deleted = 0
                SQL;
    $params = [
        'batching' => $batchingid,
    ];

    return $DB->get_records_sql($sql, $params);
}

function get_facilitators_by_batching($batchingid, $batch = null) {
    global $DB;

    $sql = <<<SQL
                SELECT f.*, u.* FROM {local_batching_facilitators} f
                JOIN {user} u ON u.id = f.user
                where f.batching = :batching
                AND u.confirmed = 1 AND u.deleted = 0
                SQL;
    $params = [
        'batching' => $batchingid,
    ];

    return $DB->get_records_sql($sql, $params);
}

function add_attendance_session_in_course($attendanceid, $courseid, $batchingdata, $day, $sessionname) {
    global $DB;

    foreach ($batchingdata->cycles as $cycleid => $cyclebatches) {
        $cycletimes = get_cycle_times_for_the_day($cycleid, $day);
        foreach ($cycletimes as $cycletimeid => $cycletime) {
            foreach ($cyclebatches->batches as $batch) {
                $groupidnumber = 'INSET' . $courseid . '/' . $cyclebatches->code . '/' . $batch->code;
                $group = $DB->get_record('groups', array('idnumber' => $groupidnumber));

                $daystartdate = customdateformat('DATE_WITHOUT_TIME', $cycletime->starttime);

                $cyclestarttime = customdateformat('TIME', $cycletime->starttime);

                $dayendstartdate = customdateformat('DATE_WITHOUT_TIME', $cycletime->endtime);

                $cycleendstarttime = customdateformat('TIME', $cycletime->endtime);

                $daystartdatetime = new DateTime($daystartdate . ' ' . $cyclestarttime);

                $dayendenddatetime = new DateTime($dayendstartdate . ' ' . $cycleendstarttime);

                $daystarttimestamp = $daystartdatetime->getTimestamp();
                $dayendtimestamp = $dayendenddatetime->getTimestamp();
                $duration = 7200;
                if ($sessionname == 'morning') {
                    $attendancesession = new stdClass();
                    $attendancesession->attendanceid = $attendanceid;
                    $attendancesession->description = 'Regular class session';
                    $attendancesession->groupid = $group->id;
                    $attendancesession->sessdate = $daystarttimestamp;
                    $attendancesession->duration = $duration;
                    $attendancesession->timemodified = time();
                    $attendancesession->descriptionformat = 1;
                    $attendancesession->studentscanmark = 1;
                    $attendancesession->includeqrcode = 1;

                    $DB->insert_record('attendance_sessions', $attendancesession);
                }

                if ($sessionname == 'evening') {
                    $attendancesession = new stdClass();
                    $attendancesession->attendanceid = $attendanceid;
                    $attendancesession->description = 'Regular class session';
                    $attendancesession->groupid = $group->id;
                    $attendancesession->sessdate = $dayendtimestamp - $duration;
                    $attendancesession->duration = $duration;
                    $attendancesession->timemodified = time();
                    $attendancesession->descriptionformat = 1;
                    $attendancesession->studentscanmark = 1;
                    $attendancesession->includeqrcode = 1;

                    $DB->insert_record('attendance_sessions', $attendancesession);
                }
            }
        }
    }
}

function local_add_questionnaire_activity($questionnairename, $roleid, $courseid, $sectionid) {
    global $DB;

    // Create the questionnaire instance.
    $questionnairesurvey = new stdClass();
    $questionnairesurvey->courseid = $courseid; // Replace with the desired course ID.
    $questionnairesurvey->name = $questionnairename; // Replace with the desired name for the questionnaire.
    $questionnairesurvey->realm = 'private'; // Replace with the desired introduction text for the questionnaire.
    $questionnairesurvey->title = $questionnairename;
    $sid = $DB->insert_record('questionnaire_survey', $questionnairesurvey);

    $role = $DB->get_record('role', array('id' => $roleid));
    $question = new stdClass();
    $question->surveyid = $sid;
    $question->name = 1;
    $question->type_id = 8;
    $question->length = 5;
    $question->position = 1;
    $question->extradata = '[]';
    $question->content = '<p dir="ltr" style="text-align: left;">How would you rate '.$role->name.'?</p>';

    $questionid = $DB->insert_record('questionnaire_question', $question);

    $questchoice = new stdClass();
    $questchoice->question_id = $questionid;
    $questchoice->content = '1-5';
    $questionid = $DB->insert_record('questionnaire_quest_choice', $questchoice);

    // Create the questionnaire instance.
    $questionnaire = new stdClass();
    $questionnaire->course = $courseid; // Replace with the desired course ID.
    $questionnaire->name = $questionnairename; // Replace with the desired name for the questionnaire.
    $questionnaire->intro = ''; // Replace with the desired introduction text for the questionnaire.
    $questionnaire->introformat = '';
    $questionnaire->timecreated = time();
    $questionnaire->sid = $sid;
    $questionnaire->timemodified = $questionnaire->timecreated;
    $questionnaire->assigned_user_role_id = $roleid;
    $questionnaireid = $DB->insert_record('questionnaire', $questionnaire);

    $questionnairemoduleid = $DB->get_field('modules', 'id', array('name' => 'questionnaire'));

    // Add the questionnaire to the course module.
    $module = new stdClass();
    $module->course = $questionnaire->course;
    $module->module = $questionnairemoduleid; // Replace with the actual module ID of the questionnaire activity.
    $module->instance = $questionnaireid;
    $module->section = $sectionid; // Replace with the desired section ID where the questionnaire will be added.
    $module->added = time();
    $moduleid = $DB->insert_record('course_modules', $module);

    // Update the section's sequence.
    $section = $DB->get_record('course_sections', array('id' => $module->section), '*', MUST_EXIST);
    $section->sequence .= ',' . $moduleid;
    $DB->update_record('course_sections', $section);

    // Return the questionnaire instance ID.
    return $questionnaireid;
}

function add_record_in_batching_log($data) {
    global $DB;

    $batchingdata = $DB->get_record('local_batching', array('id' => $data->id));

    $batchingdata->batching = $data->id;
    unset($batchingdata->id);

    $DB->insert_record('local_batching_logs', $batchingdata);

}

function get_finanancial_changes_by_batching($batching, $proposalchangetype) {
    global $DB;

    $batchingfinacialssql = '
                    SELECT bf.*, bf.title, lfc.name categoryname FROM {local_batching_financials} bf
                    LEFT JOIN {local_financial_categories} lfc ON lfc.id = bf.category
                    JOIN {local_batching_proposal_logs} lbfl ON lbfl.id = bf.proposallog
                    ';

    $batchingfinacialssql .= ' WHERE lbfl.batching = '.$batching.' AND lbfl.type = "' . $proposalchangetype . '" ';

    $batchingfinancials = $DB->get_records_sql($batchingfinacialssql);

    return $batchingfinancials;
}

function get_or_add_record_in_proposal_log_by_batching($id, $proposalchangetype) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $currenttimestamp = time();

    $poroposaldetail = $DB->get_record('local_batching_proposal_logs',
     array('batching' => $id, 'type' => $proposalchangetype, 'status' => 'open'));
    if (!$poroposaldetail) {
        $proposallogs = new stdClass();
        $proposallogs->batching = $id;
        $proposallogs->type = $proposalchangetype;
        $proposallogs->status = 'open';
        $proposallogs->usercreated = $loggedinuserid;
        $proposallogs->usermodified = $loggedinuserid;
        $proposallogs->timecreated = $currenttimestamp;
        $proposallogs->timemodified = $currenttimestamp;
        return $DB->insert_record('local_batching_proposal_logs', $proposallogs );
    } else {
        return $poroposaldetail->id;
    }
}

function upload_corrigendum_or_addendum_file($batching, $proposalchangetype) {
    global $DB, $USER, $CFG;

    $loggedinuserid = $USER->id;
    $author = $USER->firstname.' '.$USER->lastname;
    $currenttimestamp = time();

    $systemcontext = context_system::instance();
    $proposallog = get_proposal_log($batching, $proposalchangetype);

    if (!$proposallog) {
        return;
    }
    $proposallogfile = 'proposal-' . $batching . '-' . $proposallog->id . '-' . $proposalchangetype. '-file.pdf';

    $proposallogdata = new stdClass();
    $proposallogdata->id = $proposallog->id;
    $proposallogdata->file = $proposallogfile;

    $proposalhtml = generate_corrigendum_or_addendum_proposal_html($proposallog->id, $proposalchangetype);

    $filenamewithpath = $CFG->dataroot . '/temp/filestorage' . '/' . $proposallogfile;

    $filepath = generate_pdf_from_html($proposalhtml, $filenamewithpath);
    if (file_exists($filepath)) {
        // Create a file record in Moodle and get the file object.
        $filename = basename($filepath);
        $filemimetype = mime_content_type($filepath);
        $fs = get_file_storage();
        $fileinfo = array(
            'contextid' => $systemcontext->id,
            'component' => 'local_batching',
            'filearea' => 'attachment',
            'itemid' => $proposallog->id,
            'userid' => $loggedinuserid,
            'source' => $filename,
            'author' => $author,
            'license' => 'public',
            'filepath' => '/',
            'filename' => $filename,
            'filetype' => $filemimetype,
        );
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
        if ($file) {
            $file->delete();
        }

        $filestatus = $fs->create_file_from_pathname($fileinfo, $filepath);

        $proposallogdata->usermodified = $loggedinuserid;
        $proposallogdata->status = 'close';
        $proposallogdata->timemodified = $currenttimestamp;
        if ($DB->update_record('local_batching_proposal_logs', $proposallogdata)) {
            unlink($filepath);
        }
    }
}

function generate_corrigendum_or_addendum_proposal_html($proposallogid, $proposalchangetype) {
    global $DB;

    $batchingfinacialssql = 'SELECT bf.*, bf.title, lfc.name categoryname FROM {local_batching_financials} bf
                            LEFT JOIN {local_financial_categories} lfc ON lfc.id = bf.category';

    $batchingfinacialssql .= ' WHERE proposallog = '.$proposallogid.' ';

    $batchingfinancials = $DB->get_records_sql($batchingfinacialssql);

    $proposalhtml = '';
    if (!empty($batchingfinancials)) {
        $proposalhtml .= '<div class="p-3" style="text-align: center">';
        $proposalhtml .= '<h4 class="font-weight-bold">Financials after ' . ucfirst($proposalchangetype) . '</h4>';
        $proposalhtml .= '</div>';
        $proposalhtml .= '<h4 class="font-weight-bold mt-4">' . get_string('financials', 'local_batching') . '</h4>';
        $proposalhtml .= '<div class="light-bg p-3">';
        $proposalhtml .= '<table class="financial_table simple-table">';
        $proposalhtml .= '<thead>
                            <tr role="row">
                                <th class"header c0 sorting_asc">' . get_string('category', 'local_batching') . '</th>
                                <th class"header c0 sorting_asc">' . get_string('item', 'local_batching') . '</th>
                                <th class"header c0 sorting_asc">' . get_string('unit', 'local_batching') . '</th>
                                <th class"header c0 sorting_asc">' . get_string('cost', 'local_batching') . '</th>
                            </tr>
                         </thead>';
        $financialhtml = '';
        $totalexpenses = 0;
        foreach ($batchingfinancials as $financial) {
            $totalexpenses += $financial->cost;
            $financialhtml .= '<tr class="lastrow odd">';
            $financialhtml .= '<td class="cell c0 sorting_1">'
                              . (($financial->categoryname) ? $financial->categoryname : get_String('hyphen', 'local_batching')) .
                              '</td>';
            $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->title . '</td>';
            $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->unit . '</td>';
            $financialhtml .= '<td class="cell c0 sorting_1">' . custom_money_format($financial->cost) . '</td>';
            $financialhtml .= '</tr>';
        }
        $financialhtml .= '<tr class="lastrow odd">';
        $financialhtml .= '<td class="cell c0 sorting_1"></td>';
        $financialhtml .= '<td class="cell c0 sorting_1"><b>' . get_string('total', 'local_batching') . '</b></td>';
        $financialhtml .= '<td class="cell c0 sorting_1"></td>';
        $financialhtml .= '<td class="cell c0 sorting_1"><b>' . custom_money_format($totalexpenses) . '</b></td>';
        $financialhtml .= '</tr>';

        $proposalhtml .= $financialhtml;
        $proposalhtml .= '</table></div>';

        $proposalhtml .= '<div class="" style="text-align: right">';
        $proposalhtml .= '<h4 class="mt-5"> ' . get_string('line', 'local_batching'). ' </h4>';
        $proposalhtml .= '<h4 class="">(' . get_string('branchname', 'local_batching') . ') </h4>';
        $proposalhtml .= '<div class="header_text">' . get_string('dateformat', 'local_batching') . ' </div>';
        $proposalhtml .= '</div>';
    }

    return $proposalhtml;
}

function get_proposal_log($batching, $proposalchangetype) {
    global $DB;

    $proposallogssql = '
                    SELECT * FROM {local_batching_proposal_logs}
                     WHERE batching = '.$batching.'
                    ';

    $proposallogssql .= ' AND status = "open" AND type = "' . $proposalchangetype . '" ';

    $poposallog = $DB->get_record_sql($proposallogssql);

    return $poposallog;
}

function add_record_in_quiz_section($quizid) {
    global $DB;

    $quizsection = new stdClass();
    $quizsection->quizid = $quizid;
    $quizsection->heading = '';
    $quizsection->firstslot = 1;
    $quizsection->shufflequestions = 0;

    $DB->insert_record('quiz_sections', $quizsection);
}

function check_record_exist_in_label_by_course_id($courseid) {
    global $DB;

    $recordexist = $DB->get_record('label', array('course' => $courseid));

    return $recordexist;
}

function add_record_in_label($labelobject) {
    global $DB;

    return $DB->insert_record('label', $labelobject);
}

function update_course_section_by_course_id($courseid) {
    global $DB;

    $sectiondetail = $DB->get_record('course_sections', array('course' => $courseid, 'section' => 0));
    if ($sectiondetail) {
        $sectionsequence = explode(',', $sectiondetail->sequence);
        array_shift($sectionsequence);

        $updatecoursesectionsql = <<<SQL_QUERY
                UPDATE {course_sections}
                SET name = :sectionname, sequence = :sequence
                WHERE section = :section AND course = :course
                SQL_QUERY;

        $params = [
            'sectionname' => get_string('trainingdetail', 'local_batching'),
            'section' => 0,
            'course' => $courseid,
            'sequence' => implode(',', $sectionsequence),
        ];
        $DB->execute($updatecoursesectionsql, $params);
    }
}