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

class local_user_profile_update extends moodleform {

    public function definition () {

        global $DB, $USER;
        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $sql = "SELECT id, name FROM {local_subjects} ";
        $subjectslist = $DB->get_records_sql_menu($sql);

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-sm-12 col-lg-8 offset-lg-2">');
        $mform->addElement('html', '<div class="bg-white inner_page px-5 py-4 mb-4 rounded-lg shadow-lg">');
        $mform->addElement('html', '<div class="border-bottom h4 mb-4 pb-3 pt-3">' . get_string('profiledetailserrormsg', 'local_profile_management') . '</div>');

        $options = array('multiple' => true, 'noselectionstring' => 'Select Subjects');
        $mform->addElement('autocomplete', 'subject', get_string('subjects', 'local_profile_management'), $subjectslist, $options);
        $mform->addRule('subject', get_string('required'), 'required', null, 'client');

        $sql = "SELECT id, name FROM {local_school_positions} ";

        $positionslist = $DB->get_records_sql_menu($sql);
        $options = array('multiple' => true, 'noselectionstring' => 'Select Positions');
        $mform->addElement('autocomplete', 'position', get_string('position', 'local_profile_management'), $positionslist, $options);
        $this->add_action_buttons(false, get_string('update', 'local_profile_management'));
        $mform->addRule('position', get_string('required'), 'required', null, 'client');

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

        $errors = parent::validation($usernew, $files);

        $usernew = (object)$usernew;

        return $errors;
    }
}
