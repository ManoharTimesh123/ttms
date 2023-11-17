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
 * Web Service functions for Custom API - blog
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
use local_customapi\helper\bloghelper;

/**
 * Blog functions for Custom API.
 *
 */
class blog extends external_api {

    /**
     * The parameters for blog add.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function blog_add_parameters() {
        $blogfields = [
            'title' => new external_value(
                PARAM_TEXT,
                'Title of the blog'
            ),
            'description' => new external_value(
                PARAM_TEXT,
                'Description of the bloh'
            ),
            'file' => new external_value(
                PARAM_FILE,
                'File uploaded for blog',
                VALUE_OPTIONAL
            )
        ];

        return new external_function_parameters(
            [
                'blog' => new external_multiple_structure(
                    new external_single_structure($blogfields)
                )
            ]
        );
    }

    /**
     * The API can send blog details.
     *
     * @param $blog
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function blog_add($blog) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::blog_add_parameters(), ['blog' => $blog]);

        $records = [];
        $warnings = [];

        foreach ($params['blog'] as $blog) {

            try {

                $records[] = bloghelper::create_blog($blog);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['blog']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for blog_add.
     *
     * @return external_single_structure
     */
    public static function blog_add_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * The parameters for blog get.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function blog_get_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * The API can get blog detail.
     *
     * @param $blog
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function blog_get() {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
  
        $records = [];
        $warnings = [];

        try {

            $records[] = bloghelper::read_blog();
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * The return configuration for blog_get.
     *
     * @return external_single_structure
     */
    public static function blog_get_returns() {
        return new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'title' => new external_value(PARAM_RAW, 'The title of the blog'),
                            'description' => new external_value(PARAM_RAW, 'The description of the blog'),
                            'image' => new external_value(PARAM_RAW, 'The image of the blog'),
                        )
                    )
                )
            )
        );
    }

    /**
     * The parameters for blog update.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function blog_update_parameters() {
        $blogfields = [
            'blogid' => new external_value(
                PARAM_INT,
                'Id of the blog'
            ),
            'title' => new external_value(
                PARAM_TEXT,
                'Title of the blog'
            ),
            'description' => new external_value(
                PARAM_TEXT,
                'Description of the blog'
            ),
            'file' => new external_value(
                PARAM_FILE,
                'File uploaded for blog',
                VALUE_OPTIONAL
            )
        ];

        return new external_function_parameters(
            [
                'blog' => new external_multiple_structure(
                    new external_single_structure($blogfields)
                )
            ]
        );
    }

    /**
     * The API can send blog details.
     *
     * @param $blog
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function blog_update($blog) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::blog_update_parameters(), ['blog' => $blog]);

        $records = [];
        $warnings = [];

        foreach ($params['blog'] as $blog) {

            try {

                $records[] = bloghelper::update_blog($blog);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['blog']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for blog_update.
     *
     * @return external_single_structure
     */
    public static function blog_update_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * The parameters for blog delete.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function blog_delete_parameters() {
        $blogfields = [
            'blogid' => new external_value(
                PARAM_INT,
                'Id of the blog'
            )
        ];

        return new external_function_parameters(
            [
                'blog' => new external_multiple_structure(
                    new external_single_structure($blogfields)
                )
            ]
        );
    }

    /**
     * The API can send blog id.
     *
     * @param $blog
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function blog_delete($blog) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::blog_delete_parameters(), ['blog' => $blog]);

        $records = [];
        $warnings = [];

        foreach ($params['blog'] as $news) {

            try {

                $records[] = bloghelper::delete_blog($news);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['blog']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for news_delete.
     *
     * @return external_single_structure
     */
    public static function blog_delete_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }
}
