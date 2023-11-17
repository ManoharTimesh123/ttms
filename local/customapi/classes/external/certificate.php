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
 * Web Service functions for Custom API - certificate Information
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
use external_warnings;
use external_format_value;
use external_files;
use local_customapi\exception\customapiException;
use local_customapi\helper\certificatehelper;

/**
 * Course functions for Custom API.
 *
 */
class certificate extends external_api {

     /**
      * Certificate Module
      * The parameters for get certificates by course.
      *
      * @return external_function_parameters
      * @throws coding_exception
      */
    public static function get_certificates_by_course_parameters() {

        $courseids = [
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            ];

        return new external_function_parameters(
            [
                'courseids' => new external_single_structure($courseids),
            ]
        );
    }

    /**
     * The API can get certificates by course.
     *
     * @param $courseids
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_certificates_by_course($courseids) {
       
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_certificates_by_course_parameters(), ['courseids' => $courseids]);

        $courseids = $params['courseids'];
        
        $records = [];
        $warnings = [];

        try {

            $records[] = certificatehelper::read_certificates_by_course($courseids);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }
 
    /**
     * The return configuration for get certificates by course.
     *
     * @return external_single_structure
     */
    public static function get_certificates_by_course_returns() {
        return  new external_single_structure(
            array(
                'records' => new external_single_structure(
                    array(
                        'certificates' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                        'id' => new external_value(PARAM_INT, 'Certificate id'),
                                        'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                                        'course' => new external_value(PARAM_INT, 'Course id'),
                                        'name' => new external_value(PARAM_RAW, 'Certificate name'),
                                        'intro' => new external_value(PARAM_RAW, 'The Certificate intro', VALUE_OPTIONAL),
                                        'introformat' => new external_format_value('intro', VALUE_OPTIONAL),
                                        'requiredtimenotmet' => new external_value(PARAM_INT, 'Whether the time req is met', VALUE_OPTIONAL),
                                        'emailteachers' => new external_value(PARAM_INT, 'Email teachers?', VALUE_OPTIONAL),
                                        'emailothers' => new external_value(PARAM_RAW, 'Email others?', VALUE_OPTIONAL),
                                        'savecert' => new external_value(PARAM_INT, 'Save certificate?', VALUE_OPTIONAL),
                                        'reportcert' => new external_value(PARAM_INT, 'Report certificate?', VALUE_OPTIONAL),
                                        'delivery' => new external_value(PARAM_INT, 'Delivery options', VALUE_OPTIONAL),
                                        'requiredtime' => new external_value(PARAM_INT, 'Required time', VALUE_OPTIONAL),
                                        'certificatetype' => new external_value(PARAM_RAW, 'Type', VALUE_OPTIONAL),
                                        'orientation' => new external_value(PARAM_ALPHANUM, 'Orientation', VALUE_OPTIONAL),
                                        'borderstyle' => new external_value(PARAM_RAW, 'Border style', VALUE_OPTIONAL),
                                        'bordercolor' => new external_value(PARAM_RAW, 'Border color', VALUE_OPTIONAL),
                                        'printwmark' => new external_value(PARAM_RAW, 'Print water mark?', VALUE_OPTIONAL),
                                        'printdate' => new external_value(PARAM_RAW, 'Print date?', VALUE_OPTIONAL),
                                        'datefmt' => new external_value(PARAM_INT, 'Date format', VALUE_OPTIONAL),
                                        'printnumber' => new external_value(PARAM_INT, 'Print number?', VALUE_OPTIONAL),
                                        'printgrade' => new external_value(PARAM_INT, 'Print grade?', VALUE_OPTIONAL),
                                        'gradefmt' => new external_value(PARAM_INT, 'Grade format', VALUE_OPTIONAL),
                                        'printoutcome' => new external_value(PARAM_INT, 'Print outcome?', VALUE_OPTIONAL),
                                        'printhours' => new external_value(PARAM_TEXT, 'Print hours?', VALUE_OPTIONAL),
                                        'printteacher' => new external_value(PARAM_INT, 'Print teacher?', VALUE_OPTIONAL),
                                        'customtext' => new external_value(PARAM_RAW, 'Custom text', VALUE_OPTIONAL),
                                        'printsignature' => new external_value(PARAM_RAW, 'Print signature?', VALUE_OPTIONAL),
                                        'printseal' => new external_value(PARAM_RAW, 'Print seal?', VALUE_OPTIONAL),
                                        'timecreated' => new external_value(PARAM_INT, 'Time created', VALUE_OPTIONAL),
                                        'timemodified' => new external_value(PARAM_INT, 'Time modified', VALUE_OPTIONAL),
                                        'section' => new external_value(PARAM_INT, 'course section id', VALUE_OPTIONAL),
                                        'visible' => new external_value(PARAM_INT, 'visible', VALUE_OPTIONAL),
                                        'groupmode' => new external_value(PARAM_INT, 'group mode', VALUE_OPTIONAL),
                                        'groupingid' => new external_value(PARAM_INT, 'group id', VALUE_OPTIONAL),
                                ), 'Tool'
                            )
                        ),
                        'warnings' => new external_warnings(),
                    )
                )
            )
        );
    }

    /**
     * The parameters for get view certificate.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_view_certificate_parameters() {

        $certificateid = [
                'certificateid' => new external_value(PARAM_INT, 'certificate instance id')
            ];

        return new external_function_parameters(
            [
                'certificateinfo' => new external_single_structure($certificateid),
            ]
        );
    }

    /**
     * The API can get view certificate.
     *
     * @param $certificateinfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_view_certificate($certificateinfo) {
       
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_view_certificate_parameters(), ['certificateinfo' => $certificateinfo]);

        $certificateinfo = $params['certificateinfo'];
        
        $records = [];
        $warnings = [];

        try {

            $records[] = certificatehelper::read_view_certificate($certificateinfo);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }
 
    /**
     * The return configuration for get view certificate.
     *
     * @return external_single_structure
     */
    public static function get_view_certificate_returns() {
        return  new external_single_structure(
            array(
            'records' => new external_single_structure(
                    array(
                        'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                        'warnings' => new external_warnings()
                    )
                )
            )
        );
    }

     /**
      * The parameters for get issue certificate.
      *
      * @return external_function_parameters
      * @throws coding_exception
      */
    public static function get_issue_certificate_parameters() {

         $certificateid = [
                'certificateid' => new external_value(PARAM_INT, 'certificate instance id')
            ];

         return new external_function_parameters(
            [
                'certificateinfo' => new external_single_structure($certificateid),
            ]
         );
    }

    /**
     * The API can get issue certificate
     *
     * @param $certificateinfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_issue_certificate($certificateinfo) {
      
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_issue_certificate_parameters(), ['certificateinfo' => $certificateinfo]);

        $certificateinfo = $params['certificateinfo'];
        
        $records = [];
        $warnings = [];

        try {

            $records[] = certificatehelper::read_issue_certificate($certificateinfo);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
        
        return [
            'records' => $records[0]['records'],
        ];
    }
 
    /**
     * The return configuration for get issue certificate.
     *
     * @return external_single_structure
     */
    public static function get_issue_certificate_returns() {
        return  new external_single_structure(
            array(
            'records' => new external_single_structure(
                    array(
                        'issue' => self::certificate_issued_structure(),
                        'warnings' => new external_warnings()
                    )
                )
            )
        );
    }

    /**
     * The parameters for get issue certificate.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_all_issued_certificates_parameters() {

        $certificateid = [
                'certificateid' => new external_value(PARAM_INT, 'certificate instance id')
            ];

        return new external_function_parameters(
            [
                'certificateinfo' => new external_single_structure($certificateid),
            ]
        );
    }

    /**
     * The API can get issue certificate
     *
     * @param $certificateinfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_all_issued_certificates($certificateinfo) {
       
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_all_issued_certificates_parameters(), ['certificateinfo' => $certificateinfo]);

        $certificateinfo = $params['certificateinfo'];
        
        $records = [];
        $warnings = [];

        try {

            $records[] = certificatehelper::read_all_issued_certificates($certificateinfo);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }
 
    /**
     * The return configuration for get issue certificate.
     *
     * @return external_single_structure
     */
    public static function get_all_issued_certificates_returns() {
        return  new external_single_structure(
            array(
            'records' => new external_single_structure(
                    array(
                        'issues' => new external_multiple_structure(self::certificate_issued_structure()),
                        'warnings' => new external_warnings()
                    )
                )
            )
        );
    }

    /**
     * Returns a issued certificated structure
     *
     * @return external_single_structure External single structure
     */
    private static function certificate_issued_structure() {
        return new external_single_structure(
            array(
            'id' => new external_value(PARAM_INT, 'Issue id'),
            'userid' => new external_value(PARAM_INT, 'User id'),
            'certificateid' => new external_value(PARAM_INT, 'Certificate id'),
            'code' => new external_value(PARAM_RAW, 'Certificate code'),
            'timecreated' => new external_value(PARAM_INT, 'Time created'),
            'filename' => new external_value(PARAM_FILE, 'Time created'),
            'fileurl' => new external_value(PARAM_URL, 'Time created'),
            'mimetype' => new external_value(PARAM_RAW, 'mime type'),
            'grade' => new external_value(PARAM_NOTAGS, 'Certificate grade', VALUE_OPTIONAL),
            )
        );
    }
}
