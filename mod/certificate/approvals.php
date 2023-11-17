<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * This page lists all the instances of certificate in a particular course
 *
 * @package    mod_certificate
 * @copyright  Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* INTG Customization Start : Approvals page to have the filters and participants list for certificate approval. */
require_once('../../config.php');
require_once('locallib.php');
require_once('certificate_filter_form.php');

require_login();
$cmid = optional_param('id', 0, PARAM_INT);
$courseid = optional_param('course', 0, PARAM_INT);
$userid = optional_param('user', 0, PARAM_INT);
$groupid = optional_param('group', 0, PARAM_INT);
$statuscomment = optional_param('statuscomment', 0, PARAM_INT);
$action = optional_param('action', 0, PARAM_INT);
$update = optional_param('update', 0, PARAM_INT);

$systemcontext = context_system::instance();
if (!is_siteadmin() && !has_capability('mod/certificate:approve', $systemcontext)) {
    print_error('nopermission', 'error', '', null, 'You do not have permission to access this page.');
}

$PAGE->set_context($systemcontext);
$PAGE->set_url('/mod/certificate/approvals.php');
$PAGE->set_title(get_string('certificateapproval', 'mod_certificate'));
$PAGE->set_heading(get_string('certificateapproval', 'mod_certificate'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/certificate/js/jquery.dataTables.min.js', true);
$PAGE->requires->js('/mod/certificate/js/certificate_approvals.js', true);

$PAGE->navbar->add(get_string('certificateapproval', 'mod_certificate'));

$url = new moodle_url($CFG->wwwroot.'/mod/certificate/approvals.php');

$mform = new certificate_filter_form(null, $data);

// Approval of the certificate.
if ($action == 1 && $update == 2) {
    $data = new stdClass();
    $data->status = 'approve';
    $data->statuscomment = 'approve';
    update_user_certificate_status($userid, $courseid, $data);
    redirect($url, 'Certificate Approved', null, \core\output\notification::NOTIFY_SUCCESS);
}

// Rejection of the certificate.
if ($action == 2 && $update == 2) {
    $data = new stdClass();
    $data->status = 'reject';
    $data->statuscomment = 'reject';
    update_user_certificate_status($userid, $courseid, $data);
    redirect($url, 'Certificate Rejected', null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

if ($action > 0 && $update == 1) {
    if ($action == 1) {
        $statuslabel = 'Approve';
    } else {
        $statuslabel = 'Reject';
    }

    $message = get_string('certificateconfirmation', 'mod_certificate', $statuslabel);
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot . '/mod/certificate/approvals.php',
        array('action' => $action, 'update' => 2, 'course' => $courseid, 'user' => $userid)), get_string('yes'));
    $formcancel = new single_button(new moodle_url($CFG->wwwroot . '/mod/certificate/approvals.php',
        array('action' => $action, 'update' => 0, 'course' => $courseid, 'user' => $userid)), get_string('no'));
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
} else {

    $setdata = array();
    if ($groupid > 0 ) {
        $courserecord = $DB->get_field('course', 'fullname', array('id' => $courseid));
        $grouprecord = $DB->get_record('groups', array('id' => $groupid));
        $setdata['course'] = $courseid;
        $setdata['group'] = $grouprecord->id;

        echo html_writer::script("
                        $(document).ready(function() {
                            get_course_groups_ajax ('filterdata', ".$courseid.", ".$groupid.");

                        });
                    ");
    }

    $mform->set_data($setdata);
    $mform->display();

    if ($mform->is_cancelled()) {
        redirect($url);
    } else if ($mform->get_data()) {
        $data = data_submitted();
        if ($data->course > 0) {
            echo show_participants_certificates($data->course, $data->group);
        }
    } else if ($courseid > 0 && $groupid > 0) {
        echo show_participants_certificates($courseid, $groupid);
    }

    if (empty($courseid) && empty($groupid)) {
        echo "<div class='alert alert-danger'>Please select Course and Batch to view participants.</div>";
    }
}

echo $OUTPUT->footer();
/* INTG Customization End. */

