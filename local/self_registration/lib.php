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
 * Form for editing HTML block instances.
 *
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_self_registration
 * @category  files
 * @param string $email email area
 * @param string $otp otp area
 * @todo MDL-36050 improve capability check on stick blocks, so we can check user capability before sending images.
 */
function send_email_otp($email, $otp) {
        global $CFG;
        $user = new stdClass();
        $user->email = $email;
        $user->id = -1;
        $supportuser = core_user::get_support_user();
        $messagehtml = '<html><body><h1>' . get_string('otpverificationmsg', 'local_self_registration') . ':' . $otp . '</h1></body></html>';
        $messagetext = html_to_text($messagehtml);
        $user->mailformat = 1;
        $subject = get_string('otpverificationsubject', 'local_self_registration');
        // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
        // INTG customnotifications trigger for the OTP Generation.
        require_once($CFG->dirroot.'/local/customnotifications/lib.php');
        signup_otp_send_message($user, $otp, $messagehtml, $templatename = 'signup_otp', $emailtobesent = 1);
        return '';
}

function set_otp($email) {
    GLOBAL $DB;
    $otp = rand(100000, 999999);
    $dataobject = new stdclass();
    $dataobject->email = $email;
    $dataobject->otp = $otp;
    $dataobject->timerequest = time();
    if ($DB->record_exists_sql("SELECT * FROM {local_self_registration_otp} WHERE email = '$email'")) {
        $DB->execute("UPDATE {local_self_registration_otp} SET otp = $otp WHERE email = '$email'");
    } else {
        $DB->insert_record('local_self_registration_otp', $dataobject);
    }
    return $otp;
}

function unset_otp($email) {
    GLOBAL $DB;
    $value = $DB->execute("DELETE FROM {local_self_registration_otp} WHERE email = '$email'");
    return $value;
}
