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
 * Need Based Trainings
 *
 * @package    local_need_based_trainings
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class need_based_training_filter_form extends moodleform {
    /**
     * Form definition
     */
    public function definition () {

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $mform->addElement('html', '<div class="customized-field">');
        $mform->addElement('header', 'need_based_trainings', get_string('filter', 'local_need_based_trainings'), '');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-xl-4">');

        $options = array('multiple' => false, 'noselectionstring' => get_string('select'));
        $courses = get_training_courses();

        $mform->addElement('autocomplete', 'course', get_string('course', 'local_need_based_trainings'), $courses, $options);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $this->add_action_buttons(true, get_string('searchsubmitbutton', 'local_need_based_trainings'));
        $mform->addElement('html', '</div>');
    }
}
