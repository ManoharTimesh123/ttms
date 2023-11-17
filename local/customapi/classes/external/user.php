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
use external_format_value;
use local_customapi\exception\customapiException;
use local_customapi\helper\userhelper;

/**
 * User functions for Custom API.
 *
 */
class user extends external_api {

    /**
     * The parameters for user profile get.
     *
     * @return external_function_parameters
     * @throws coding_exception
     */
    public static function user_profile_get_parameters() {

        return new external_function_parameters(array());
    }

    /**
     * The API can get user detail.
     *
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     * @throws coding_exception
     */
    public static function user_profile_get() {
        global $CFG;

        // Ensure the current user is allowed to run this function in this context.
        // Note; specific capabilities will be checked within the create and update functions.
        self::validate_context(context_system::instance());

        $records = [];
        $warnings = [];

        try {

            $records[] = userhelper::read_user_profile();
        } catch (customapiException $err) {
            $warnings[] = $err->getwarning();
        }

        return [
            'records' => $records[0]['records']
        ];
    }

    /**
     * The return configuration for user_profile_get.
     *
     * @return external_single_structure
     */
    public static function user_profile_get_returns() {
        return  new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'The name of the user'),
                            'email' => new external_value(PARAM_RAW, 'The email of the user'),
                            'profile_picture' => new external_value(PARAM_RAW, 'The image of the user'),
                            'uid' => new external_value(PARAM_RAW, 'The uid of the user'),
                            'school_name' => new external_value(PARAM_RAW, 'The school name of the user'),
                            'school_code' => new external_value(PARAM_RAW, 'The school code of the user'),
                            'position' => new external_value(PARAM_RAW, 'The position of the user'),
                            'subject' => new external_value(PARAM_RAW, 'The subject of the user'),
                            'doj' => new external_value(PARAM_RAW, 'The date of joining of the user'),
                            'rating' => new external_value(PARAM_RAW, 'The overall course rating of the user'),
                            'grade' => new external_value(PARAM_RAW, 'The grade of the user'),
                            'post' => new external_value(PARAM_RAW, 'The post of the user'),
                            'caste' => new external_value(PARAM_RAW, 'The cate of the user'),
                            'achievements' => new external_single_structure(
                                array(
                                    'badges' => new external_value(PARAM_RAW, 'The ID of the item'),
                                    'medals' => new external_value(PARAM_RAW, 'The description of the item'),
                                    'certificates' => new external_value(PARAM_RAW, 'The commenter name'),
                                    'courses' => new external_value(PARAM_RAW, 'The created time of the item'),
                                    'trainings' => new external_value(PARAM_RAW, 'The created time of the item'),
                                )
                            ),
                        )
                    )
                )
            )
        );
    }

    /**
     * Get user notifications parameters description.
     *
     * @return external_function_parameters
     *
     */
    public static function get_user_notifications_parameters() {
        return new external_function_parameters([
                'newestfirst' => new external_value(PARAM_BOOL, 'True for ordering DESC, false for ASC', VALUE_DEFAULT, true),
                'limit' => new external_value(PARAM_INT, 'The number of rows to return', VALUE_DEFAULT, 0),
                'offset' => new external_value(PARAM_INT, 'Offset the result set by a given amount', VALUE_DEFAULT, 0),
        ]);
    }

    
    /**
     * Get user notifications function.
     * @param  bool     $newestfirst        True for ordering DESC, false for ASC
     * @param  int      $limit              The number of rows to return
     * @param  int      $offset             Offset the result set by a given amount
     * @return all notification
     */
    public static function get_user_notifications($newestfirst, $limit, $offset) {
        global $CFG, $PAGE, $DB, $USER;

        $params = self::validate_parameters(self::get_user_notifications_parameters(),
            array(
                'newestfirst' => $newestfirst,
                'limit' => $limit,
                'offset' => $offset,
            )
        );
        $userid = $USER->id;
        $newestfirst = $params['newestfirst'];
        $limit = $params['limit'];
        $offset = $params['offset'];
        $renderer = $PAGE->get_renderer('core_message');
        $sort = $newestfirst ? 'DESC' : 'ASC';
        $notifications = \message_popup\api::get_popup_notifications($userid, $sort, $limit, $offset);
        $usernotifications = [];
        if ($notifications) {
            foreach ($notifications as $notification) {
                $notificationoutput = new \message_popup\output\popup_notification($notification);
                $notificationcontext = $notificationoutput->export_for_template($renderer);
                $notificationcontext->deleted = false;
                $usernotifications[] = $notificationcontext;
            }
        }

        return array(
            'records' => $usernotifications,
        );
    }

    /**
     * Get user notifications return description.
     *
     * @return external_single_structure
     *
     */
    public static function get_user_notifications_returns() {
        return new external_single_structure(
            array(
                'records' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Notification id'),
                            'useridfrom' => new external_value(PARAM_INT, 'User from id'),
                            'useridto' => new external_value(PARAM_INT, 'User to id'),
                            'subject' => new external_value(PARAM_TEXT, 'The notification subject'),
                            'shortenedsubject' => new external_value(PARAM_TEXT, 'The notification subject shortened
                                with ellipsis'),
                            'text' => new external_value(PARAM_RAW, 'The message text formated'),
                            'fullmessage' => new external_value(PARAM_RAW, 'The message'),
                            'fullmessageformat' => new external_format_value('fullmessage'),
                            'fullmessagehtml' => new external_value(PARAM_RAW, 'The message in html'),
                            'smallmessage' => new external_value(PARAM_RAW, 'The shorten message'),
                            'contexturl' => new external_value(PARAM_RAW, 'Context URL'),
                            'contexturlname' => new external_value(PARAM_TEXT, 'Context URL link name'),
                            'timecreated' => new external_value(PARAM_INT, 'Time created'),
                            'timecreatedpretty' => new external_value(PARAM_TEXT, 'Time created in a pretty format'),
                            'timeread' => new external_value(PARAM_INT, 'Time read'),
                            'read' => new external_value(PARAM_BOOL, 'notification read status'),
                            'deleted' => new external_value(PARAM_BOOL, 'notification deletion status'),
                            'iconurl' => new external_value(PARAM_URL, 'URL for notification icon'),
                            'component' => new external_value(PARAM_TEXT, 'The component that generated the notification',
                                VALUE_OPTIONAL),
                            'eventtype' => new external_value(PARAM_TEXT, 'The type of notification', VALUE_OPTIONAL),
                            'customdata' => new external_value(PARAM_RAW, 'Custom data to be passed to the message processor.', VALUE_OPTIONAL),
                        ), 'message'
                    )
                ),
            )
        );
    }
}
