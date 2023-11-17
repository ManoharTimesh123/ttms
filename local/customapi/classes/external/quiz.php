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
use external_files;
use mod_quiz_external;
use local_customapi\exception\customapiException;
use local_customapi\helper\quizhelper;

/**
 * Course functions for Custom API.
 *
 */
class quiz extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function get_quiz_start_attempt_parameters() {

        $qid = [
            'id' => new external_value(PARAM_INT, 'The quiz id'),
        ];

        return new external_function_parameters(
            [
                'quizid' => new external_single_structure($qid)
            ]
        );
    }

    /**
     * The API can start the quiz attempt.
     *
     * @param $quizid
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_quiz_start_attempt($quizid) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());
        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_quiz_start_attempt_parameters(), ['quizid' => $quizid]);
        $qid = $params['quizid'];
        $records = [];
        $warnings = [];

        try {
            $records[] = quizhelper::read_get_quiz_start_attempt($qid);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * Describes the start_attempt return value.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function get_quiz_start_attempt_returns() {
        return new external_single_structure(
             array(
                'records' => new external_single_structure(
                    array(
                        'attempt' => self::return_attempt_structure(),
                        'warnings' => new external_warnings(),
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
    public static function get_quiz_attempt_data_parameters() {

        $quizattemptid = [
             'attemptid' => new external_value(PARAM_INT, 'attempt id'),
             'page' => new external_value(PARAM_INT, 'page number'),
        ];

        return new external_function_parameters(
            [
                'quizattemptid' => new external_single_structure($quizattemptid)
            ]
        );
    }

    /**
     * The API can get quiz attempt data.
     *
     * @param $quizattemptid
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_quiz_attempt_data($quizattemptid = null) {

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());
        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_quiz_attempt_data_parameters(), ['quizattemptid' => $quizattemptid]);
        $quizattemptids = $params['quizattemptid'];
     
        $records = [];
        $warnings = [];

        try {
            $records[] = quizhelper::read_quiz_attempt_data($quizattemptids);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
    
        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * Describes the start_attempt return value.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function get_quiz_attempt_data_returns() {
        return new external_single_structure(
            array(
                'records' =>
                    new external_single_structure(
                        array(
                            'attempt' => self::return_attempt_structure(),
                            'messages' => new external_multiple_structure(
                                new external_value(PARAM_TEXT, 'access message'),
                                'access messages, will only be returned for users with mod/quiz:preview capability,
                                for other users this method will throw an exception if there are messages'),
                            'nextpage' => new external_value(PARAM_INT, 'next page number'),
                            'questions' => new external_multiple_structure(self::return_question_structure()),
                            'warnings' => new external_warnings(),
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
    public static function get_quiz_attempt_summary_parameters() {
        $quizattemptid = [
             'attemptid' => new external_value(PARAM_INT, 'attempt id'),
        ];

        return new external_function_parameters(
            [
                'quizattemptid' => new external_single_structure($quizattemptid)
            ]
        );

    }

    /**
     * The API can get quiz attempt summary.
     *
     * @param $quizattemptid
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_quiz_attempt_summary($quizattemptid = null) {
         // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());
        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_quiz_attempt_summary_parameters(), ['quizattemptid' => $quizattemptid]);
        $quizattemptid = $params['quizattemptid'];
   
        $records = [];
        $warnings = [];

        try {
            $records[] = quizhelper::read_quiz_attempt_summary($quizattemptids);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
        return [
            'records' => $records[0]['records'],
        ];

    }

    /**
     * Describes the start_attempt return value.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function get_quiz_attempt_summary_returns() {
         return new external_single_structure(
             array(
                'records' => new external_single_structure(
                    array(
                        'questions' => new external_multiple_structure(self::return_question_structure()),
                        'warnings' => new external_warnings(),
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
    public static function get_quiz_save_attempt_parameters() {
        $quizattemptidanddata = [
            'attemptid' => new external_value(PARAM_INT, 'attempt id'),
            'data' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_RAW, 'data name'),
                        'value' => new external_value(PARAM_RAW, 'data value'),
                    )
                  ), 'the data to be saved'
            )
        ];

        return new external_function_parameters(
            [
                'quizattemptidanddata' => new external_single_structure($quizattemptidanddata)
            ]
        );

    }

    /**
     * The API can get quiz save attempt.
     *
     * @param $quizattemptidanddata
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_quiz_save_attempt($quizattemptidanddata = null) {

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());
        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_quiz_save_attempt_parameters(), ['quizattemptidanddata' => $quizattemptidanddata]);
      
        $quizattemptidanddata = $params['quizattemptidanddata'];
        $records = [];
        $warnings = [];

        try {
            $records[] = quizhelper::read_quiz_save_attempt($quizattemptidanddata);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
        return [
            'records' => $records[0]['records'],
        ];

    }

    /**
     * Describes the start_attempt return value.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function get_quiz_save_attempt_returns() {
        return new external_single_structure(
             array(
                'records' => new external_single_structure(
                    array(
                        'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                        'warnings' => new external_warnings(),
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
    public static function get_quiz_attempt_review_parameters() {
        $quizattemptidanddata = [
            'attemptid' => new external_value(PARAM_INT, 'attempt id'),
        ];

        return new external_function_parameters(
            [
                'quizattemptid' => new external_single_structure($quizattemptidanddata)
            ]
        );

    }

    /**
     * The API can get quiz attempt review.
     *
     * @param $quizattemptid
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_quiz_attempt_review($quizattemptid = null) {

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());
        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_quiz_attempt_review_parameters(), ['quizattemptid' => $quizattemptid]);
        $quizattemptid = $params['quizattemptid'];
        $records = [];
        $warnings = [];

        try {
            $records[] = quizhelper::read_get_quiz_attempt_review($quizattemptid);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
        return [
            'records' => $records[0]['records'],
        ];

    }

    /**
     * Describes the quiz attempt review.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function get_quiz_attempt_review_returns() {
        return new external_single_structure(
             array(
                'records' => new external_single_structure(
                   array(
                        'grade' => new external_value(PARAM_RAW, 'grade for the quiz (or empty or "notyetgraded")'),
                        'attempt' => self::return_attempt_structure(),
                        'additionaldata' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_ALPHANUMEXT, 'id of the data'),
                                    'title' => new external_value(PARAM_TEXT, 'data title'),
                                    'content' => new external_value(PARAM_RAW, 'data content'),
                                )
                            )
                        ),
                        'questions' => new external_multiple_structure(self::return_question_structure()),
                        'warnings' => new external_warnings(),
                    )
                )
            )
        );
        
    }

    /**
     * Describes the parameters for process_attempt.
     *
     * @return external_function_parameters
     * @since Moodle 3.1
     */
    public static function get_quiz_process_attempt_parameters() {
        $quizattemptinfo = [
            'attemptid' => new external_value(PARAM_INT, 'attempt id'),
            'data' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_RAW, 'data name'),
                        'value' => new external_value(PARAM_RAW, 'data value'),
                    )
                ),
               'the data to be saved', VALUE_DEFAULT, array()
            ),
            'finishattempt' => new external_value(PARAM_BOOL, 'whether to finish or not the attempt', VALUE_DEFAULT, false),
            'timeup' => new external_value(PARAM_BOOL, 'whether the WS was called by a timer when the time is up',
                                           VALUE_DEFAULT, false)
        ];
        return new external_function_parameters(
            [
                'quizattemptinfo' => new external_single_structure($quizattemptinfo)
            ]
        );
    }

    /**
     * The API can get quiz proces attempt.
     *
     * @param $quizattemptinfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_quiz_process_attempt($quizattemptinfo = null) {


        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());
        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_quiz_process_attempt_parameters(), ['quizattemptinfo' => $quizattemptinfo]);
       
        $quizattemptprocessinfo = $params['quizattemptinfo'];
        $records = [];
        $warnings = [];

        try {
            $records[] = quizhelper::read_get_quiz_process_attempt($quizattemptprocessinfo);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
        return [
            'records' => $records[0]['records'],
        ];

    }

    /**
     * Describes the quiz process attempt return value.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function get_quiz_process_attempt_returns() {
        return new external_single_structure(
             array(
                'records' => new external_single_structure(
                    array(
                        'state' => new external_value(PARAM_ALPHANUMEXT, 'state: the new attempt state:inprogress, finished, overdue, abandoned'),
                        'warnings' => new external_warnings(),
                    )
                )
            )
        );
    }




    /**
     * Describes a single attempt structure.
     *
     * @return external_single_structure the attempt structure
     */
    private static function return_attempt_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Attempt id.', VALUE_OPTIONAL),
                'quiz' => new external_value(PARAM_INT, 'Foreign key reference to the quiz that was attempted.',
                                                VALUE_OPTIONAL),
                'userid' => new external_value(PARAM_INT, 'Foreign key reference to the user whose attempt this is.',
                                                VALUE_OPTIONAL),
                'attempt' => new external_value(PARAM_INT, 'Sequentially numbers this students attempts at this quiz.',
                                                VALUE_OPTIONAL),
                'uniqueid' => new external_value(PARAM_INT, 'Foreign key reference to the question_usage that holds the
                                                    details of the the question_attempts that make up this quiz
                                                    attempt.', VALUE_OPTIONAL),
                'layout' => new external_value(PARAM_RAW, 'Attempt layout.', VALUE_OPTIONAL),
                'currentpage' => new external_value(PARAM_INT, 'Attempt current page.', VALUE_OPTIONAL),
                'preview' => new external_value(PARAM_INT, 'Whether is a preview attempt or not.', VALUE_OPTIONAL),
                'state' => new external_value(PARAM_ALPHA, 'The current state of the attempts. \'inprogress\',
                                                \'overdue\', \'finished\' or \'abandoned\'.', VALUE_OPTIONAL),
                'timestart' => new external_value(PARAM_INT, 'Time when the attempt was started.', VALUE_OPTIONAL),
                'timefinish' => new external_value(PARAM_INT, 'Time when the attempt was submitted.
                                                    0 if the attempt has not been submitted yet.', VALUE_OPTIONAL),
                'timemodified' => new external_value(PARAM_INT, 'Last modified time.', VALUE_OPTIONAL),
                'timemodifiedoffline' => new external_value(PARAM_INT, 'Last modified time via webservices.', VALUE_OPTIONAL),
                'timecheckstate' => new external_value(PARAM_INT, 'Next time quiz cron should check attempt for state changes.  NULL means never check.', VALUE_OPTIONAL),
                'sumgrades' => new external_value(PARAM_FLOAT, 'Total marks for this attempt.', VALUE_OPTIONAL),
            )
        );
    }

    /**
     * Describes a single question structure.
     *
     * @return external_single_structure the question data. Some fields may not be returned depending on the quiz display settings.
     * @since  Moodle 3.1
     * @since Moodle 3.2 blockedbyprevious parameter added.
     */
    private static function return_question_structure() {
        return new external_single_structure(
            array(
                'slot' => new external_value(PARAM_INT, 'slot number'),
                'type' => new external_value(PARAM_ALPHANUMEXT, 'question type, i.e: multichoice'),
                'page' => new external_value(PARAM_INT, 'page of the quiz this question appears on'),
                'html' => new external_value(PARAM_RAW, 'the question rendered'),
                'responsefileareas' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'area' => new external_value(PARAM_NOTAGS, 'File area name'),
                            'files' => new external_files('Response files for the question', VALUE_OPTIONAL),
                        )
                    ), 'Response file areas including files', VALUE_OPTIONAL
                ),
                'sequencecheck' => new external_value(PARAM_INT, 'the number of real steps in this attempt', VALUE_OPTIONAL),
                'lastactiontime' => new external_value(PARAM_INT, 'the timestamp of the most recent step in this question attempt', VALUE_OPTIONAL),
                'hasautosavedstep' => new external_value(PARAM_BOOL, 'whether this question attempt has autosaved data', VALUE_OPTIONAL),
                'flagged' => new external_value(PARAM_BOOL, 'whether the question is flagged or not'),
                'number' => new external_value(PARAM_INT, 'question ordering number in the quiz', VALUE_OPTIONAL),
                'state' => new external_value(PARAM_ALPHA, 'the state where the question is in.
                    It will not be returned if the user cannot see it due to the quiz display correctness settings.', VALUE_OPTIONAL),
                'status' => new external_value(PARAM_RAW, 'current formatted state of the question', VALUE_OPTIONAL),
                'blockedbyprevious' => new external_value(PARAM_BOOL, 'whether the question is blocked by the previous question', VALUE_OPTIONAL),
                'mark' => new external_value(PARAM_RAW, 'the mark awarded.It will be returned only if the user is allowed to see it.', VALUE_OPTIONAL),
                'maxmark' => new external_value(PARAM_FLOAT, 'the maximum mark possible for this question attempt.
                    It will be returned only if the user is allowed to see it.', VALUE_OPTIONAL),
                'settings' => new external_value(PARAM_RAW, 'Question settings (JSON encoded).', VALUE_OPTIONAL),
            ),
            'The question data. Some fields may not be returned depending on the quiz display settings.'
        );
    }
}
