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
 * @package    block_venue_requests
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/venue_requests/locallib.php');

function render_venue_approval_requests_grid() {
    global $CFG, $USER;

    $loggedinuserid = $USER->id;
    $context = \context_system::instance();
    $table = new html_table();

    $tableheader = array(
        get_string('schoolname', 'block_venue_requests'),
        get_string('batchingname', 'block_venue_requests'),
        get_string('requeststatus', 'block_venue_requests'),
        get_string('requestaction', 'block_venue_requests')
    );

    $venueapprovalrequests = get_venue_required_approval();
    $table->head = $tableheader;
    $data = [];
    $output = '';
    if (!empty($venueapprovalrequests)) {
        foreach ($venueapprovalrequests as $key => $venuerequest) {
            $row = array();
            $id = $venuerequest->id;
            $row[] = $venuerequest->school;
            $row[] = $venuerequest->batching;
            if ($venuerequest->status == 'Approved') {
                $status = '<i class="fa fa-thumbs-up me-2 text-success" title="Approved"></i>';
                $changestatus = '<span class="badge-success p-2 rounded-pill">' . get_string('approved', 'block_venue_requests') . '</span>';
            } else {
                $status = '<i class="fa fa-thumbs-down me-2 text-danger" title="Unapproved"></i>';
                $changestatus = '<a id="' . $id . '" class="update-venue-request">
                                    <span style="cursor:pointer;" class="badge-primary p-2 rounded-pill">
                                    ' . get_string('approve', 'block_venue_requests') . '
                                    </span>
                                </a>';
            }
            $row[] = $status;
            $row[] = $changestatus;
            $data[] = $row;
        }
        $table->data = $data;
        $table->id = 'venue-approval-list';
        $venuerequiredapprovals = html_writer::table($table);
        $output .= '<div class="table-responsive">' . $venuerequiredapprovals . '</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#venue-approval-list').dataTable({
                                    'bSort': false,
                                });
                            });
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'block_venue_requests') . '</div>';
    }
    return $output;
}

