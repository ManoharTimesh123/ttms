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
 * Bulk user upload forms
 *
 * @package    local
 * @subpackage user_management
 * @copyright  2007 Dan Poltawski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/editlib.php');


/**
 * Registration for Student with user information.
 *
 * @copyright  2007 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_registration_form extends moodleform {
    public function definition () {
        global $CFG;

        require_once($CFG->libdir . '/recaptchalib_v2.php');

        $mform = $this->_form;

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-sm-12 col-lg-8 offset-lg-2">');
        $mform->addElement('html', '<div class="bg-white inner_page px-5 py-4 rounded-lg shadow-lg">');
        $mform->addElement('html', '<h3 class="text-center">' . get_string('registrationform', 'local_self_registration') . '</h3>');
        $mform->addElement('html', '<span class="border-bottom d-block mb-4 pb-3 text-center text-secondary">' . get_string('fillingdetails', 'local_self_registration') . '</span>');

        $mform->addElement('text', 'username', get_string('username'), 'size="20"');
        $mform->addRule('username', get_string('usernamerequired', 'local_self_registration'), 'required');
        $mform->addHelpButton('username', 'username', 'auth');
        $mform->setType('username', PARAM_RAW);

        $mform->addElement('text', 'firstname', get_string('firstname'), 'size="20"');
        $mform->addRule('firstname', get_string('firstnamerequired', 'local_self_registration'), 'required');
        $mform->setType('firstname', PARAM_RAW);

        $mform->addElement('text', 'lastname', get_string('lastname'), 'size="20"');
        $mform->addRule('lastname', get_string('lastnameerequired', 'local_self_registration'), 'required');
        $mform->setType('lastname', PARAM_RAW);

        $mform->addElement('text', 'email', get_string('email')); // Add elements to your form.
        $mform->setType('email', PARAM_NOTAGS);                   // Set type of element.
        $mform->setDefault('email', '');        // Default value.

        $mform->addElement('text', 'phone', get_string('phone1'), 'maxlength="20" size="25"');
        $mform->setType('phone', core_user::get_property_type('phone1'));
        $mform->setForceLtr('phone');

        $mform->addElement('html', '<div class="register-new-password">');

        $mform->addElement('passwordunmask', 'newpassword', get_string('newpassword'), 'size="20"');
        $mform->addHelpButton('newpassword', 'newpassword');
        $mform->setType('newpassword', core_user::get_property_type('password'));

        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="row" >');
        $mform->addElement('html', '<div class="col-md-3">');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div class="col-md-9">');
        $mform->addElement('html', '<div>'.recaptcha_get_challenge_html(RECAPTCHA_API_URL, $CFG->recaptchapublickey, null).'</div>');
        $mform->addElement('static', 'error_message', '');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $this->add_action_buttons(false, get_string('registration', 'local_self_registration'));

        $mform->addElement('html', '</div></div></div>');

    }

    /**
     * Validate incoming form data.
     * @param array $usernew
     * @param array $files
     * @return array
     */
    public function validation($usernew, $files) {
        global $CFG, $DB;

        require_once($CFG->libdir . '/recaptchalib_v2.php');

        $errors = parent::validation($usernew, $files);

        $usernew = (object)$usernew;
        $user = $DB->get_record('user', array('id' => $usernew->id));
        if (isset($usernew->phone)) {
            $phone = (int)$usernew->phone;
            if ((strlen($usernew->phone) != 10) || ($phone < 1)) {
                $errors['phone'] = get_string('validatephone', 'local_self_registration');
            }
        }
        if (isset($usernew->username)) {
            if ($DB->record_exists_sql("SELECT * FROM {user} WHERE username='$usernew->username'")) {
                $errors['username'] = get_string('username', 'local_self_registration');
            }
        }

        // Validate email.
        if (!isset($usernew->email)) {
            // Mail not confirmed yet.
            $errors['email'] = "Required";
        } else if (!validate_email($usernew->email)) {
            $errors['email'] = get_string('invalidemail');
        } else if (($usernew->email !== $user->email) && empty($CFG->allowaccountssameemail)) {
            // Make a case-insensitive query for the given email address.
            $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid';
            $params = array(
                'email' => $usernew->email,
                'mnethostid' => $CFG->mnet_localhost_id,
            );

            // If there are other user(s) that already have the same email, show an error.
            if ($DB->record_exists_select('user', $select, $params)) {

                $errors['email'] = get_string('emailexists');
            }
        }
        if (empty($usernew->newpassword)) {
            $errors['newpassword'] = get_string('required');
        }
        if (!empty($usernew->newpassword)) {
            $errmsg = ''; // Prevent eclipse warning.
            if (!check_password_policy($usernew->newpassword, $errmsg, $usernew)) {
                $errors['newpassword'] = $errmsg;
            }
        }

        $formsubmitteddata = (array)data_submitted();

        if (isset($formsubmitteddata['g-recaptcha-response']) && !empty($formsubmitteddata['g-recaptcha-response'])) {
            $response = recaptcha_check_response(RECAPTCHA_VERIFY_URL,
                $CFG->recaptchaprivatekey,
                getremoteaddr(),
                $formsubmitteddata['g-recaptcha-response']
            );

            if (!$response['isvalid']) {
                $errors['error_message'] = $response['error'];
            }

        } else {
            $errors['error_message'] = get_string('recaptchacheckbox', 'local_self_registration');
        }

        return $errors;
    }
}
