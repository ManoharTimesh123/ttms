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
 * @subpackage otp_form
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

class local_otp_form extends moodleform {
    public function definition () {
        $mform = $this->_form;

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-sm-12 col-lg-8 offset-lg-2">');
        $mform->addElement('html', '<div class="bg-white inner_page px-5 py-4 mb-4 rounded-lg shadow-lg">');
        $mform->addElement('html', '<h3>Mobile phone verification</h3>');
        $mform->addElement('html', '<span class="border-bottom d-block mb-4 pb-3 text-gray">
                                        ' . get_string('sentoptcode', 'local_self_registration') . '
                                    </span>');

        $mform->addElement('text', 'otp', get_string('otp', 'local_self_registration')); // Add elements to your form.

        $mform->addElement('html', '<div class="registration-opt-btn">');

        $mform->addElement('button', 'resendotp', get_string("resendotp", 'local_self_registration'));
        $mform->addElement('button', 'complete', get_string("submit", 'local_self_registration'));

        $mform->addElement('html', '</div></div></div');

    }
}
