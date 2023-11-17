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
 * @package local_announcement
 */

namespace announcement;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/user_management/locallib.php');

class local_announcement {

    public static function getannouncements($filters, $page = null) {
        global $DB, $CFG;

        $timestamp = strtotime(date('Y-m-d'));

        $announcementsql = <<<SQL_QUERY
                SELECT * FROM {local_announcements}
                WHERE deleted = 0
            SQL_QUERY;
        $systemcontext = \context_system::instance();

        if (!has_capability('local/announcement:edit', $systemcontext)) {
            $profiledetail = self::get_user_profile_detail_for_announcement();
            if (!empty($profiledetail)) {
                $announcementsql .= ' AND (`global` = 1';
                $announcementsql .= ' OR (CASE WHEN `global` = 0 THEN FIND_IN_SET(' . $profiledetail['userdetailzone'] . ', `zoneid`) END)';
                $announcementsql .= ' OR (CASE WHEN `global` = 0 THEN FIND_IN_SET(' . $profiledetail['userdetailschool'] . ', `schoolid`) END)';
                $announcementsql .= ' OR (CASE WHEN `global` = 0 THEN FIND_IN_SET(' . $profiledetail['userdetaildiet'] . ', `dietid`) END)';
                $announcementsql .= ' OR (CASE WHEN `global` = 0 THEN FIND_IN_SET(' . $profiledetail['userdetaildistrict'] . ', `districtid`) END)';
                $announcementsql .= ' ) ';

            }
        }

        if ($filters['current_announcement']) {
            $announcementsql .= ' AND startdate <= ' . $timestamp . '  AND enddate >= ' . $timestamp;
        }

        if (isset($filters['id'])) {
            $announcementsql .= ' AND id =  ' . $filters['id'];
        }

        $announcementsql .= ' ORDER BY id DESC';

        $datacount = count($DB->get_records_sql($announcementsql));

        if ($page > -1) {
            $offset = $CFG->itemperpage;
            $limit = ($page + 1 - 1) * $offset;

            $announcementsql .= ' LIMIT ' . $limit . ', ' . $offset;
        }

        $data = $DB->get_records_sql($announcementsql);

        $announcements = [
            'data' => $data,
            'count' => $datacount
        ];

        return $announcements;
    }

    public static function getimageurl($announcement, $contextid) {
        global $CFG;

        $announcementid = $announcement->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'local_announcement', 'attachment', $announcementid, 'sortorder', false);
        $file = reset($files);

        if (!empty($file) && !empty($file->get_filename())) {
            $imagename = $file->get_filename();
            if ($imagename <> '.') {
                $imageurl = $CFG->wwwroot . "/pluginfile.php/" . $contextid . "/local_announcement/attachment/" .
                $announcementid . '/' . $imagename;
            }
        } else {
            $imageurl = $CFG->wwwroot . '/theme/edumy/pix/default-image.jpg';
        }

        return $imageurl;
    }

    public static function get_user_profile_detail_for_announcement() {
        global $USER;

        $userdetails = get_user_profile_details($USER, ['details', 'hierarchy']);

        foreach ($userdetails[$USER->id]->hierarchy_details as $hierarchydetail) {
            $userdetailzone = $hierarchydetail->zone_id;
            $userdetailschool = $hierarchydetail->id;
            $userdetaildiet = $hierarchydetail->diet;
            $userdetaildistrict = $hierarchydetail->district_id;
        }
        if (!empty($userdetails[$USER->id]->hierarchy_details)) {
            $userdetail = [
                'userdetailzone' => $userdetailzone,
                'userdetailschool' => $userdetailschool,
                'userdetaildiet' => $userdetaildiet,
                'userdetaildistrict' => $userdetaildistrict,
            ];

            return $userdetail;
        }

        return [];
    }

    public static function get_announcement_status($announcement) {
        $currentdate = date('Y-m-d');
        $announcementstatus = [];

        if (date('Y-m-d', $announcement->enddate) < $currentdate) {
            $announcementstatus['announcementstatustype'] = get_string('past', 'local_announcement');
            $announcementstatus['statusclass'] = 'past';
        }

        if (date('Y-m-d', $announcement->startdate) > $currentdate) {
            $announcementstatus['announcementstatustype'] = get_string('upcoming', 'local_announcement');
            $announcementstatus['statusclass'] = 'upcoming';
        }

        if (date('Y-m-d', $announcement->startdate) <= $currentdate && date('Y-m-d', $announcement->enddate) > $currentdate) {
            $announcementstatus['announcementstatustype'] = get_string('ongoing', 'local_announcement');
            $announcementstatus['statusclass'] = 'ongoing';
        }

        return $announcementstatus;
    }

}
