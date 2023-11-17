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
 * Web Service functions for Custom API - Mooc courses
 *
 * @package local_customapi
 */

namespace local_customapi\external;


use coding_exception;
use Exception;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use core_course\external\course_summary_exporter;
use local_customapi\exception\customapiException;
use local_customapi\helper\mooccourseshelper;

/**
 * Mooc courses functions for Custom API.
 *
 */
class mooccourses extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function get_all_user_mooc_courses_parameters() {
         return new external_function_parameters(
            array(
                'classification' => new external_value(PARAM_ALPHA, 'future, inprogress, or past'),
                'limit' => new external_value(PARAM_INT, 'Result set limit', VALUE_DEFAULT, 0),
                'offset' => new external_value(PARAM_INT, 'Result set offset', VALUE_DEFAULT, 0),
                'sort' => new external_value(PARAM_TEXT, 'Sort string', VALUE_DEFAULT, null),
                'customfieldname' => new external_value(PARAM_ALPHANUMEXT, 'Used when classification = customfield',
                    VALUE_DEFAULT, null),
                'customfieldvalue' => new external_value(PARAM_RAW, 'Used when classification = customfield',
                    VALUE_DEFAULT, null),
            )
        );
    }

    /**
     * The API can get all user mooc courses.
     *
     * @param $classification
     * @param $limit
     * @param $offset
     * @param $sort
     * @param $customfieldname
     * @param $customfieldvalue
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_all_user_mooc_courses($classification, $limit = 0, $offset = 0, $sort = null, $customfieldname = null, $customfieldvalue = null) {

        global $CFG, $PAGE, $USER;
        require_once($CFG->dirroot . '/course/lib.php');
     
         $params = self::validate_parameters(self::get_all_user_mooc_courses_parameters(),
            array(
                'classification' => $classification,
                'limit' => $limit,
                'offset' => $offset,
                'sort' => $sort,
                'customfieldvalue' => $customfieldvalue,
            )
        );

        $classification = $params['classification'];
        $limit = $params['limit'];
        $offset = $params['offset'];
        $sort = $params['sort'];
        $customfieldvalue = $params['customfieldvalue'];

        $records = [];
        $warnings = [];
        try {
            $records[] = mooccourseshelper::read_get_user_mooc_courses($classification, $limit, $offset, $sort, $customfieldvalue);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return  $records[0]['records'];
       
    }

    /**
     * Describes get all user mooc courses.
     *
     * @return external_single_structure
     * 
     */
    public static function get_all_user_mooc_courses_returns() {
       return new external_single_structure(
            array(
                'courses' => new external_multiple_structure(course_summary_exporter::get_read_structure(), 'Course'),
                'nextoffset' => new external_value(PARAM_INT, 'Offset for the next request')
            )
        );
    }
}

    
