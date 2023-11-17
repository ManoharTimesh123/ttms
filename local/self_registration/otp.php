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
 * Bulk user registration script from a comma separated file
 *
 * @package    local
 * @subpackage Otp form
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('otp_form.php');
require_once('lib.php');

global $CFG, $DB, $OUTPUT, $PAGE;
$PAGE->requires->jquery();
$PAGE->requires->js('/local/self_registration/js/sendotp.js');

$mform = new local_otp_form();
$PAGE->set_heading('Verify OTP');
// Form processing and displaying is done here.

if ($mform->is_cancelled()) {

    // Handle form cancel operation, if cancel button is present on form.
    redirect("#");

} else if ($fromform = $mform->get_data()) {
    $user = new stdClass();
    $val = unset_otp($_SESSION['email']);
    if ($val) {

        $user = new stdClass();

        $user->username = $_SESSION['username'];
        $user->email = $_SESSION['email'];
        $user->password = $_SESSION['password'];
        $user->phone1 = $_SESSION['phone'];
        $user->firstname = $_SESSION['firstname'];
        $user->lastname = $_SESSION['lastname'];
        $user->mnethostid = 1;
        $user->confirmed = 1;

        $id = user_create_user($user);

        if ($id) {
            $check = complete_user_login(get_complete_user_data('id', $id));
            if ($check) {
                redirect($CFG->wwwroot."/local/custom_profile_field_update");
            }
        }

    }

} else {

    echo $OUTPUT->header();

    // Displays the form.
    if ($_SESSION['email']) {
        $user = new stdClass();
        $user->username = $_SESSION['username'];
        $user->email = $_SESSION['email'];
        $user->password = $_SESSION['password'];
        $user->phone = $_SESSION['phone'];
        $user->firstname = $_SESSION['firstname'];
        $user->lastname = $_SESSION['lastname'];
        $mform->display();
    }

    echo $OUTPUT->footer();
}
