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

class local_directory extends moodleform {
    public function definition () {
        global $DB, $CFG;
        $allfields = $DB->get_records('local_user_public_fields');
        $mform = $this->_form;
        $mform->addElement('html', '<div class="heading pt-4">' . get_string('field', 'local_directory') .'</div>');
        foreach ($allfields as $allfieldsvalue) {
            $mform->addElement('html', '<div class="row"><div class="col-md-9 offset-md-3"> <div class="qheader">');

            $mform->addElement('checkbox', $allfieldsvalue->userfields, $allfieldsvalue->userfields);
            $mform->addElement('html', "<button type='button' class='deletebtn border rounded-pill'>
                                        <a href='$CFG->wwwroot/local/directory/deletefield?id=$allfieldsvalue->id'>
                                            <i class='fa fa-trash' aria-hidden='true'>
                                            </i>
                                        </a>
                                    </button>
                                    </div></div></div>");

        }
        $this->add_action_buttons();

    }
}
