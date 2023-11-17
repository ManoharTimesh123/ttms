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
 * Profile Manaagement
 *
 * @package    local_profile_management
 */

function add_updated_user_profile_field($data, $ajaxdata = false) {
    global $USER;

    $userid = $data->id;
    $changedbyuserid = $USER->id;

    $userdetails = get_user_detail($userid);

    $currenttimestamp = time();

    if ($userdetails && $userid > 2) {

        // Add user jobtype if updated.
        if ($userdetails->Jobtype == 'contract') {
            $latestrecordsql = get_latest_record_by_changed_item('Jobtype', $userid);

            $timecreated = (!empty($latestrecordsql)) ? $latestrecordsql->todate : $userdetails->timecreated;

            $userprofile = new stdClass();
            $userprofile->userid = $userid;
            $userprofile->changeditem = 'Jobtype';
            $userprofile->itemvalue = $userdetails->jobtype;
            $userprofile->fromdate = $timecreated;
            $userprofile->todate = $currenttimestamp;
            $userprofile->usermodified = $changedbyuserid;
            $userprofile->timecreated = $currenttimestamp;
            add_profile_field_change_record($userprofile);
        }

        // Add user grade if updated.
        if ($data->grade != $userdetails->grade) {
            $latestrecordsql = get_latest_record_by_changed_item('Grade', $userid);

            $timecreated = (!empty($latestrecordsql)) ? $latestrecordsql->todate : $userdetails->timecreated;

            $userprofile = new stdClass();
            $userprofile->userid = $userid;
            $userprofile->changeditem = 'Grade';
            $userprofile->itemvalue = $userdetails->grade;
            $userprofile->fromdate = $timecreated;
            $userprofile->todate = $currenttimestamp;
            $userprofile->usermodified = $changedbyuserid;
            $userprofile->timecreated = $currenttimestamp;
            add_profile_field_change_record($userprofile);
        }

        // Add user post if updated.
        if ($data->post != $userdetails->post) {
            $latestrecordsql = get_latest_record_by_changed_item('Post', $userid);

            $timecreated = (!empty($latestrecordsql)) ? $latestrecordsql->todate : $userdetails->timecreated;

            $userprofile = new stdClass();
            $userprofile->userid = $userid;
            $userprofile->changeditem = 'Post';
            $userprofile->itemvalue = $userdetails->post;
            $userprofile->fromdate = $timecreated;
            $userprofile->todate = $currenttimestamp;
            $userprofile->usermodified = $changedbyuserid;
            $userprofile->timecreated = $currenttimestamp;
            add_profile_field_change_record($userprofile);
        }

        // Add user school if updated.
        if ($data->schoolid != $userdetails->schoolid) {
            $latestrecordsql = get_latest_record_by_changed_item('School', $userid);

            $timecreated = (!empty($latestrecordsql)) ? $latestrecordsql->todate : $userdetails->timecreated;

            $userprofile = new stdClass();
            $userprofile->userid = $userid;
            $userprofile->changeditem = 'School';
            $userprofile->itemvalue = $userdetails->schoolid;
            $userprofile->fromdate = $timecreated;
            $userprofile->todate = $currenttimestamp;
            $userprofile->usermodified = $changedbyuserid;
            $userprofile->timecreated = $currenttimestamp;
            add_profile_field_change_record($userprofile);
        }

        // Add user position if updated.
        if ($userdetails->position  && !empty($data->position)) {
            $latestrecordsql = get_latest_record_by_changed_item('Position', $userid);

            $timecreated = (!empty($latestrecordsql)) ? $latestrecordsql->todate : $userdetails->timecreated;

            foreach (explode(',', $userdetails->position) as $position) {
                if (!in_array($position, $data->position)) {
                    $userprofile = new stdClass();
                    $userprofile->userid = $userid;
                    $userprofile->changeditem = 'Position';
                    $userprofile->itemvalue = $position;
                    $userprofile->fromdate = $timecreated;
                    $userprofile->todate = $currenttimestamp;
                    $userprofile->usermodified = $changedbyuserid;
                    $userprofile->timecreated = $currenttimestamp;
                    add_profile_field_change_record($userprofile);
                }
            }
        }

        // Add user department if updated.
        if ($userdetails->department && !empty($data->custom_department)) {
            $latestrecordsql = get_latest_record_by_changed_item('Department', $userid);

            $timecreated = (!empty($latestrecordsql)) ? $latestrecordsql->todate : $userdetails->timecreated;

            foreach (explode(',', $userdetails->department) as $department) {
                if (!in_array($department, $data->custom_department)) {
                    $userprofile = new stdClass();
                    $userprofile->userid = $userid;
                    $userprofile->changeditem = 'Department';
                    $userprofile->itemvalue = $department;
                    $userprofile->fromdate = $timecreated;
                    $userprofile->todate = $currenttimestamp;
                    $userprofile->usermodified = $changedbyuserid;
                    $userprofile->timecreated = $currenttimestamp;
                    add_profile_field_change_record($userprofile);
                }
            }
        }
    }
}

function add_profile_field_change_record($profilefield) {
    global $DB;

    return $DB->insert_record('local_user_profile_details', $profilefield);
}

function get_latest_record_by_changed_item($itemname, $userid) {
    global $DB;

    $latestrecordsql = <<<SQL_QUERY
                SELECT * FROM {local_user_profile_details}
                WHERE userid = $userid
                AND changeditem = '$itemname'
                ORDER BY  id DESC
            SQL_QUERY;

    return $DB->get_record_sql($latestrecordsql);
}

function get_user_detail($userid) {
    global $DB;

    return $DB->get_record('local_user_details', array('userid' => $userid));
}
