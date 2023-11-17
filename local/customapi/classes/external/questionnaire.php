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
 * Web Service functions for Custom API - questionnaire information
 *
 * @package local_customapi
 */

namespace local_customapi\external;

require_once($CFG->dirroot . '/course/externallib.php');
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
use local_customapi\exception\customapiException;
use local_customapi\helper\questionnairehelper;

/**
 * Course functions for Custom API.
 *
 */
class questionnaire extends external_api {

    /**
     * Questionnaire feedback module
     * The parameters for get submit questionnaire responses.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
      
    public static function get_questionnaire_responses_parameters() {

        $questionnaireinfo = [
                'questionnaireid' => new external_value(PARAM_INT, 'Questionnaire instance id'),
                'surveyid' => new external_value(PARAM_INT, 'Survey id'),
                'cmid' => new external_value(PARAM_INT, 'Course module id'),
                'sec' => new external_value(PARAM_INT, 'Section number'),
                'completed' => new external_value(PARAM_INT, 'Completed survey or not'),
                'rid' => new external_value(PARAM_INT, 'Existing response id'),
                'submit' => new external_value(PARAM_INT, 'Submit survey or not'),
                'action' => new external_value(PARAM_ALPHA, 'Page action'),
                'responses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'name' => new external_value(PARAM_RAW, 'data key'),
                            'value' => new external_value(PARAM_RAW, 'data value')
                        ]
                    ),
                    'The data to be saved', VALUE_DEFAULT, []
                )
            ];

        return new external_function_parameters(
            [
                'questionnaireinfo' => new external_single_structure($questionnaireinfo),
            ]
        );
    }

    /**
     * The API can get added questionnaire responses.
     *
     * @param $questionnaireinfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */

    public static function get_questionnaire_responses($questionnaireinfo) {

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.

        $params = self::validate_parameters(self::get_questionnaire_responses_parameters(), ['questionnaireinfo' => $questionnaireinfo]);

        $questionnaireinfo = $params['questionnaireinfo'];
        $records = [];
        $warnings = [];

        try {

            $records[] = questionnairehelper::add_user_questionnaire_responses($questionnaireinfo);
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

    public static function get_questionnaire_responses_returns() {

        return  new external_single_structure(
            array(
                'records' => new external_single_structure(
                        array(
                            'submitted' => new \external_value(PARAM_BOOL, 'submitted', true, false, false),
                            'warnings' => new \external_warnings()
                        )
                    )
            )
        );
    }

     /**
      * Questionnaire view module
      * The parameters for get submit questionnaire responses.
      *
      * @return external_function_parameters
      * @throws coding_exception
      */

    public static function get_questionnaire_view_parameters() {

        $questionnaireinfo = [
                    'questionnaireid' => new external_value(PARAM_INT, 'Questionnaire instance id'),
                    'cmid' => new external_value(PARAM_INT, 'Course module id'),
                ];

        return new external_function_parameters(
            [
                'questionnaireinfo' => new external_single_structure($questionnaireinfo),
            ]
        );
    }

    /**
     * The API can get questionnaire view.
     *
     * @param $questionnaireinfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_questionnaire_view($questionnaireinfo) {
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.

        $params = self::validate_parameters(self::get_questionnaire_view_parameters(), ['questionnaireinfo' => $questionnaireinfo]);
        $questionnaireinfo = $params['questionnaireinfo'];
        $records = [];
        $warnings = [];

        try {
            $records[] = questionnairehelper::read_view_questionnaire_data($questionnaireinfo);
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
     * @return external_single_structure.
     */

    public static function get_questionnaire_view_returns() {
         return  new external_single_structure(
            array(
                'records' => new external_single_structure(
                    array(
                        'questions' => self::get_questionnaire_structure(),
                        'course' => self::get_courses_structure(),
                        'id' => new external_value(PARAM_INT, 'questionnaire id'),
                        'name' => new external_value(PARAM_TEXT, 'the questionnaire name'),
                        'sid' => new external_value(PARAM_INT, 'survey id'),
                        'assigned_user_role_id' => new external_value(PARAM_INT, 'questionnaire assigned user role id'),
                        'survey' => self::get_survey_structure(),
                        'cm' => self::get_course_module_structure(),
                        'warnings' => new \external_warnings()
                    )
                )
            )
        );
    }

    /**
     * Describes a single questionnaire structure.
     * @return external_single_structure the questionnaire data. Some fields may not be returned depending on the questionnaire display settings.
     *
     */
    public function get_questionnaire_structure() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'question id'),
                    'surveyid' => new external_value(PARAM_INT, 'survey id'),
                    'name' => new external_value(PARAM_TEXT, 'the question name'),
                    'type' => new external_value(PARAM_TEXT, 'question type, i.e: rate scale'),
                    'choices' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'question id'),
                                'questionid' => new external_value(PARAM_INT, 'question id'),
                                'content' => new external_value(PARAM_TEXT, 'the question content'),
                                'value' => new external_value(PARAM_TEXT, 'the question value'),

                            )
                        ), 'Response file areas including files', VALUE_OPTIONAL
                    ),

                    'type_id' => new external_value(PARAM_INT, 'question type'),
                ),
                'The question data. Some fields may not be returned depending on the question display settings.'
            ), 'Question'
        );
    }

    /**
     * Describes a single survey structure.
     * @return external_single_structure the survey data.
     *
     */
    public function get_survey_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Survey id'),
                'name' => new external_value(PARAM_RAW, 'Survey name'),
                'courseid' => new external_value(PARAM_INT, 'Course id'),
            ), 'Surveys'
        );

    }

    /**
     * Describes a single course module structure.
     * @return external_single_structure the course module data.
     *
     */
    public function get_course_module_structure() {
        return new external_single_structure(
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
            ), 'Cm'
        );
    }

    /**
     * Describes a single course structure.
     * @return external_single_structure the course data.
     *
     */
    public function get_courses_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'course id'),
                'categoryid' => new external_value(PARAM_INT, 'category id'),
                'fullname' => new external_value(PARAM_RAW, 'full name'),
                'shortname' => new external_value(PARAM_RAW, 'course short name'),
                'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                'summary' => new external_value(PARAM_RAW, 'summary'),
                'summaryformat' => new external_format_value('summary'),
                'format' => new external_value(PARAM_PLUGIN,
                        'course format: weeks, topics, social, site,..'),
                'visible' => new external_value(PARAM_INT,
                        '1: available to student, 0:not available', VALUE_OPTIONAL),
                'enablecompletion' => new external_value(PARAM_INT,
                        'Enabled, control via completion and activity settings. Disbaled,
                            not shown in activity settings.',
                        VALUE_OPTIONAL),
            ), 'course'
        );
    }
}
