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
 * @author    Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2023 TTMS Limited
 */

/*
 * Function for the trigger for the custom notification for course completion
 * templatename = 'course_completion'
 */
function course_completion_send_message($learner, $courseid, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;
    include_once($CFG->dirroot . '/grade/querylib.php');
    require_once($CFG->dirroot . '/grade/report/lib.php');
    require_once($CFG->libdir . '/grade/constants.php');
    require_once($CFG->libdir . '/grade/grade_category.php');
    require_once($CFG->libdir . '/grade/grade_item.php');
    require_once($CFG->libdir . '/grade/grade_grade.php');
    require_once($CFG->libdir . '/grade/grade_scale.php');
    require_once($CFG->libdir . '/grade/grade_outcome.php');

    $touser = $DB->get_record('user', array('id' => $learner));
    $tousername = fullname($touser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;

    $sql = "SELECT r.name FROM {role} r
            JOIN {role_assignments} ra ON ra.roleid=r.id
            JOIN {context} c ON c.id= ra.contextid
            WHERE c.contextlevel = 50 AND c.instanceid= :courseid AND ra.userid = :learnerid";
    $userroledata = $DB->get_record_sql($sql, array('courseid' => $courseid, 'learnerid' => $learner));
    $rolename = $userroledata->name;

    $centername = $centernames = '';
    $ccarray = $bccarray = array();
    $course = $DB->get_record('course', array('id' => $courseid));
    $coursedata = $DB->get_record('local_course_details', array('course' => $courseid));
    $modality = $DB->get_record('local_modality', array('id' => $coursedata->modality));
    $coursetype = $DB->get_record('local_coursetype', array('id' => $coursedata->coursetype));

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;
    $fromusername = fullname($fromuser, true);

    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($courseid);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));

    $subject = str_replace(array('{coursename}'), array($course->fullname), $template->subject);

    $courseitem = grade_item::fetch_course_item($courseid);
    // Get the stored grade.
    $coursegrade = new grade_grade(array('itemid' => $courseitem->id, 'userid' => $learner));
    $coursegrade->grade_item =& $courseitem;
    $gradecourse = round(grade_format_gradevalue($coursegrade->finalgrade, $coursegrade->grade_item, true,
        GRADE_DISPLAY_TYPE_PERCENTAGE), 0);

    if ($gradecourse) {
        $templatecontent = str_replace(array('Grade: {grade}'), array('Grade: '.$gradecourse), $template->messagecontent);
        $plaintextcontent = str_replace(array('Grade: {grade}'), array('Grade: '.$gradecourse), $template->plaintext);
    } else {
        $templatecontent = str_replace(array('Grade: {grade}'), array(''), $template->messagecontent);
        $plaintextcontent = str_replace(array('Grade: {grade}'), array(''), $template->plaintext);
    }

    $content = str_replace(
        array('{coursename}', '{coursecode}', '{courseidnumber}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $course->idnumber, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name,
            customdateformat('HOUR_WITH_MINUTES', $course->startdate), $endtime, $rolename,
            '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $templatecontent);

    $plaintext = str_replace(
        array('{coursename}', '{coursecode}', '{courseidnumber}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $course->idnumber, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $plaintextcontent);

    $smstext = str_replace(
        array('{coursename}', '{coursecode}', '{courseidnumber}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}', '{touser_sms}'),
        array($course->fullname, $course->shortname, $course->idnumber, $fromusername, $ccusernamesms, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, $CFG->wwwroot, $tousersms),
        $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray, $file,
            $filename);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for signup OTP
 * templatename = 'signup_otp'
 * This function is different as it is having static email to user call
 */
function signup_otp_send_message($user, $otp, $messagehtml, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $CFG;

    $messagetext = html_to_text($messagehtml);
    $tousername = $user->firstname.' '.$user->lastname;
    $user->mailformat = 1;

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));
    $subject = str_replace(array('{otp}'), array($otp), $template->subject);

    $content = str_replace(array('{otp}', '{fromuser}', '{ccusers}', '{touser}', '{siteurl}'),
                            array($otp, $fromusername, $ccusername, $tousername,
                            '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>'),
                            $template->messagecontent);
    $plaintext = html_to_text($content);
    $smstext = str_replace(array('{otp}', '{fromuser}', '{ccusers}', '{touser}', '{siteurl}'),
                            array($otp, $fromusername, $ccusernamesms, $tousername, $CFG->wwwroot),
                            $template->smstext);

    $templateid = $template->smstemplateid;
    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        email_to_user($user, $supportuser, $subject , $plaintext , $content , '' , '' , true);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for course feedback
 * templatename = 'course_feedback'
 */
function course_feedback_send_message($learner, $courseid, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;

    $touser = $DB->get_record('user', array('id' => $learner));
    $tousername = fullname($touser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;
    $userroledata = $DB->get_record_sql("SELECT r.name FROM {role} r
                                    JOIN {role_assignments} ra on ra.roleid=r.id
                                    JOIN {context} c on c.id= ra.contextid
                                    WHERE c.contextlevel = 50 AND c.instanceid = $courseid AND ra.userid = $learner");
    $rolename = $userroledata->name;
    $course = $DB->get_record('course', array('id' => $courseid));
    $coursedata = $DB->get_record('local_course_details', array('course' => $courseid));

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;
    $fromusername = fullname($fromuser, true);

    $ccarray = $bccarray = array();
    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($courseid);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));
    $subject = str_replace(array('{coursename}'), array($course->fullname), $template->subject);

    $templatecontent = $template->messagecontent;
    $plaintextcontent = $template->plaintext;

    $content = str_replace(
            array('{coursename}', '{coursecode}', '{courseidnumber}', '{fromuser}', '{ccusers}', '{touser}',
                '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
                '{siteurl}', '{courseurl}'),
            array($course->fullname, $course->shortname, $course->idnumber, $fromusername, $ccusername, $tousername,
                customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
                $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
                '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.
                    $CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
            $templatecontent);

    $plaintext = str_replace(
        array('{coursename}', '{coursecode}', '{courseidnumber}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $course->idnumber, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.
                $CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $plaintextcontent);

    $smstext = str_replace(
        array('{coursename}', '{coursecode}', '{courseidnumber}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{touser_sms}'),
        array($course->fullname, $course->shortname, $course->idnumber, $fromusername, $ccusernamesms, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, $CFG->wwwroot, $tousersms),
        $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for Training Alignment
 * templatename = 'training_alignment'
 * function call at enrollment
 */
function training_alignment_send_message($learner, $courseid, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;

    $touser = $DB->get_record('user', array('id' => $learner));
    $tousername = fullname($touser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;
    $userroledata = $DB->get_record_sql("SELECT r.name FROM {role} r
                                    JOIN {role_assignments} ra on ra.roleid = r.id
                                    JOIN {context} c on c.id = ra.contextid
                                    WHERE c.contextlevel = 50 AND c.instanceid = $courseid AND ra.userid = $learner");
    $rolename = $userroledata->name;

    $course = $DB->get_record('course', array('id' => $courseid));
    $coursedata = $DB->get_record('local_course_details', array('course' => $courseid));

    $modality = $DB->get_record('local_modality', array('id' => $coursedata->modality));
    $coursetype = $DB->get_record('local_coursetype', array('id' => $coursedata->coursetype));
    $trainingschool = $DB->get_record('local_schools', array('id' => $coursedata->venue));

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;
    $fromusername = fullname($fromuser, true);

    $ccarray = $bccarray = array();
    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($courseid);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));

    $subject = str_replace(array('{coursename}'), array($course->fullname), $template->subject);
    $templatecontent = $template->messagecontent;
    $plaintextcontent = $template->plaintext;

    $content = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $templatecontent);

    $plaintext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $plaintextcontent);

    $smstext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
                '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
                '{siteurl}', '{touser_sms}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusernamesms, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, $CFG->wwwroot, $tousersms),
        $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for Venue approval request
 * templatename = 'venue_approval'
 */
function venue_approval_send_message($hos, $schoolid, $courseid, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;

    $touser = $DB->get_record('user', array('id' => $hos));
    $tousername = fullname($touser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;

    $rolename = 'HOS';
    $course = $DB->get_record('course', array('id' => $courseid));
    $coursedata = $DB->get_record('local_course_details', array('course' => $courseid));
    $school = $DB->get_record('local_schools', array('id' => $schoolid));

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;
    $fromusername = fullname($fromuser, true);

    $ccarray = $bccarray = array();
    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($courseid);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));

    $subject = str_replace(array('{coursename}'), array($course->fullname), $template->subject);
    $templatecontent = $template->messagecontent;
    $plaintextcontent = $template->plaintext;

    $content = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $school->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $templatecontent);

    $plaintext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
                '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
                 '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $school->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $plaintextcontent);

    $smstext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
                '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
                 '{siteurl}', '{touser_sms}'),
        array($course->fullname, $course->shortname, $school->name, $fromusername, $ccusernamesms, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, $CFG->wwwroot, $tousersms),
        $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for training pending activity
 * templatename = 'training_activity_pending'
 */
function course_pending_activity_send_message($learner, $modid, $courseid, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;

    $touser = $DB->get_record('user', array('id' => $learner));
    $tousername = fullname($touser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;

    $userroledata = $DB->get_record_sql("SELECT r.name FROM {role} AS r
                                    JOIN {role_assignments} AS ra ON ra.roleid = r.id
                                    JOIN {context} AS c ON c.id = ra.contextid
                                    WHERE c.contextlevel = 50 AND c.instanceid = $courseid AND ra.userid = $learner");
    $rolename = $userroledata->name;

    $course = $DB->get_record('course', array('id' => $courseid));
    $coursedata = $DB->get_record('local_course_details', array('course' => $courseid));

    $sql = "SELECT cm.id, cm.instance, m.name
                FROM {course_modules} AS cm
                JOIN {modules} AS m ON m.id = cm.module
                WHERE cm.id = :cmid AND cm.visible = :visible AND cm.course = :courseid
            ";
    $moddetails = $DB->get_record($sql, array('cmid' => $modid, 'visible' => 1, 'courseid' => $courseid));
    if ($moddetails->name) {
        $activityname = $DB->get_field('mod_'.$moddetails->name, 'name', array('id' => $moddetails->instance));
        $status = 'details';
    } else {
        $activityname = 'N/A';
        $status = 'details';
    }

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;
    $fromusername = fullname($fromuser, true);

    $ccarray = $bccarray = array();
    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($courseid);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));

    $subject = str_replace(array('{coursename}'), array($course->fullname), $template->subject);
    $templatecontent = $template->messagecontent;
    $plaintextcontent = $template->plaintext;

    $content = str_replace(
        array('{coursename}', '{coursecode}', '{activityname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{status}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $activityname, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $status, customdateformat('HOUR_WITH_MINUTES', $course->startdate), $endtime,
            $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $templatecontent);

    $plaintext = str_replace(
        array('{coursename}', '{coursecode}', '{activityname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{status}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $activityname, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $status, customdateformat('HOUR_WITH_MINUTES', $course->startdate), $endtime,
             $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
             '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $plaintextcontent);

    $smstext = str_replace(
        array('{coursename}', '{coursecode}', '{activityname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{status}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{touser_sms}'),
        array($course->fullname, $course->shortname, $activityname, $fromusername, $ccusernamesms, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $status, customdateformat('HOUR_WITH_MINUTES', $course->startdate), $endtime,
             $rolename, $CFG->wwwroot, $tousersms),
        $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for user profile update
 * templatename = 'user_profile_update'
 */
function profile_update_send_message($user, $usermodified, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;

    $touser = $DB->get_record('user', array('id' => $user));
    $tousername = fullname($touser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;

    $modifieduser = $DB->get_record('user', array('id' => $usermodified));
    $modifiedusername = fullname($modifieduser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;
    $rolename = 'Participant';

    $course = $DB->get_record('course', array('id' => 1));

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;
    $fromusername = fullname($fromuser, true);

    $ccarray = $bccarray = array();
    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($course->id);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));

    $subject = str_replace(array('{touser}'), array($tousername), $template->subject);
    $templatecontent = $template->messagecontent;
    $plaintextcontent = $template->plaintext;

    $content = str_replace(
        array('{coursename}', '{coursecode}', '{teachername}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}'),
        array($course->fullname, $course->shortname, $modifiedusername, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>'),
        $templatecontent);

    $plaintext = str_replace(
        array('{coursename}', '{coursecode}', '{teachername}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}'),
        array($course->fullname, $course->shortname, $modifiedusername, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>'),
        $plaintextcontent);

    $smstext = str_replace(
        array('{coursename}', '{coursecode}', '{teachername}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{touser_sms}'),
        array($course->fullname, $course->shortname, $modifiedusername, $fromusername, $ccusernamesms, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, $CFG->wwwroot, $tousersms),
        $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for New Announcement in the system
 * templatename = 'announcement'
 */
function announcement_send_message($learner, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;

    $touser = $DB->get_record('user', array('id' => $learner));
    $tousername = fullname($touser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;

    $rolename = get_string('participant', 'local_customnotifications');
    $course = $DB->get_record('course', array('id' => 1));

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;
    $fromusername = fullname($fromuser, true);

    $ccarray = $bccarray = array();
    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($course->id);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));

    $subject = $template->subject;
    $templatecontent = $template->messagecontent;
    $plaintextcontent = $template->plaintext;

    $content = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>'),
        $templatecontent);

    $plaintext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>'),
        $plaintextcontent);

    $smstext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}', '{touser_sms}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusernamesms, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, $CFG->wwwroot, $tousersms),
        $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for NODAL OFFICER to plan the training
 * templatename = 'nodal_training_planning'
 */
function nodal_training_planning_send_message($nodal, $courseid, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;

    $touser = $DB->get_record('user', array('id' => $nodal));
    $tousername = fullname($touser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;

    $rolename = get_string('nodalofficer', 'local_customnotifications');
    $course = $DB->get_record('course', array('id' => $courseid));
    $coursedata = $DB->get_record('local_course_details', array('course' => $courseid));

    $modality = $DB->get_record('local_modality', array('id' => $coursedata->modality));
    $trainingschool = $DB->get_record('local_schools', array('id' => $coursedata->venue));

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;
    $fromusername = fullname($fromuser, true);

    $ccarray = $bccarray = array();
    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($courseid);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));

    $subject = str_replace(array('{coursename}'), array($course->fullname), $template->subject);
    $templatecontent = $template->messagecontent;
    $plaintextcontent = $template->plaintext;

    $content = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $templatecontent);

    $plaintext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $plaintextcontent);

    $smstext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{type}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}', '{touser_sms}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusernamesms, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $coursetype->name, customdateformat('HOUR_WITH_MINUTES', $course->startdate),
            $endtime, $rolename, $CFG->wwwroot, $tousersms),
        $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for DIET to plan the training
 * templatename = 'diet_training_planning'
 */
function diet_training_planning_send_message($diet, $nodal, $courseid, $templatename, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;

    $touser = $DB->get_record('user', array('id' => $diet));
    $tousername = fullname($touser, true);
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;

    $nodal = $DB->get_record('user', array('id' => $nodal));
    $nodalname = fullname($nodal, true);

    $rolename = get_string('diet', 'local_customnotifications');

    $course = $DB->get_record('course', array('id' => $courseid));
    $coursedata = $DB->get_record('local_course_details', array('course' => $courseid));

    $modality = $DB->get_record('local_modality', array('id' => $coursedata->modality));
    $trainingschool = $DB->get_record('local_schools', array('id' => $coursedata->venue));

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;
    $fromusername = fullname($fromuser, true);

    $ccarray = $bccarray = array();
    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($courseid);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));

    $subject = str_replace(array('{coursename}'), array($course->fullname), $template->subject);
    $templatecontent = $template->messagecontent;
    $plaintextcontent = $template->plaintext;

    $content = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{nodalofficer}', '{starttime}', '{endtime}', '{userrole}',
             '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $nodalname, customdateformat('HOUR_WITH_MINUTES', $course->startdate), $endtime,
            $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $templatecontent);

    $plaintext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{nodalofficer}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}', '{courseurl}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusername, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $nodalname, customdateformat('HOUR_WITH_MINUTES', $course->startdate), $endtime,
            $rolename, '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
            '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>'),
        $plaintextcontent);

    $smstext = str_replace(
        array('{coursename}', '{coursecode}', '{schoolname}', '{fromuser}', '{ccusers}', '{touser}',
            '{startdate}', '{enddate}', '{modality}', '{nodalofficer}', '{starttime}', '{endtime}', '{userrole}',
            '{siteurl}', '{touser_sms}'),
        array($course->fullname, $course->shortname, $trainingschool->name, $fromusername, $ccusernamesms, $tousername,
            customdateformat('DATE_WITHOUT_TIME', $course->startdate), $enddate, $modality->name, $nodalname, customdateformat('HOUR_WITH_MINUTES', $course->startdate), $endtime,
            $rolename, $CFG->wwwroot, $tousersms),
        $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger for the custom notification for user enrollment for training
 * templatename = 'participant_enrollment'
 */
function training_enrolment_send_message($learner, $courseid, $templatename, $roleid=0, $emailtobesent = 0, $smstobesent = 0) {
    global $DB, $USER, $CFG;

    $touser = $DB->get_record('user', array('id' => $learner));
    $tousersms = $touser->firstname;
    $tousermobile = $touser->phone1;
    $tousername = fullname($touser, true);

    $course = $DB->get_record('course', array('id' => $courseid));
    $coursedata = $DB->get_record('local_course_details', array('course' => $courseid));
    $rolename = $facilitatorname = '';

    $modality = $DB->get_record('local_modality', array('id' => $coursedata->modality));
    $coursetype = $DB->get_record('local_coursetype', array('id' => $coursedata->coursetype));
    $centername = $centernames = '';

    $info = new stdClass();
    $info->username = fullname($touser, true);
    $info->name = $course->fullname;
    $info->url = get_course_url_from_courseid($courseid);
    $info->eventtype = 'custom_notify';
    $info->timeupdated = time();

    if ($roleid > 0) {
        $sql = "SELECT r.name FROM {role} AS r
            JOIN {role_assignments} AS ra ON ra.roleid=r.id
            JOIN {context} AS c ON c.id= ra.contextid
            WHERE c.contextlevel = 50 AND c.instanceid = :courseid AND ra.userid = :learner";
        $params = array('courseid' => $courseid, 'learner' => $learner);
        $sql .= " and ra.roleid = :roleid";
        $params['roleid'] = $roleid;

        $userroledata = $DB->get_record_sql($sql, $params);
        $rolename = $userroledata->name;
    }
    $ccarray = $bccarray = array();

    $template = $DB->get_record('local_notification_templates', array('code' => $templatename));

    $subject = str_replace(array('{coursename}'), array($course->fullname), $template->subject);

    $content = str_replace(
            array('{coursename}', '{coursecode}', '{courseidnumber}', '{fromuser}', '{ccusers}', '{touser}',
                '{startdate}', '{enddate}', '{modality}', '{coursetype}', '{centername}', '{starttime}', '{endtime}', '{userrole}',
                 '{siteurl}', '{courseurl}', '{facilitator}'),
            array($course->fullname, $course->shortname, $course->idnumber, $fromusername, $ccusername, $tousername,
                $startdate, $enddate, $modality->name, $coursetype->name, $centername, $starttime, $endtime, $rolename,
                '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
                '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.
                    $CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>', $facilitatorname),
            $template->messagecontent);

    $plaintext = str_replace(
            array('{coursename}', '{coursecode}', '{courseidnumber}', '{fromuser}', '{ccusers}', '{touser}',
                '{startdate}', '{enddate}', '{modality}', '{coursetype}', '{centername}', '{starttime}', '{endtime}', '{userrole}',
                 '{siteurl}', '{courseurl}', '{facilitator}'),
            array($course->fullname, $course->shortname, $course->idnumber, $fromusername, $ccusername, $tousername,
                $startdate, $enddate, $modality->name, $coursetype->name, $centername, $starttime, $endtime, $rolename,
                '<a href="'.$CFG->wwwroot.'">'.$CFG->wwwroot.'</a>',
                '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">'.
                    $CFG->wwwroot.'/course/view.php?id='.$courseid.'</a>', $facilitatorname),
            $template->plaintext);

    $smstext = str_replace(
            array('{coursename}', '{coursecode}', '{courseidnumber}', '{fromuser}', '{ccusers}', '{touser}',
                    '{startdate}', '{enddate}', '{modality}', '{coursetype}', '{centername}', '{starttime}', '{endtime}',
                    '{userrole}', '{siteurl}', '{courseurl}', '{facilitator}', '{touser_sms}'),
            array($course->fullname, $course->shortname, $course->idnumber, $fromusername, $ccusernamesms, $tousername,
                $startdate, $enddate, $modality->name, $coursetype->name, $centername, $starttime, $endtime, $rolename,
                $CFG->wwwroot, $ccusername, $tousersms),
            $template->smstext);

    $templateid = $template->smstemplateid;
    $plaintext = html_to_text($plaintext);

    // TODO: This below code should be replaced by picking up the cisrcular PDF.
    $batchingid = $DB->get_field('local_batching', 'id', array('course' => $courseid, 'status' => 'launched'));
    $itemid = 0;
    $file = $filename = '';
    if ($batchingid > 0) {
        $itemid = $batchingid;
        $systemcontext = context_system::instance();
        $fs = get_file_storage();

        $sql = "SELECT * FROM {files}
                WHERE contextid = :contextid and component = 'local_batching' and filearea = 'attachment'
                and filesize != 0 and itemid= :itemid";
        $filerecord = $DB->get_record_sql($sql, array('contextid' => $systemcontext->id, 'itemid' => $itemid));
        $filename = $filerecord->filename;

        $file = $fs->get_file($systemcontext->id, 'local_batching', 'attachment', $itemid, '/', $filename);
    }

    $emailoutput = $smsoutput = '';
    if ($emailtobesent == 1) {
        $emailoutput = send_message($touser, $fromuser, $subject, $plaintext, $content, $info, $ccarray, $bccarray, $file,
            $filename);
    }

    if ($smstobesent == 1) {
        $smsoutput = send_nic_sms_text($tousermobile, $smstext, $templateid);
    }

    return array('emailoutput' => $emailoutput, 'smsoutput' => $smsoutput);
}

/*
 * Function for the trigger of the default messaging notfiication on moodle
 */
function send_message($touser, $fromuser, $postsubject, $posttext, $posthtml, $info, $cc, $bcc, $attachment=null, $attachname='') {
    global $CFG, $DB;

    require_once($CFG->libdir.'/messagelib.php');
    require_once($CFG->dirroot . '/message/lib.php');
    $eventdata = new \core\message\message();

    $supportuser = core_user::get_support_user();
    $fromuser = $supportuser;

    $eventdata->modulename = 'local_customnotifications';
    $eventdata->userfrom = $fromuser;
    $eventdata->userto = $touser;
    $eventdata->subject = $postsubject;
    $eventdata->fullmessage = $posttext;
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml = $posthtml;
    $eventdata->smallmessage = $postsubject;
    $eventdata->name = $info->eventtype;
    $eventdata->component = 'local_customnotifications';
    $eventdata->notification = 1;
    $eventdata->contexturl = $info->url;
    $eventdata->contexturlname = $info->name;
    if ($attachment) {
        $eventdata->attachment = $attachment;
        $eventdata->attachname = $attachname;
    }
    $eventdata->anonymous = 'true';
    $eventdata->cc = $cc;
    $eventdata->bcc = $bcc;
    $return = message_send($eventdata);
    return $return;
}

/*
*
* Function to add and update template for notifications
* data - details submitted in the template form
*
*/
function add_update_template($data) {
    global $DB, $USER, $CFG;
    $templatedata = new stdClass();
    $templatedata = $data;
    $format = $data->messagecontent['format'];
    $templatedata->messagecontent = $data->messagecontent['text'];
    $templatedata->messagecontentformat = $format;
    $textformat = $data->plaintext['format'];
    $templatedata->plaintext = $data->plaintext['text'];
    $templatedata->smstext = $data->smstext;
    $templatedata->smstemplateid = $data->smstemplateid;
    $templatedata->name = $data->name;
    $templatedata->workflow = $data->workflow;
    $templatedata->attachments = 0;
    $templatedata->bccusers = 0;
    $templatedata->fromuser = -1;
    $templatedata->touser = implode(',', $data->touser);
    $templatedata->ccusers = implode(',', $data->ccusers);

    if ($data->id > 0) {
        $templateid = $data->id;
        $templatedata->usermodified = $USER->id;
        $templatedata->timemodified = time();

        $DB->update_record('local_notification_templates', $templatedata);
    } else {
        $templatedata->usercreated = $USER->id;
        $templatedata->timecreated = time();

        $templateid = $DB->insert_record('local_notification_templates', $templatedata);

    }
    return $templateid;

}

/*
 * Function for the trigger sms for the customnotifications
 */
function send_nic_sms_text($tousermobile, $smstext, $templateid) {
    global $CFG;
    return get_string('smsdisabled', 'local_customnotifications');
    $config = get_config('local_customnotifications');
    if (empty($config->enablesms)) {
        return get_string('smssettingsdisabled', 'local_customnotifications');
    }
    $validphone = 0;
    if (empty($tousermobile) || empty($smstext) || empty($templateid)) {
        return get_string('invalidsmstrigger', 'local_customnotifications');
    }
    if (strlen($tousermobile) > 10 ) {
        if (substr($tousermobile, 0, 3) == '+91') {
            $tousermobile = substr($tousermobile, 3);
        }
    }
    if (is_numeric($tousermobile) && strlen($tousermobile) == 10) {
        $validphone = 1;
        $tousermobile = '91'.$tousermobile;
    }

    $nicsmsendpoint = $config->sms_endpoint;
    $nicsmsusername = $config->sms_username;
    $smspin = $config->sms_pin;
    $signature = $config->sms_signature;
    $mobileno = $tousermobile;
    $messagecontent = $smstext;
    $dltentityid = $config->sms_dlt_entity_id;
    $dlttemplateid = $templateid;
    $messagecontent = str_replace('{enter}', chr(10), $messagecontent);

    $url = $nicsmsendpoint;
    $url .= 'username='.$nicsmsusername;
    $url .= '&pin='.urlencode($smspin);
    $url .= '&signature='.$signature;
    $url .= '&mnumber='.$mobileno;
    $url .= '&message='.urlencode($messagecontent);
    $url .= '&dlt_entity_id='.$dltentityid;
    $url .= '&dlt_template_id='.$dlttemplateid;

    /* if ($validphone == 1) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        $result = curl_exec($ch);
        curl_close($ch);
    } else {
        $result = 'error: Invalid Phone number';
    } */

    return $result;
}

/*
 * Function for the cron trigger for the local_customnotifications
 */
function local_customnotifications_cron() {
    global $DB;

    $sql = "SELECT * FROM {local_notification_records}
            WHERE source = :source
            AND (emailstatus = :emailstatus OR smsstatus = :smsstatus)
            ORDER BY id DESC";
    $pendingnotifications = $DB->get_records_sql($sql, array('source' => 'cron', 'emailstatus' => 'logged',
                                                                'smsstatus' => 'logged'), 0, 500);

    if (!empty($pendingnotifications)) {
        foreach ($pendingnotifications as $pendingnotification) {

            switch ($pendingnotification->template) {
                case 'participant_enrollment':
                    $params = json_decode($pendingnotification->params);
                    $coursedata = json_decode($params);
                    $output = training_enrolment_send_message($pendingnotification->touser,
                                                                    $coursedata->course,
                                                                    $pendingnotification->template,
                                                                    $roleid = 0,
                                                                    $pendingnotification->emailtobesent,
                                                                    $pendingnotification->smstobesent
                                                                );

                    if ($output['emailoutput']) {
                        $pendingnotification->emailstatus = 'triggered';
                        $pendingnotification->emailmessage = $output['emailoutput'];
                        $pendingnotification->emailtimetriggered = time();
                    }

                    if ($output['smsoutput']) {
                        $pendingnotification->smsstatus = 'triggered';
                        $pendingnotification->smsmessage = $output['smsoutput'];
                        $pendingnotification->smstimetriggered = time();
                    }
                    $DB->update_record('local_notification_records', $pendingnotification);
                    break;
                default:
                    // No notification for default.
                    break;
            }

        }
    }
    // course_feedback_send_message($learner, $courseid, $templatename = 'course_feedback');
}

/*
*
* Function to inserting data in local_notification_records
* data - details from each notification trigger in local_notifications_records table
*
*/
function notification_entry($data) {
    global $DB;

    $logdata = new stdClass();
    $logdata->template = $data->template;
    $logdata->touser = $data->touser;
    $logdata->params = json_encode($data->params);
    $logdata->emailtobesent = $data->emailtobesent;
    $logdata->smstobesent = $data->smstobesent;
    $logdata->source = $data->source;
    if ($data->emailtobesent == 1) {
        $logdata->emailstatus = $data->emailstatus;
        $logdata->emailmessage = $data->emailmessage;
        $logdata->emailtimecreated = $data->emailtimecreated;
        if ($logdata->source != 'cron') {
            $data->emailtriggered = $data->emailtimecreated;
        }
        $logdata->emailtriggered = $data->emailtriggered;
    }

    if ($data->smstobesent == 1) {
        $logdata->smsstatus = $data->smsstatus;
        $logdata->smsmessage = $data->smsmessage;
        $logdata->smstimecreated = $data->smstimecreated;
        if ($logdata->source != 'cron') {
            $data->smstriggered = $data->smstimecreated;
        }
        $logdata->smstriggered = $data->smstriggered;
    }

    $id = $DB->insert_record('local_notification_records', $logdata);
    return $id;
}

function get_course_url_from_courseid($courseid) {
    global $CFG, $DB;
    $url = '';

    if (isset($courseid) && $DB->record_exists('course', array('id' => $courseid))) {
        $url = $CFG->wwwroot . '/course/view.php?id=' . $courseid;
    }

    return $url;
}
