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
 * @package    block_venue_details
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/venue_details/locallib.php');

function render_venue_details_grid($venuedetailfilter) {
    global $USER;

    $currenttimestamp = strtotime(date('Y-m-d'));
    $context = \context_system::instance();

    $table = new html_table();

    $tableheader = array(
        get_string('schoolname', 'block_venue_details'),
        get_string('batchingname', 'block_venue_details'),
        get_string('batchcode', 'block_venue_details'),
        get_string('cyclecode', 'block_venue_details'),
        get_string('starttime', 'block_venue_details'),
        get_string('endtime', 'block_venue_details'),
        get_string('participants', 'block_venue_details'),
        get_string('status', 'block_venue_details'),
    );

    $venuefilter = '<form action="" method="get" name="venue_detail_filter" id="venue_detail_filter">
                        <select name="venue_detail" class="form-control venue-detail-filter">
                        <option ' . (($venuedetailfilter == 'all') ? 'selected' : '') . ' value="all">All</option>
                        <option ' . (($venuedetailfilter == 'past') ? 'selected' : '') . ' value="past">Past</option>
                        <option ' . (($venuedetailfilter == 'present') ? 'selected' : '') . ' value="present">Present</option>
                        <option ' . (($venuedetailfilter == 'upcoming') ? 'selected' : '') . ' value="upcoming">Upcoming</option>
                        </select>
                    </form>';
    $venuedetailsdata = get_venue_details($venuedetailfilter);

    $table->head = $tableheader;
    $data = [];
    $output = '';

    if (!empty($venuedetailsdata)) {

        foreach ($venuedetailsdata as $key => $venuedetail) {

            $participantsperbatch = get_participant_per_batch($venuedetail->batch);

            if ($venuedetail->starttime < $currenttimestamp) {
                $venuestatusttype = '<span class="badge-danger p-2 rounded-pill">' . get_string('past', 'block_venue_details') . '</span>';
            }

            if ($venuedetail->starttime > $currenttimestamp) {
                $venuestatusttype = '<span class="badge-info p-2 rounded-pill">' . get_string('upcoming', 'block_venue_details') . '</span>';
            }

            if ($venuedetail->starttime == $currenttimestamp) {
                $venuestatusttype = '<span class="badge-success p-2 rounded-pill">' . get_string('present', 'block_venue_details') . '</span>';
            }

            $row = array();
            $id = $venuedetail->id;
            $row[] = $venuedetail->schoolname;
            $row[] = $venuedetail->batching;
            $row[] = $venuedetail->batchcode;
            $row[] = $venuedetail->cyclecode;
            $row[] = customdateformat('DATE_WITH_TIME', $venuedetail->starttime);
            $row[] = customdateformat('DATE_WITH_TIME', $venuedetail->endtime);
            $row[] = $participantsperbatch;
            $row[] = $venuestatusttype;
            $data[] = $row;
        }

        $table->data = $data;
        $table->id = 'venue-detail-list';
        $venuedetails = html_writer::table($table);
        $output .= '<div class="table-responsive">
                        '. $venuefilter. '
                        ' . $venuedetails . '
                    </div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#venue-detail-list').dataTable({
                                searching: false,
                                'bSort': false,
                                });
                            });
                        ");

    } else {
        $output = $venuefilter.'<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'block_venue_details') . '</div>';
    }
    return $output;

}
