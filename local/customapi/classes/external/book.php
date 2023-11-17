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
 * Web Service functions for Custom API - Book Information
 *
 * @package local_customapi
 */

namespace local_customapi\external;

use coding_exception;
use context_system;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use external_format_value;
use external_files;
use local_customapi\exception\customapiException;
use local_customapi\helper\bookhelper;

/**
 * Course functions for Custom API.
 *
 */
class book extends external_api {

    /**
     * The parameters for get view book.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_view_book_parameters() {

        $bookids = [
                'bookid' => new external_value(PARAM_INT, 'book instance id'),
                'chapterid' => new external_value(PARAM_INT, 'chapter id', VALUE_DEFAULT, 0)
            ];

        return new external_function_parameters(
            [
                'bookinfo' => new external_single_structure($bookids),
            ]
        );
    }

    /**
     * The API can get book view.
     *
     * @param $bookinfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_view_book($bookinfo) {
      
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_view_book_parameters(), ['bookinfo' => $bookinfo]);

        $bookinfo = $params['bookinfo'];
        
        $records = [];
        $warnings = [];

        try {

            $records[] = bookhelper::read_view_book($bookinfo);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records'],
        ];
    }
 
    /**
     * The return configuration for get issue certificate.
     *
     * @return external_single_structure
     */
    public static function get_view_book_returns() {
        return  new external_single_structure(
            array(
            'records' => new external_single_structure(
                      array(
                        'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                        'warnings' => new external_warnings()
                    )
                )
            )
        );
    }

    /**
     *
     * The parameters for get all books by courses.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function get_all_books_by_courses_parameters() {

        $courseids = [
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()),
            ];

        return new external_function_parameters(
            [
                'courseidsinfo' => new external_single_structure($courseids),
            ]
        );
    }

    /**
     * The API can get_all_books_by_courses.
     *
     * @param $courseidsinfo
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function get_all_books_by_courses($courseidsinfo) {
      
        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        // Do basic automatic PARAM checks on incoming data, using params description (again; external.lib already did this).
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(self::get_all_books_by_courses_parameters(), ['courseidsinfo' => $courseidsinfo]);

        $courseidsinfo = $params['courseidsinfo'];
        
        $records = [];
        $warnings = [];

        try {

            $records[] = bookhelper::read_all_books_by_courses($courseidsinfo);
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }
       
        return [
            'records' => $records[0]['records'],
        ];
    }
 
    /**
     * The return configuration for get_all_books_by_courses.
     *
     * @return external_single_structure
     */
    public static function get_all_books_by_courses_returns() {
        return  new external_single_structure(
            array(
                'records' => new external_single_structure(
                    array(
                        'books' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'Book id'),
                                    'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                                    'course' => new external_value(PARAM_INT, 'Course id'),
                                    'name' => new external_value(PARAM_RAW, 'Book name'),
                                    'intro' => new external_value(PARAM_RAW, 'The Book intro'),
                                    'introformat' => new external_format_value('intro'),
                                    'introfiles' => new external_files('Files in the introduction text', VALUE_OPTIONAL),
                                    'numbering' => new external_value(PARAM_INT, 'Book numbering configuration'),
                                    'navstyle' => new external_value(PARAM_INT, 'Book navigation style configuration'),
                                    'customtitles' => new external_value(PARAM_INT, 'Book custom titles type'),
                                    'revision' => new external_value(PARAM_INT, 'Book revision', VALUE_OPTIONAL),
                                    'timecreated' => new external_value(PARAM_INT, 'Time of creation', VALUE_OPTIONAL),
                                    'timemodified' => new external_value(PARAM_INT, 'Time of last modification', VALUE_OPTIONAL),
                                    'section' => new external_value(PARAM_INT, 'Course section id', VALUE_OPTIONAL),
                                    'visible' => new external_value(PARAM_BOOL, 'Visible', VALUE_OPTIONAL),
                                    'groupmode' => new external_value(PARAM_INT, 'Group mode', VALUE_OPTIONAL),
                                    'groupingid' => new external_value(PARAM_INT, 'Group id', VALUE_OPTIONAL),
                                ), 'Books'
                            )
                        ),
                        'warnings' => new external_warnings(),
                    )
                )
            )
        );
    }
}
