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
 * The News Management
 *
 * @package    local_news
 */

namespace news;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/user_management/locallib.php');

class local_news {
    public static function getnews($filters, $page = null) {
        global $DB, $CFG;

        $timestamp = strtotime(date('Y-m-d'));
        $systemcontext = \context_system::instance();

        $userschoolid = self::get_current_user_school_id();

        $newssql = <<<SQL_QUERY
            SELECT n.*, s.name schoolname FROM {local_news} n
            JOIN {local_schools} s ON s.id = n.schoolid
            WHERE deleted = 0
        SQL_QUERY;

        // If not site admin and does not have permission to manage then only show school news.
        if (!has_capability('local/news:manage', $systemcontext) && !is_siteadmin()) {
            $newssql .= ' AND schoolid =  ' . $userschoolid;
        }

        if (isset($filters['status'])) {
            $newssql .= ' AND approved = ' . $filters['status'];
        }

        if ($filters['current_news']) {
            $newssql .= ' AND datefrom <= ' . $timestamp . '  AND dateto >= ' . $timestamp;
        }

        if (isset($filters['id'])) {
            $newssql .= ' AND n.id =  ' . $filters['id'];
        }

        $newssql .= ' ORDER BY id DESC';

        $datacount = count($DB->get_records_sql($newssql));

        if ($page > -1) {
            $offset = $CFG->itemperpage;
            $limit = ($page + 1 - 1) * $offset;

            $newssql .= ' LIMIT ' . $limit . ', ' . $offset;
        }

        $data = $DB->get_records_sql($newssql);

        $news = [
            'data' => $data,
            'count' => $datacount
        ];

        return $news;
    }

    public static function getimageurl($news, $contextid) {
        global $CFG;

        $newsid = $news->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'local_news', 'attachment', $newsid, 'sortorder', false);
        $file = reset($files);

        if (!empty($file) && !empty($file->get_filename())) {
            $imagename = $file->get_filename();
            if ($imagename <> '.') {
                $imageurl = $CFG->wwwroot . "/pluginfile.php/" . $contextid . "/local_news/attachment/" . $newsid. '/' . $imagename;
            } else {
                $imageurl = '';
            }
        } else {
            $imageurl = $CFG->wwwroot . '/theme/edumy/pix/default-image.jpg';
        }

        return $imageurl;
    }

    public static function get_current_user_school_id() {
        global $USER;

        $userdetails = get_user_profile_details($USER, ['details', 'hierarchy']);

        if (!empty($userdetails[$USER->id]->hierarchy_details)) {
            foreach ($userdetails[$USER->id]->hierarchy_details as $hierarchydetail) {
                $userdetailschoolid = $hierarchydetail->id;
            }
            return $userdetailschoolid;
        }

        return 0;
    }

    public static function get_news_status($news) {
        $currentdate = date('Y-m-d');
        $newsstatus = [];

        if (date('Y-m-d', $news->dateto) < $currentdate) {
            $newsstatus['newsstatustype'] = get_string('past', 'local_news');
            $newsstatus['statusclass'] = 'past';
        }

        if (date('Y-m-d', $news->datefrom) > $currentdate) {
            $newsstatus['newsstatustype'] = get_string('upcoming', 'local_news');
            $newsstatus['statusclass'] = 'upcoming';
        }

        if (date('Y-m-d', $news->datefrom) <= $currentdate && date('Y-m-d', $news->dateto) > $currentdate) {
            $newsstatus['newsstatustype'] = get_string('ongoing', 'local_news');
            $newsstatus['statusclass'] = 'ongoing';
        }

        return $newsstatus;
    }
}
