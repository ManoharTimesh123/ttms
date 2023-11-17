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
 * mod_certificate
 *
 * @package    mod_certificate
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2023 Moodle Limited
 */

 /* INTG Customization Start : Filters form to select course and group data to list the participants for certificate approval. */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class certificate_filter_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;

        $options = array('multiple' => false, 'noselectionstring' => get_string('select'), 'class' => 'certificateapproval_course');

        $courses = certificate_get_courses_for_approval();
        $trainings = array(null => 'Select Training');
        foreach ($courses as $course) {
            $trainings[$course->id] = $course->fullname;
        }
        $mform->addElement('autocomplete', 'course', get_string('training', 'mod_certificate'), $trainings, $options);
        $mform->settype('course', PARAM_INT);

        $groups = array('' => 'Select Batch');
        $mform->addElement('select', 'group', get_string('batch', 'mod_certificate'), $groups);
        $mform->settype('group', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(false, get_string('show'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

    public function set_data($data) {
        // Set up the description type.
        if (!empty($data['course'])) {
            $data['course'] = $data['course'];
        }
        // Call parent.
        parent::set_data($data);
    }

}
/* INTG Customization End. */

