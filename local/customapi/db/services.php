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
 * Custom API Service API - webservice definitions.
 *
 * @package    local_customapi
 */

defined('MOODLE_INTERNAL') || die();

// Web service functions made available by the plugin.
$functions = [
    'local_customapi_wall_post_add' => array(
        'classname' => 'local_customapi\\external\\wall',
        'methodname' => 'post_add',
        'description' => 'Create wall post',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),

    'local_customapi_wall_post_update' => array(
        'classname' => 'local_customapi\\external\\wall',
        'methodname' => 'post_update',
        'description' => 'Update wall post',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_customapi_wall_post_delete' => array(
        'classname' => 'local_customapi\\external\\wall',
        'methodname' => 'post_delete',
        'description' => 'Delete wall post',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_customapi_wall_post_get' => array(
        'classname' => 'local_customapi\\external\\wall',
        'methodname' => 'post_get',
        'description' => 'Get all post',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_customapi_wall_post_comment_add' => array(
        'classname' => 'local_customapi\\external\\wall',
        'methodname' => 'post_comment_add',
        'description' => 'Create wall post comment',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_customapi_wall_post_comment_delete' => array(
        'classname' => 'local_customapi\\external\\wall',
        'methodname' => 'post_comment_delete',
        'description' => 'Delete wall post comment',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_customapi_wall_post_like' => array(
        'classname' => 'local_customapi\\external\\wall',
        'methodname' => 'post_like',
        'description' => 'Add wall post like',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_customapi_wall_post_share' => array(
        'classname' => 'local_customapi\\external\\wall',
        'methodname' => 'post_share',
        'description' => 'Add wall post share',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_customapi_news_get' => array(
        'classname' => 'local_customapi\\external\\news',
        'methodname' => 'news_get',
        'description' => 'Get news',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_news_add' => array(
        'classname' => 'local_customapi\\external\\news',
        'methodname' => 'news_add',
        'description' => 'Create news',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_news_update' => array(
        'classname' => 'local_customapi\\external\\news',
        'methodname' => 'news_update',
        'description' => 'Update news',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_news_delete' => array(
        'classname' => 'local_customapi\\external\\news',
        'methodname' => 'news_delete',
        'description' => 'Delete news',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_announcement_get' => array(
        'classname' => 'local_customapi\\external\\announcement',
        'methodname' => 'announcement_get',
        'description' => 'Get announcement',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_blog_add' => array(
        'classname' => 'local_customapi\\external\\blog',
        'methodname' => 'blog_add',
        'description' => 'Create blog',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_blog_get' => array(
        'classname' => 'local_customapi\\external\\blog',
        'methodname' => 'blog_get',
        'description' => 'Get blog',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_blog_update' => array(
        'classname' => 'local_customapi\\external\\blog',
        'methodname' => 'blog_update',
        'description' => 'Update blog',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_blog_delete' => array(
        'classname' => 'local_customapi\\external\\blog',
        'methodname' => 'blog_delete',
        'description' => 'Delete blog',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_user_profile_get' => array(
        'classname' => 'local_customapi\\external\\user',
        'methodname' => 'user_profile_get',
        'description' => 'Get user profile',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_personal_training_calendar_get' => array(
        'classname' => 'local_customapi\\external\\personal_training_calendar',
        'methodname' => 'get_personal_training_calendar',
        'description' => 'get_personal_training_calendar data',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_annual_training_calendar_get' => array(
        'classname' => 'local_customapi\\external\\annual_training_calendar',
        'methodname' => 'get_annual_training_calendar',
        'description' => 'Get annual training calendar',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_training_transcript_get' => array(
        'classname' => 'local_customapi\\external\\training_transcript',
        'methodname' => 'get_training_transcript',
        'description' => 'Get training transcript',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_user_notifications_get' => array(
        'classname' => 'local_customapi\\external\\user',
        'methodname' => 'get_user_notifications',
        'description' => 'Get user notifications',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_training_get' => array(
        'classname' => 'local_customapi\\external\\training',
        'methodname' => 'get_training',
        'description' => 'Get training',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_training_activity_get' => array(
        'classname' => 'local_customapi\\external\\training',
        'methodname' => 'get_training_activity',
        'description' => 'Get training activity',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_courses_with_today_sessions_get' => array(
        'classname' => 'local_customapi\\external\\attendance',
        'methodname' => 'get_user_courses_with_today_sessions',
        'description' => 'get courses with today sessions',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_quiz_start_attempt_get' => array(
        'classname' => 'local_customapi\\external\\quiz',
        'methodname' => 'get_quiz_start_attempt',
        'description' => 'Get quiz start attempt',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_session_information_get' => array(
        'classname' => 'local_customapi\\external\\attendance',
        'methodname' => 'get_session_information',
        'description' => 'Method that retrieves the session data',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_user_session_information_get' => array(
        'classname' => 'local_customapi\\external\\attendance',
        'methodname' => 'get_user_session_information',
        'description' => 'Method that retrieves user session information',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_user_attendance_status_update' => array(
        'classname' => 'local_customapi\\external\\attendance',
        'methodname' => 'get_update_user_status',
        'description' => 'Method that updates the user status in a session.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_questionnaire_response_add' => array(
        'classname' => 'local_customapi\\external\\questionnaire',
        'methodname' => 'get_questionnaire_responses',
        'description' => 'Questionnaire Feedback submit.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_questionnaire_view_get' => array(
        'classname' => 'local_customapi\\external\\questionnaire',
        'methodname' => 'get_questionnaire_view',
        'description' => 'Questionnaire view.',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_certificates_by_courses_get' => array(
        'classname' => 'local_customapi\\external\\certificate',
        'methodname' => 'get_certificates_by_course',
        'description' => 'Returns a list of certificate instances in a provided set of courses.',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_view_certificate_get' => array(
        'classname' => 'local_customapi\\external\\certificate',
        'methodname' => 'get_view_certificate',
        'description' => 'Trigger the course module viewed event and update the module completion status.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_quiz_attempt_data_get' => array(
        'classname' => 'local_customapi\\external\\quiz',
        'methodname' => 'get_quiz_attempt_data',
        'description' => 'Get quiz attempt data',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),

    'local_customapi_quiz_attempt_summary_get' => array(
        'classname' => 'local_customapi\\external\\quiz',
        'methodname' => 'get_quiz_attempt_summary',
        'description' => 'Get quiz attempt summary',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_quiz_save_attempt_get' => array(
        'classname' => 'local_customapi\\external\\quiz',
        'methodname' => 'get_quiz_save_attempt',
        'description' => 'Get quiz save attempt',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_quiz_attempt_review_get' => array(
        'classname' => 'local_customapi\\external\\quiz',
        'methodname' => 'get_quiz_attempt_review',
        'description' => 'Get quiz attempt review.',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_quiz_process_attempt_get' => array(
        'classname' => 'local_customapi\\external\\quiz',
        'methodname' => 'get_quiz_process_attempt',
        'description' => 'Process responses during an attempt at a quiz and also deals with attempts finishing.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_issue_certificate_get' => array(
        'classname' => 'local_customapi\\external\\certificate',
        'methodname' => 'get_issue_certificate',
        'description' => 'Create new certificate record, or return existing record for the current user.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_issued_certificates_get' => array(
        'classname' => 'local_customapi\\external\\certificate',
        'methodname' => 'get_all_issued_certificates',
        'description' => 'Get the list of issued certificates for the current user.',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
    'local_customapi_book_view_get' => array(
        'classname' => 'local_customapi\\external\\book',
        'methodname' => 'get_view_book',
        'description' => 'Simulate the view.php web interface book: trigger events, completion, etc...',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'mod/book:read',
    ),
    'local_customapi_books_by_courses_get' => array(
        'classname' => 'local_customapi\\external\\book',
        'methodname' => 'get_all_books_by_courses',
        'description' => 'Returns a list of book instances in a provided set of courses,
                            if no courses are provided then all the book instances the user has access to will be returned.',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => '',
    ),
     'local_customapi_user_mooc_courses_get' => array(
        'classname' => 'local_customapi\\external\\mooccourses',
        'methodname' => 'get_all_user_mooc_courses',
        'description' => 'Returns a user mooc courses.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
     ),

];

// We define the services to install as pre-built services.
// They are not customisable in the UI by an administrator.
$services = [
    'Custom API Webservices' => [
        'functions' => [
            'local_customapi_wall_post_add',
            'local_customapi_wall_post_update',
            'local_customapi_wall_post_delete',
            'local_customapi_wall_post_get',
            'local_customapi_wall_post_comment_add',
            'local_customapi_wall_post_comment_delete',
            'local_customapi_wall_post_like',
            'local_customapi_wall_post_share',
            'local_customapi_news_get',
            'local_customapi_news_add',
            'local_customapi_news_update',
            'local_customapi_news_delete',
            'local_customapi_announcement_get',
            'local_customapi_blog_add',
            'local_customapi_blog_get',
            'local_customapi_blog_update',
            'local_customapi_blog_delete',
            'local_customapi_user_profile_get',
            'local_customapi_personal_training_calendar_get',
            'local_customapi_annual_training_calendar_get',
            'local_customapi_training_transcript_get',
            'local_customapi_user_notifications_get',
            'local_customapi_training_get',
            'local_customapi_training_activity_get',
            'local_customapi_courses_with_today_sessions_get',
            'local_customapi_session_information_get',
            'local_customapi_user_session_information_get',
            'local_customapi_user_attendance_status_update',
            'local_customapi_questionnaire_response_add',
            'local_customapi_questionnaire_view_get',
            'local_customapi_certificates_by_courses_get',
            'local_customapi_view_certificate_get',
            'local_customapi_issue_certificate_get',
            'local_customapi_issued_certificates_get',
            'local_customapi_book_view_get',
            'local_customapi_books_by_courses_get',
            'local_customapi_quiz_start_attempt_get',
            'local_customapi_quiz_attempt_data_get',
            'local_customapi_quiz_attempt_summary_get',
            'local_customapi_quiz_attempt_review_get',
            'local_customapi_quiz_save_attempt_get',
            'local_customapi_quiz_process_attempt_get',
            'local_customapi_user_mooc_courses_get',
            
        ],
        'requiredcapability' => '',
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'customapi',
        'downloadfiles' => 1,
        'uploadfiles' => 1,
    ]
];
