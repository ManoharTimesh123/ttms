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
 * Web Service functions for Custom API - attendance information
 *
 * @package local_customapi
 */

namespace local_customapi\external;

use coding_exception;
use context_system;
use Exception;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_customapi\exception\customapiException;
use local_customapi\helper\attendancehelper;

/**
 * Course functions for Custom API.
 *
 */
class attendance extends external_api {

    /**
     * Attendance Module
     * The parameters for get user today session in course.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_user_courses_with_today_sessions_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * The API can get user courses with today sessions.
     *
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_user_courses_with_today_sessions() {
      
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        
        $records = [];
        $warnings = [];

        try {

            $records[] = attendancehelper::read_user_courses_with_today_sessions();
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * The return configuration for get user courses with today sessions.
     *
     * @return external_single_structure
     */
    public static function get_user_courses_with_today_sessions_returns() {

         $todaysessions = self::get_user_session_structure();

        $attendanceinstances = array('name' => new external_value(PARAM_TEXT, 'Attendance name.'),
                                      'today_sessions' => new external_multiple_structure(
                                                          new external_single_structure($todaysessions)));

        $courses = array('shortname' => new external_value(PARAM_TEXT, 'short name of a moodle course.'),
                         'fullname' => new external_value(PARAM_TEXT, 'full name of a moodle course.'),
                         'attendance_instances' => new external_multiple_structure(
                                                   new external_single_structure($attendanceinstances)));
        return  new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                      $courses
                    )
                )
            )
        );
    }

     /**
      * The parameters for get session information.
      *
      * @return external_function_parameters
      * @throws coding_exception
      */
    public static function get_session_information_parameters() {

        $sessionid = [
            'sessionid' => new external_value(PARAM_INT, 'session id'),
        ];

        return new external_function_parameters(
            [
                'sessioninfo' => new external_single_structure($sessionid)
            ]
        );
    }

    /**
     * The API can get get session information.
     *
     * @param $sessioninfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_session_information($sessioninfo) {
     
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_session_information_parameters(), ['sessioninfo' => $sessioninfo]);

        $sessioninfo = $params['sessioninfo'];
        
        $records = [];
        $warnings = [];

        try {

            $records[] = attendancehelper::read_session_information($sessioninfo);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * The return configuration for get session information.
     *
     * @return external_single_structure
     */
    public static function get_session_information_returns() {

          $statuses = array('id' => new external_value(PARAM_INT, 'Status id.'),
                          'attendanceid' => new external_value(PARAM_INT, 'Attendance id.'),
                          'acronym' => new external_value(PARAM_TEXT, 'Status acronym.'),
                          'description' => new external_value(PARAM_RAW, 'Status description.'),
                          'grade' => new external_value(PARAM_FLOAT, 'Status grade.'),
                          'visible' => new external_value(PARAM_INT, 'Status visibility.'),
                          'deleted' => new external_value(PARAM_INT, 'informs if this session was deleted.'),
                          'setnumber' => new external_value(PARAM_INT, 'Set number.'));

          $users = array('id' => new external_value(PARAM_INT, 'User id.'),
                       'firstname' => new external_value(PARAM_TEXT, 'User first name.'),
                       'lastname' => new external_value(PARAM_TEXT, 'User last name.'));

          $attendancelog = array('studentid' => new external_value(PARAM_INT, 'Student id.'),
                                'statusid' => new external_value(PARAM_TEXT, 'Status id (last time).'),
                                'remarks' => new external_value(PARAM_TEXT, 'Last remark.'),
                                'id' => new external_value(PARAM_TEXT, 'log id.'));

          $session = self::get_user_session_structure();
          $session['courseid'] = new external_value(PARAM_INT, 'Course moodle id.');
          $session['statuses'] = new external_multiple_structure(new external_single_structure($statuses));
          $session['attendance_log'] = new external_multiple_structure(new external_single_structure($attendancelog));
          $session['users'] = new external_multiple_structure(new external_single_structure($users));

          return  new external_single_structure(
              array(
                  'records' => new external_single_structure($session),
              )
          );
    }

    /**
     * The parameters for get update user status.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_update_user_status_parameters() {

        $sessioninfo = [
                        'sessionid' => new external_value(PARAM_INT, 'Session id'),
                        'studentid' => new external_value(PARAM_INT, 'Student id'),
                        'takenbyid' => new external_value(PARAM_INT, 'Id of the user who took this session'),
                        'statusid' => new external_value(PARAM_INT, 'Status id'),
                        'statusset' => new external_value(PARAM_TEXT, 'Status set of session'),
                      ];

        return new external_function_parameters(
            [
                'sessioninfo' => new external_single_structure($sessioninfo)
            ]
        );
    }

    /**
     * The API can get update user status.
     *
     * @param $sessioninfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_update_user_status($sessioninfo) {
     
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_update_user_status_parameters(), ['sessioninfo' => $sessioninfo]);

        $sessioninfo = $params['sessioninfo'];
        
        $records = [];
        $warnings = [];

        try {

            $records[] = attendancehelper::read_update_user_status($sessioninfo);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * The return configuration for get update user status.
     *
     * @return external_single_structure
     */
    public static function get_update_user_status_returns() {
        return  new external_single_structure(
            array(
                'records' => new external_value(PARAM_TEXT, 'Http code'),
            )
        );
    }

    /**
     * Get structure of an attendance session.
     *
     * @return array
     */
    private static function get_user_session_structure() {
        $session = array('id' => new external_value(PARAM_INT, 'Session id.'),
                         'attendanceid' => new external_value(PARAM_INT, 'Attendance id.'),
                         'groupid' => new external_value(PARAM_INT, 'Group id.'),
                         'sessdate' => new external_value(PARAM_INT, 'Session date.'),
                         'duration' => new external_value(PARAM_INT, 'Session duration.'),
                         'lasttaken' => new external_value(PARAM_INT, 'Session last taken time.'),
                         'lasttakenby' => new external_value(PARAM_INT, 'ID of the last user that took this session.'),
                         'timemodified' => new external_value(PARAM_INT, 'Time modified.'),
                         'description' => new external_value(PARAM_RAW, 'Session description.'),
                         'descriptionformat' => new external_value(PARAM_INT, 'Session description format.'),
                         'studentscanmark' => new external_value(PARAM_INT, 'Students can mark their own presence.'),
                         'absenteereport' => new external_value(PARAM_INT, 'Session included in absetee reports.'),
                         'autoassignstatus' => new external_value(PARAM_INT, 'Automatically assign a status to students.'),
                         'preventsharedip' => new external_value(PARAM_INT, 'Prevent students from sharing IP addresses.'),
                         'preventsharediptime' => new external_value(PARAM_INT, 'Time delay before IP address is allowed again.'),
                         'statusset' => new external_value(PARAM_INT, 'Session statusset.'),
                         'includeqrcode' => new external_value(PARAM_INT, 'Include QR code when displaying password'));

        return $session;
    }
    /**
     * The parameters for get update user status.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_user_session_information_parameters() {

        $userinfo = [
                        'attendanceid' => new external_value(PARAM_INT, 'Attendance id'),
                        'userid' => new external_value(PARAM_INT, 'User id'),
                      ];

        return new external_function_parameters(
            [
                'userinfo' => new external_single_structure($userinfo)
            ]
        );
    }

    /**
     * The API can get user session.
     *
     * @param $userinfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_user_session_information($userinfo) {
     
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_user_session_information_parameters(), ['userinfo' => $userinfo]);

        $userinfo = $params['userinfo'];
        
        
        $records = [];
        $warnings = [];

        try {

            $records[] = attendancehelper::read_user_session_information($userinfo);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * The return configuration for get update user status.
     *
     * @return external_single_structure
     */
    public static function get_user_session_information_returns() {
        return  new external_single_structure(
            array(
                'records' => 
                    new external_single_structure(
                        array(
                            'sessionid' => new external_value(PARAM_INT, 'Session Id'),
                        )
                    )
                
            )
        );
    }
}
