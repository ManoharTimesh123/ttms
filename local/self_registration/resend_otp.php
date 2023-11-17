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
 * Bulk resend OTP
 *
 * @package    local_self_registration
 * @subpackage resend value
 * @copyright  2007 Dan Poltawski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$resend = optional_param('resend', 0, PARAM_INT);
$submit = optional_param('submit', 0, PARAM_INT);
$otp = optional_param('otp', null, PARAM_RAW);

global $DB;
if ($resend) {
    $email = $_SESSION['email'];
    if ($email) {

        $otp = set_otp($email);
        if ($otp) {
            $sendemail = send_email_otp($email, $otp);
            $sendemail = true;
            if ($sendemail) {
                echo 1;
            }
        }
    } else {
        echo 0;
    }
}
if ($submit && $otp) {
    $email = $_SESSION['email'];
    if ($email) {
        if (!$DB->record_exists_sql("SELECT * FROM {local_self_registration_otp} WHERE email='$email' and otp='$otp'")) {
            echo 1;
        } else {
            echo 0;
        }
    } else {
        echo 1;
    }
}
