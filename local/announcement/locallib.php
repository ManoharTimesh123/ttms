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
 * Announcement
 *
 * @package    local_announcement
 */


function add_announcement($data) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $postdata = new stdClass();
    $timestamp = time();
    $postdata->title = strip_tags($data->title);
    $postdata->description = $data->description['text'];
    $postdata->startdate = $data->datefrom;
    $postdata->enddate = $data->dateto;
    $postdata->global = $data->global;
    if (isset($data->districts) && !empty($data->districts)) {
        $postdata->districtid = implode(",", $data->districts);
    }
    if (isset($data->diets) && !empty($data->diets)) {
        $postdata->dietid = implode(",", $data->diets);
    }
    if (isset($data->zones) && !empty($data->zones)) {
        $postdata->zoneid = implode(",", $data->zones);
    }
    if (isset($data->schools) && !empty($data->schools)) {
        $postdata->schoolid = implode(",", $data->schools);
    }
    if ($data->id > 0) {

        $announcement = $DB->get_record('local_announcements', array('id' => $data->id));
        $postdata->id = $announcement->id;
        $postdata->timemodified = $timestamp;
        $postdata->updatedby = $loggedinuserid;
        $DB->update_record('local_announcements', $postdata);

        return $announcement->id;
    } else {
        $postdata->timecreated = $timestamp;
        $postdata->timemodified = $timestamp;
        $postdata->createdby = $loggedinuserid;
        $postdata->updatedby = $loggedinuserid;
        $announcementid = $DB->insert_record('local_announcements', $postdata);

        return $announcementid;
    }

}

function delete_announcement($id) {
    global $DB, $USER;

    $announcement = $DB->get_record('local_announcements', array('id' => $id));
    $postdata = new stdClass();
    $postdata->id = $announcement->id;
    $postdata->deleted = 1;
    $postdata->timedeleted = time();
    $postdata->deletedby = $USER->id;

    return $DB->update_record('local_announcements', $postdata);
}

