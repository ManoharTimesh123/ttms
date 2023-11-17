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
 * Reports
 *
 * @package    local_reports
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class report_activity_filter_form extends moodleform {
    /**
     * Form definition
     */
    public function definition () {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('html', '<div class="customized-field">');

        $mform->addElement('html', '<div class="row">');

        $options = array('multiple' => false, 'noselectionstring' => get_string('select'));

        $courses = $DB->get_records_sql('SELECT id, fullname FROM {course} WHERE visible=1');
        $coursedata = array();
        $coursedata[] = '';
        foreach ($courses as $course) {
            $coursedata[$course->id] = $course->fullname;
        }
        $mform->addElement('html', '<div class="col-xl-8">');
        $mform->addElement('autocomplete', 'course', get_string('course', 'local_reports'), $coursedata, $options);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $this->add_action_buttons(false, get_string('viewreport', 'local_reports'));
        $mform->addElement('html', '</div>');
    }
}

class report_feedback_filter_form extends moodleform {
    /**
     * Form definition
     */
    public function definition () {
        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $mform->addElement('html', '<div class="row">');

        $options = [
            'multiple' => false,
            'noselectionstring' => get_string('select'),
            'class' => $data->activityname . 'report'
        ];

        $courses = $DB->get_records_sql('SELECT id, fullname FROM {course} WHERE visible=1');
        $coursedata = array();
        $coursedata[] = '';
        foreach ($courses as $course) {
            $coursedata[$course->id] = $course->fullname;
        }
        $mform->addElement('html', '<div class="col-xl-6">');
        $mform->addElement('autocomplete', 'course', get_string('choosecourse', 'local_reports'), $coursedata, $options);
        $mform->addElement('html', '</div>');

        $courseactivity = array();
        $courseactivity[] = '--select--';
        foreach ($data->activitylist as $key => $value) {
            $courseactivity[$key] = $value;
        }

        $mform->addElement('html', '<div class="col-xl-6">');
        $mform->addElement('select', $data->activityname, 'Select ' . $data->activityname . ' ', $courseactivity);
        $mform->addElement('html', '</div>');

        $this->add_action_buttons(false, get_string('viewreport', 'local_reports'));
        $mform->addElement('html', '</div>');
    }
}
