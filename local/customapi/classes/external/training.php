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
 * Web Service functions for Custom API - User training
 *
 * @package local_customapi
 */

namespace local_customapi\external;

require_once($CFG->dirroot . '/mod/quiz/classes/external.php');
use coding_exception;
use context_system;
use Exception;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use local_customapi\exception\customapiException;
use local_customapi\helper\traininghelper;

/**
 * Course functions for Custom API.
 *
 */
class training extends external_api {

    /**
     * The parameters for training get.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_training_parameters() {

        $coursefields = [
            'courseid' => new external_value(
                PARAM_INT,
                'Course id'
            ),
        ];

        return new external_function_parameters(
            [
                'training' => new external_single_structure($coursefields)
            ]
        );
    }

    /**
     * The API can get training.
     *
     * @param $taining
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_training($coursefields) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_training_parameters(), ['training' => $coursefields]);

        $course = $params['training'];
        
        $records = [];
        $warnings = [];

        try {

            $records[] = traininghelper::read_training($course);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * The return configuration for training_get.
     *
     * @return external_single_structure
     */
    public static function get_training_returns() {
        return  new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'The id of the section'),
                            'name' => new external_value(PARAM_RAW, 'The name of the section'),
                            'visible' => new external_value(PARAM_RAW, 'The visibility of the section'),
                            'summary' => new external_value(PARAM_RAW, 'The summary of the section'),
                            'modules' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'id' => new external_value(PARAM_INT, 'The ID of the module'),
                                        'name' => new external_value(PARAM_RAW, 'The name of the module'),
                                        'modname' => new external_value(PARAM_RAW, 'The modname of the module'),
                                        'modplural' => new external_value(PARAM_RAW, 'The modplural of the module'),
                                        'modicon' => new external_value(PARAM_RAW, 'The modicon of the module'),
                                    )
                                )
                            ),
                        )
                    )
                )
            )
        );
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function get_training_activity_parameters() {

        $cmid = [
            'cmid' => new external_value(PARAM_INT, 'The course module id'),
        ];

        return new external_function_parameters(
            [
                'trainingactivitycmid' => new external_single_structure($cmid)
            ]
        );
    }

    /**
     * The API can get training.
     *
     * @param $taining
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_training_activity($cmid) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());
        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_training_activity_parameters(), ['trainingactivitycmid' => $cmid]);

        $cmid = $params['trainingactivitycmid'];
        
        $records = [];
        $warnings = [];

        try {
            $records[] = traininghelper::read_training_activity($cmid);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
       
        return [
            'records' => $records[0]['records'],
        ];
    }
    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_training_activity_returns() {
        return new external_single_structure(
            array(
                'records' => new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'The course module id'),
                        'course' => new external_value(PARAM_INT, 'The course id'),
                        'module' => new external_value(PARAM_INT, 'The module type id'),
                        'name' => new external_value(PARAM_RAW, 'The activity name'),
                        'modname' => new external_value(PARAM_COMPONENT, 'The module component name (forum, assign, etc..)'),
                        'instance' => new external_value(PARAM_INT, 'The activity instance id'),
                        'section' => new external_value(PARAM_INT, 'The module section id'),
                        'sectionnum' => new external_value(PARAM_INT, 'The module section number'),
                        'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                        'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                        'completion' => new external_value(PARAM_INT, 'If completion is enabled'),
                        'idnumber' => new external_value(PARAM_RAW, 'Module id number', VALUE_OPTIONAL),
                        'added' => new external_value(PARAM_INT, 'Time added', VALUE_OPTIONAL),
                        'score' => new external_value(PARAM_INT, 'Score', VALUE_OPTIONAL),
                        'indent' => new external_value(PARAM_INT, 'Indentation', VALUE_OPTIONAL),
                        'visible' => new external_value(PARAM_INT, 'If visible', VALUE_OPTIONAL),
                        'visibleoncoursepage' => new external_value(PARAM_INT, 'If visible on course page', VALUE_OPTIONAL),
                        'visibleold' => new external_value(PARAM_INT, 'Visible old', VALUE_OPTIONAL),
                        'completiongradeitemnumber' => new external_value(PARAM_INT, 'Completion grade item', VALUE_OPTIONAL),
                        'completionview' => new external_value(PARAM_INT, 'Completion view setting', VALUE_OPTIONAL),
                        'completionexpected' => new external_value(PARAM_INT, 'Completion time expected', VALUE_OPTIONAL),
                        'showdescription' => new external_value(PARAM_INT, 'If the description is showed', VALUE_OPTIONAL),
                        'availability' => new external_value(PARAM_RAW, 'Availability settings', VALUE_OPTIONAL),
                        'grade' => new external_value(PARAM_FLOAT, 'Grade (max value or scale id)', VALUE_OPTIONAL),
                        'scale' => new external_value(PARAM_TEXT, 'Scale items (if used)', VALUE_OPTIONAL),
                        'gradepass' => new external_value(PARAM_RAW, 'Grade to pass (float)', VALUE_OPTIONAL),
                        'gradecat' => new external_value(PARAM_INT, 'Grade category', VALUE_OPTIONAL),
                        'advancedgrading' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'area' => new external_value(PARAM_AREA, 'Gradable area name'),
                                    'method' => new external_value(PARAM_COMPONENT, 'Grading method'),
                                )
                            ),
                            'Advanced grading settings', VALUE_OPTIONAL
                        ),
                        'outcomes' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_ALPHANUMEXT, 'Outcome id'),
                                    'name' => new external_value(PARAM_RAW, 'Outcome full name'),
                                    'scale' => new external_value(PARAM_TEXT, 'Scale items')
                                )
                            ),
                            'Outcomes information', VALUE_OPTIONAL
                        ),
                    )
                ),
                'warnings' => new external_warnings()
            )
        );
    }
     
}
