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
 * The Wall Management
 *
 * @package local_wall
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class post_create_form extends moodleform {

    public function definition() {
        global $USER;
        $mform = $this->_form;
        $id = ($this->_customdata['data']) ? $this->_customdata['data']->id : null;

        $mform->addElement('header', 'wall', get_string('createpost', 'local_wall'), '');

        $mform->addElement('editor', 'description', get_string('description', 'local_wall'));
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', get_string('required'), 'required', null, 'client');

        $mform->addElement('filemanager', 'postfile', get_string('uploadfile', 'local_wall'), null,
            array('accepted_types' => array('.png', '.jpg', '.jpeg', '.mp3', '.mp4'), 'maxbytes' => 2097152, 'maxfiles' => 1));

        $userenrolledcourses = enrol_get_all_users_courses($USER->id);
        $courses = array();
        $courses[''] = get_string('select', 'local_wall');
        foreach ($userenrolledcourses as $course) {
            $courses[$course->id] = $course->fullname;
        }
        $mform->addElement('select', 'courseid', get_string('selectcourse', 'local_wall'), $courses);
        $mform->setDefault('courseid', 0);
        $mform->addRule('courseid', get_string('required'), 'required', null, 'client');

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges', 'local_news'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
