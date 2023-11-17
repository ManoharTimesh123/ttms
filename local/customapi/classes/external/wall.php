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
 * Web Service functions for Custom API - wall posts
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
use local_customapi\helper\wallhelper;

/**
 * Wall functions for Custom API.
 *
 */
class wall extends external_api {
    /**
     * The parameters for wall post add.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function post_add_parameters() {
        $wallfields = [
            'description' => new external_value(
                PARAM_TEXT,
                'Description of the wall post'
            ),
            'file' => new external_value(
                PARAM_FILE,
                'File uploaded for wall post',
                VALUE_OPTIONAL
            ),
            'courseid' => new external_value(
                PARAM_INT,
                'Course id the wall post associated with'
            ),
        ];

        return new external_function_parameters(
            [
                'walls' => new external_multiple_structure(
                    new external_single_structure($wallfields)
                )
            ]
        );
    }

    /**
     * The API can send wall post details.
     *
     * @param $wall
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function post_add($walls) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::post_add_parameters(), ['walls' => $walls]);

        $records = [];
        $warnings = [];

        foreach ($params['walls'] as $wall) {

            try {
                // There are differences - update.
                $records[] = wallhelper::create_wall($wall);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            $records,
        ];
    }

    /**
     * The return configuration for post_add.
     *
     * @return external_single_structure
     */
    public static function post_add_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * The parameters for wall post delete.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function post_get_parameters() {
        $wallfields = [
            'courseid' => new external_value(
                PARAM_INT,
                'Course id of wall post',
                VALUE_OPTIONAL
            ),
        ];

        return new external_function_parameters(
            [
                'walls' => new external_multiple_structure(
                    new external_single_structure($wallfields)
                )
            ]
        );
    }

    /**
     * The API can send wall post id.
     *
     * @param $wall
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function post_get($walls) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::post_get_parameters(), ['walls' => $walls]);

        $records = [];
        $warnings = [];

        foreach ($params['walls'] as $wall) {

            try {
                $records[] = wallhelper::read_wall($wall);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'records' => $records[0]['records'],
        ];
    }

    /**
     * The return configuration for post_get.
     *
     * @return external_single_structure
     */
    public static function post_get_returns() {
        return new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'The ID of the item'),
                            'course_id' => new external_value(PARAM_INT, 'The ID of the course'),
                            'course_name' => new external_value(PARAM_RAW, 'The name of the course'),
                            'share_url' => new external_value(PARAM_RAW, 'The facebook share url of item'),
                            'user_like_post' => new external_value(PARAM_BOOL, 'Current user like post or not'),
                            'post_content' => new external_value(PARAM_RAW, 'The name of the item'),
                            'post_file' => new external_value(PARAM_RAW, 'The file of the item'),
                            'post_added_by' => new external_value(PARAM_RAW, 'The description of the item'),
                            'created_date' => new external_value(PARAM_RAW, 'The created date of the item'),
                            'post_like_count' => new external_value(PARAM_INT, 'like count of  item'),
                            'post_share_count' => new external_value(PARAM_INT, 'Share count of  item'),
                            'post_comment_count' => new external_value(PARAM_INT, 'Comment count of  item'),
                            'post_comment' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'id' => new external_value(PARAM_INT, 'The ID of the item'),
                                        'description' => new external_value(PARAM_RAW, 'The description of the item'),
                                        'commented_by' => new external_value(PARAM_RAW, 'The commenter name'),
                                        'timecreated' => new external_value(PARAM_RAW, 'The created time of the item'),
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
     * The parameters for wall post update.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function post_update_parameters() {
        $wallfields = [
            'description' => new external_value(
                PARAM_TEXT,
                'Description of the wall post'
            ),
            'postid' => new external_value(
                PARAM_INT,
                'Post id of wall post'
            ),
            'courseid' => new external_value(
                PARAM_INT,
                'Course id the wall post associated with',
                VALUE_OPTIONAL
            ),
        ];

        return new external_function_parameters(
            [
                'walls' => new external_multiple_structure(
                    new external_single_structure($wallfields)
                )
            ]
        );
    }

    /**
     * The API can send wall post details.
     *
     * @param $wall
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function post_update($walls) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::post_update_parameters(), ['walls' => $walls]);

        $records = [];
        $warnings = [];

        foreach ($params['walls'] as $wall) {

            try {
                // There are differences - update.
                $records[] = wallhelper::update_wall($wall);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['walls']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for post_update.
     *
     * @return external_single_structure
     */
    public static function post_update_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * The parameters for wall post delete.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function post_delete_parameters() {
        $wallfields = [
            'postid' => new external_value(
                PARAM_INT,
                'Post id of wall post'
            ),
        ];

        return new external_function_parameters(
            [
                'walls' => new external_multiple_structure(
                    new external_single_structure($wallfields)
                )
            ]
        );
    }

    /**
     * The API can send wall post id.
     *
     * @param $wall
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function post_delete($walls) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::post_delete_parameters(), ['walls' => $walls]);

        $records = [];
        $warnings = [];

        foreach ($params['walls'] as $wall) {

            try {
                $records[] = wallhelper::delete_wall($wall);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['walls']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for post_delete.
     *
     * @return external_single_structure
     */
    public static function post_delete_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * The parameters for wall post comment add.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function post_comment_add_parameters() {
        $wallfields = [
            'description' => new external_value(
                PARAM_TEXT,
                'Description of the wall post'
            ),
            'postid' => new external_value(
                PARAM_INT,
                'Post id of wall post'
            ),
            'courseid' => new external_value(
                PARAM_INT,
                'Course id the wall post associated with',
                VALUE_OPTIONAL
            ),
        ];

        return new external_function_parameters(
            [
                'walls' => new external_multiple_structure(
                    new external_single_structure($wallfields)
                )
            ]
        );
    }

    /**
     * The API can send wall post comment details.
     *
     * @param $wall
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function post_comment_add($walls) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::post_comment_add_parameters(), ['walls' => $walls]);

        $records = [];
        $warnings = [];

        foreach ($params['walls'] as $wall) {

            try {
                $records[] = wallhelper::create_wall_comment($wall);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['walls']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for post_comment_add.
     *
     * @return external_single_structure
     */
    public static function post_comment_add_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * The parameters for wall post comment delete.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function post_comment_delete_parameters() {
        $wallfields = [
            'commentid' => new external_value(
                PARAM_INT,
                'Comment id of wall post'
            ),
        ];

        return new external_function_parameters(
            [
                'walls' => new external_multiple_structure(
                    new external_single_structure($wallfields)
                )
            ]
        );
    }

    /**
     * The API can send wall post comment id.
     *
     * @param $wall
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function post_comment_delete($walls) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::post_comment_delete_parameters(), ['walls' => $walls]);

        $records = [];
        $warnings = [];

        foreach ($params['walls'] as $wall) {

            try {
                $records[] = wallhelper::delete_wall_comment($wall);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['walls']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for post_comment_delete.
     *
     * @return external_single_structure
     */
    public static function post_comment_delete_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * The parameters for wall post like.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function post_like_parameters() {
        $wallfields = [
            'postid' => new external_value(
                PARAM_INT,
                'Post id of wall post'
            ),
        ];

        return new external_function_parameters(
            [
                'walls' => new external_multiple_structure(
                    new external_single_structure($wallfields)
                )
            ]
        );
    }

    /**
     * The API can send wall post id.
     *
     * @param $wall
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function post_like($walls) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::post_like_parameters(), ['walls' => $walls]);

        $records = [];
        $warnings = [];

        foreach ($params['walls'] as $wall) {

            try {
                $records[] = wallhelper::like_wall($wall);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['walls']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for post_like.
     *
     * @return external_single_structure
     */
    public static function post_like_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * The parameters for wall post like.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function post_share_parameters() {
        $wallfields = [
            'postid' => new external_value(
                PARAM_INT,
                'Post id of wall post to be shared'
            ),
            'shareto' => new external_value(
                PARAM_TEXT,
                'Social provider where post need to be shared'
            ),
        ];

        return new external_function_parameters(
            [
                'walls' => new external_multiple_structure(
                    new external_single_structure($wallfields)
                )
            ]
        );
    }

    /**
     * The API can send wall post id.
     *
     * @param $wall
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function post_share($walls) {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::post_share_parameters(), ['walls' => $walls]);

        $records = [];
        $warnings = [];

        foreach ($params['walls'] as $wall) {

            try {
                $records[] = wallhelper::share_wall($wall);
            } catch (customapiException $err) {
                $warnings[] = $err->getwarning();
            }
        }

        return [
            'processed' => count($params['walls']),
            'records' => $records,
            'warnings' => $warnings
        ];
    }

    /**
     * The return configuration for post_share.
     *
     * @return external_single_structure
     */
    public static function post_share_returns() {
        return new external_single_structure([
            'processed' => new external_value(PARAM_INT, 'Count of input items processed.'),
            'records' => new external_records(),
            'warnings' => new external_warnings(),
        ]);
    }
}
