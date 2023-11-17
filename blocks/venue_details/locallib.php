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
 * Venue Details
 *
 * @package    block_venue_details
 */

function get_venue_details($venuedetailfilter) {
    global $DB, $USER;

    $currenttimestamp = strtotime(customdateformat('DATE_WITHOUT_TIME'));

    $venuedetailssql = "
                    SELECT vf.*, b.name batching, s.name schoolname, bb.code batchcode,bc.code cyclecode,bct.starttime,bct.endtime
                    FROM lms_local_batching_venue_final vf
                    JOIN lms_local_batching_venue bv on bv.id = vf.batchingvenueid
                    JOIN lms_local_schools s on s.id = bv.school
                    JOIN lms_local_batching_batches bb on bb.id = vf.batch
                    JOIN lms_local_batching b on b.id = bb.batching
                    JOIN lms_local_batching_cycles bc on bc.id = bb.cycle
                    JOIN lms_local_batching_cycle_times bct on bct.cycle = bb.cycle";

    if ($venuedetailfilter == 'upcoming') {
        $venuedetailssql .= ' WHERE bct.starttime > '.$currenttimestamp.' ';
    }

    if ($venuedetailfilter == 'present') {
        $venuedetailssql .= ' AND bct.starttime = '.$currenttimestamp.' ';
    }

    if ($venuedetailfilter == 'past') {
        $venuedetailssql .= ' AND bct.starttime < '.$currenttimestamp.' ';
    }

    if (!is_siteadmin()) {
        $venuedetailssql .= ' AND hos = '.$USER->id.' ';
    }

    $venuedetails = $DB->get_records_sql($venuedetailssql);

    return $venuedetails;
}

function get_participant_per_batch($batch) {
    global $DB;

    $countparticepantss = $DB->count_records('local_batching_participants', array('batch' => $batch));

    return $countparticepantss;
}
