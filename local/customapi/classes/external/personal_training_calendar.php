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
 * Web Service functions for Custom API - personal_training_calendart
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
use local_customapi\helper\personaltrainingcalendarhelper;

/**
 * News functions for Custom API.
 *
 */
class personal_training_calendar extends external_api {

    /**
     * The parameters for personal_training_calendart get.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_personal_training_calendar_parameters() {

        return new external_function_parameters([
            'trainingfilter' => new external_single_structure([
                'freetextsearch' => new external_value(PARAM_RAW, 'For free text search', VALUE_OPTIONAL, ''),
                'trainingdate' => new external_value(PARAM_RAW, 'For training date', VALUE_OPTIONAL, ''),
                'coursemode' => new external_value(PARAM_RAW, 'For course mode', VALUE_OPTIONAL, ''),
                'startdate' => new external_value(PARAM_RAW, 'Startdate', VALUE_OPTIONAL, ''),
                'enddate' => new external_value(PARAM_RAW, 'enddate', VALUE_OPTIONAL, ''),
                'trainingid' => new external_value(PARAM_INT, 'Training id', VALUE_OPTIONAL, 0)
            ])
        ]);
    }

    /**
     * The API can get personal_training_calendart detail.
     *
     * @param $personal_training_calendaret
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_personal_training_calendar($trainingfilter) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $trainingfilter = self::validate_parameters(self::get_personal_training_calendar_parameters(), ['trainingfilter' => $trainingfilter]);

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $records = [];
        $warnings = [];

        try {

            $records[] = personaltrainingcalendarhelper::read_personal_training_calendar($trainingfilter);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'processed' => count($records),
            'records' => $records[0]['records'],
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for personal_training_calendart_get.
     *
     * @return external_single_structure
     */
    public static function get_personal_training_calendar_returns() {
        return new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'sno' => new external_value(PARAM_RAW, 'The Sno of the training'),
                            'trainingid' => new external_value(PARAM_INT, 'The id of the training'),
                            'trainingname' => new external_value(PARAM_TEXT, 'The name of a training name'),
                            'trainingtype' => new external_value(PARAM_RAW, 'The type of training like online or offline'),
                            'startdate' => new external_value(PARAM_RAW, 'The start date of training'),
                            'enddate' => new external_value(PARAM_RAW, 'The end date of training'),
                            'noofdays' => new external_value(PARAM_RAW, 'Number of training days'),
                            'traningdays' => new external_value(PARAM_RAW, 'Name of training days'),
                            'abouttraining' => new external_value(PARAM_RAW, 'Training discription'),
                            'trainingvenue' => new external_value(PARAM_RAW, 'The place of training'),
                            'cyclebatchid' => new external_value(PARAM_RAW, 'Cycle and batch id of training'),
                            'status' => new external_value(PARAM_RAW, 'Status about training like its upcoming,past,ongoing'),
                            'facilitator' => new external_value(PARAM_RAW, 'The facilitator in the training'),
                            'coordinator' => new external_value(PARAM_RAW, 'The coordinator in the training')
                        )
                    )
                )
            )
        );
    }
}

