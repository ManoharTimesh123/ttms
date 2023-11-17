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
 * Web Service functions for Custom API - news
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
use local_customapi\helper\newshelper;

/**
 * News functions for Custom API.
 *
 */
class news extends external_api {

    /**
     * The parameters for news add.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function news_add_parameters() {
        $newsfields = [
            'title' => new external_value(
                PARAM_TEXT,
                'Title of the news'
            ),
            'description' => new external_value(
                PARAM_TEXT,
                'Description of the news'
            ),
            'startdate' => new external_value(
                PARAM_TEXT,
                'Startdate of the news'
            ),
            'enddate' => new external_value(
                PARAM_TEXT,
                'Enddate of the news'
            ),
            'file' => new external_value(
                PARAM_FILE,
                'File uploaded for news',
                VALUE_OPTIONAL
            )
        ];

        return new external_function_parameters(
            [
                'news' => new external_multiple_structure(
                    new external_single_structure($newsfields)
                )
            ]
        );
    }

    /**
     * The API can send news details.
     *
     * @param $news
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function news_add($news) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::news_add_parameters(), ['news' => $news]);

        $records = [];
        $warnings = [];

        foreach ($params['news'] as $news) {

            try {

                $records[] = newshelper::create_news($news);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['news']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for news_add.
     *
     * @return external_single_structure
     */
    public static function news_add_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    public static function news_get_parameters() {

        return new external_function_parameters(array());
    }

    /**
     * The API can send news detail.
     *
     * @param $news
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function news_get() {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.

        $records = [];
        $warnings = [];

        try {

            $records[] = newshelper::read_news();
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * The return configuration for news_add.
     *
     * @return external_single_structure
     */
    public static function news_get_returns() {
        return  new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'title' => new external_value(PARAM_RAW, 'The title of the news'),
                            'description' => new external_value(PARAM_RAW, 'The description of the news'),
                            'image' => new external_value(PARAM_RAW, 'The image of the news'),
                        )
                    )
                )
            )
        );
    }

    /**
     * The parameters for news update.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function news_update_parameters() {
        $newsfields = [
            'newsid' => new external_value(
                PARAM_INT,
                'Id of the news'
            ),
            'title' => new external_value(
                PARAM_TEXT,
                'Title of the news'
            ),
            'description' => new external_value(
                PARAM_TEXT,
                'Description of the news'
            ),
            'startdate' => new external_value(
                PARAM_TEXT,
                'Startdate of the news'
            ),
            'enddate' => new external_value(
                PARAM_TEXT,
                'Enddate of the news'
            ),
            'file' => new external_value(
                PARAM_FILE,
                'File uploaded for news',
                VALUE_OPTIONAL
            )
        ];

        return new external_function_parameters(
            [
                'news' => new external_multiple_structure(
                    new external_single_structure($newsfields)
                )
            ]
        );
    }

    /**
     * The API can send news details.
     *
     * @param $news
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function news_update($news) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::news_update_parameters(), ['news' => $news]);

        $records = [];
        $warnings = [];

        foreach ($params['news'] as $news) {

            try {

                $records[] = newshelper::update_news($news);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['news']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for news_update.
     *
     * @return external_single_structure
     */
    public static function news_update_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * The parameters for news delete.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function news_delete_parameters() {
        $newsfields = [
            'newsid' => new external_value(
                PARAM_INT,
                'Id of the news'
            )
        ];

        return new external_function_parameters(
            [
                'news' => new external_multiple_structure(
                    new external_single_structure($newsfields)
                )
            ]
        );
    }

    /**
     * The API can send news id.
     *
     * @param $news
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function news_delete($news) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::news_delete_parameters(), ['news' => $news]);

        $records = [];
        $warnings = [];

        foreach ($params['news'] as $news) {

            try {

                $records[] = newshelper::delete_news($news);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['news']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for news_add.
     *
     * @return external_single_structure
     */
    public static function news_delete_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

}
