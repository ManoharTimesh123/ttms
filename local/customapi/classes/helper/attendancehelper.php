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

namespace local_customapi\helper;
require_once($CFG->dirroot . '/mod/attendance/externallib.php');
use local_customapi\exception\customapiException;
use stdClass;
use Throwable;
use mod_attendance_external;


require_once($CFG->dirroot . '/course/externallib.php');

class attendancehelper {

    public static function read_user_courses_with_today_sessions() {
        global $USER;

        $userid = $USER->id;

        $usersession = \mod_attendance_external::get_courses_with_today_sessions($userid);

        return [
            'records' => $usersession,
        ];
    }

    public static function read_session_information($sessioninfo) {
        global $USER;

        $sessionid = $sessioninfo['sessionid'];

        $sessioninfo = \mod_attendance_external::get_session($sessionid);

        return [
            'records' => $sessioninfo,
        ];
    }
    
    public static function read_update_user_status($sessioninfo) {
        global $USER;
       
        $sessionid = $sessioninfo['sessionid'];
        $studentid = $USER->id;
        $takenbyid = $sessioninfo['takenbyid'];
        $statusid = $sessioninfo['statusid'];
        $statusset = $sessioninfo['statusset'];


        $usersessionstatusinfo = \mod_attendance_external::update_user_status($sessionid, $studentid, $takenbyid, $statusid, $statusset);

        return [
            'records' => $usersessionstatusinfo,
        ];
    }
//
    public static function read_user_session_information($userinfo) {
        global $DB;
        $sqlquery = <<<SQL_QUERY
                    SELECT attedancesession.id as sessionid FROM {groups_members} groupmembers
                    JOIN {attendance_sessions} attedancesession ON groupmembers.groupid = attedancesession.groupid
                    WHERE groupmembers.userid = :userid and attedancesession.attendanceid = :attendanceid;
                    SQL_QUERY;
        $params = [
            'userid' => $userinfo['userid'],
            'attendanceid' => $userinfo['attendanceid'],
        ];
        
        $usersessioninfo = $DB->get_record_sql($sqlquery, $params);
        $data = array('sessionid' => $usersessioninfo->sessionid);
      
         return [
            'records' => $data,
        ];
        
    }
}
