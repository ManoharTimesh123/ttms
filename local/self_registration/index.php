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
 * @subpackage self registration
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('registration_form.php');
require_once('lib.php');

global $CFG, $DB, $OUTPUT, $PAGE;
require_once($CFG->dirroot.'/user/lib.php');

$mform = new local_registration_form();
$PAGE->set_heading('Registration');
echo $OUTPUT->header();
// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect('#');
} else if ($fromform = $mform->get_data()) {

      $_SESSION['username'] = $fromform->username;
      $_SESSION['email'] = $fromform->email;
      $_SESSION['password'] = $fromform->newpassword;
      $_SESSION['firstname'] = $fromform->firstname;
      $_SESSION['lastname'] = $fromform->lastname;
      $_SESSION['phone'] = $fromform->phone;
      redirect('otp.php');

} else {

    // Displays the.
    $mform->display();
}
echo $OUTPUT->footer();
