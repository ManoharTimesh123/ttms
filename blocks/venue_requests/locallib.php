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
 * Venue Requests
 *
 * @package    block_venue_requests
 */

function get_venue_required_approval() {
    global $DB, $USER;

    if (is_siteadmin()) {

        $venueapprovalrequestsql = <<<SQL
                    SELECT bv.*, b.name batching, s.name school FROM {local_batching_venue} bv
                    LEFT JOIN {local_schools} s ON s.id = bv.school
                    LEFT JOIN {local_batching} b ON b.id = bv.batching
                    WHERE bv.status = 'Approved'
                    ORDER BY bv.id DESC
                    SQL;

        $approvalrequests = $DB->get_records_sql($venueapprovalrequestsql);

    } else {

        $venueapprovalrequestsql = <<<SQL
                    SELECT bv.*, b.name batching, s.name school FROM {local_batching_venue} bv
                    LEFT JOIN {local_schools} s ON s.id = bv.school
                    LEFT JOIN {local_batching} b ON b.id = bv.batching
                    WHERE s.hos = :hos
                    ORDER BY bv.id DESC
                    SQL;

        $param = [
            'hos' => $USER->id
        ];

        $approvalrequests = $DB->get_records_sql($venueapprovalrequestsql, $param);
    }

    return $approvalrequests;
}

function update_venue_request_status($venuerequestid) {
    global $DB;

    $venuerequest = new stdClass();
    $venuerequest->id = $venuerequestid;
    $venuerequest->status = get_string('approved', 'block_venue_requests');
    if ($DB->update_record('local_batching_venue', $venuerequest)) {
        return true;
    } else {
        return false;
    }
}
