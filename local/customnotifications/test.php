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
 * Custom Notifications
 *
 * @package    local_customnotifications
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2023 TTMS Limited
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_once($CFG->dirroot.'/local/customnotifications/lib.php');

// send_sms_text(103, "Hi");

// course_feedback_send_message($learner = 180, $courseid = 8, $templatename = 'course_feedback');

// training_enrolment_send_message($learner = 180, $courseid = 8, $templatename = 'participant_enrollment', $roleid=0);

// // training_enrollment
// $log_data = new stdClass();
// $log_data->template = 'participant_enrollment';
// $log_data->touser = 180;
// $log_data->params = array('course' => 8);
// $log_data->emailtobesent = 1;
// $log_data->smstobesent = 1;
// $log_data->emailstatus = 'logged';
// $log_data->smsstatus = 'logged';
// $log_data->emailmessage = '';
// $log_data->smsmessage = '';
// $log_data->source = 'cron';
// $log_data->emailtimecreated = time();
// $log_data->emailtriggered = 0;
// $log_data->smstimecreated = time();
// $log_data->smstriggered = 0;

// notification_entry($log_data);
// // local_customnotifications_cron();

// course completion
/* course_completion_send_message($learner = 180, $courseid = 8, $templatename = 'course_completion',
                                 $emailtobesent = 1, $smstobesent = 1); */
// $log_data = new stdClass();
// $log_data->template = 'course_completion';
// $log_data->touser = 180;
// $log_data->params = array('course' => 8);
// $log_data->emailtobesent = 1;
// $log_data->smstobesent = 1;
// $log_data->emailstatus = 'logged';
// $log_data->smsstatus = 'logged';
// $log_data->emailmessage = '';
// $log_data->smsmessage = '';
// $log_data->source = 'realtime';
// $log_data->emailtimecreated = time();
// $log_data->emailtriggered = time();
// $log_data->smstimecreated = time();
// $log_data->smstriggered = time();

// notification_entry($log_data);

