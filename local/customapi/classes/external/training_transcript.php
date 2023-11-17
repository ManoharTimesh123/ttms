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
 * Web Service functions for Custom API - Training Transcript
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
use local_customapi\exception\customapiException;
use local_customapi\helper\trainingtranscripthelper;

/**
 * Annual training calendar functions for Custom API.
 *
 */
class training_transcript extends external_api {

    /**
     * The parameters for training transcript get.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_training_transcript_parameters() {

        $trainingtranscriptfields = [
            'keyword' => new external_value(
                PARAM_RAW,
                'For search',
                VALUE_OPTIONAL,
                ''
            ),
            'trainingmode' => new external_value(
                PARAM_RAW,
                'For training mode',
                VALUE_OPTIONAL,
                ''
            ),
            'startdate' => new external_value(
                PARAM_RAW,
                'Start date of training',
                VALUE_OPTIONAL,
                ''
            ),
            'enddate' => new external_value(
                PARAM_RAW,
                'End date of training',
                VALUE_OPTIONAL,
                ''
            ),
            'trainingid' => new external_value(
                PARAM_INT,
                'Training id',
                VALUE_OPTIONAL,
                0
            ),
        ];

        return new external_function_parameters(
            [
                'trainingtranscript' => new external_single_structure($trainingtranscriptfields)
            ]
        );
    }

    /**
     * The API can get training transcript detail.
     *
     * @param $training_transcript_get
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_training_transcript($filters) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $filters = self::validate_parameters(self::get_training_transcript_parameters(), ['trainingtranscript' => $filters]);

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $records = [];
        $warnings = [];

        try {

            $records[] = trainingtranscripthelper::read_training_transcript($filters);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records']
        ];
    }

    /**
     * The return configuration for training transcript get.
     *
     * @return external_single_structure
     */
    public static function get_training_transcript_returns() {
        return new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'trainingid' => new external_value(PARAM_INT, 'The id of a training name'),
                            'trainingname' => new external_value(PARAM_TEXT, 'The name of a training name'),
                            'trainingtype' => new external_value(PARAM_RAW, 'The type of training like online or offline'),
                            'trainingstartdate' => new external_value(PARAM_RAW, 'The start date of training'),
                            'trainingenddate' => new external_value(PARAM_RAW, 'The end date of training'),
                            'trainingnoofdays' => new external_value(PARAM_RAW, 'Number of training days'),
                            'attendingdays' => new external_value(PARAM_RAW, 'Total attending days'),
                            'trainingdescription' => new external_value(PARAM_RAW, 'Training description'),
                            'venue' => new external_value(PARAM_RAW, 'Venues involved in the training'),
                            'cycleandbatch' => new external_value(PARAM_RAW, 'Cycle/Batch Id'),
                            'certificate' => new external_value(PARAM_RAW, 'Certificate'),
                            'traininghours' => new external_value(PARAM_INT, 'Hours spent in the ttraining'),
                        )
                    )
                )
            )
        );
    }
}

