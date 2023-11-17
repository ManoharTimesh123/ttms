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
 * Web Service functions for Custom API - announcement
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
use local_customapi\helper\announcementhelper;

/**
 * News functions for Custom API.
 *
 */
class announcement extends external_api {

    /**
     * The parameters for announcement get.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function announcement_get_parameters() {

        return new external_function_parameters(array());
    }

    /**
     * The API can get announcement detail.
     *
     * @param $announcemenet
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function announcement_get() {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.

        $records = [];
        $warnings = [];

        try {

            $records[] = announcementhelper::read_announcement();
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * The return configuration for announcement_get.
     *
     * @return external_single_structure
     */
    public static function announcement_get_returns() {
        return  new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'title' => new external_value(PARAM_RAW, 'The title of the announcement'),
                            'description' => new external_value(PARAM_RAW, 'The description of the announcement'),
                            'image' => new external_value(PARAM_RAW, 'The image of the announcement'),
                        )
                    )
                )
            )
        );
    }
}
