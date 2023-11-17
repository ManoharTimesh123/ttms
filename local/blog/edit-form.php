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
 * The blog Management
 *
 * @package    local_blog
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class blog_create_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;
        $id = ($this->_customdata['data']) ? $this->_customdata['data']->id : null;

        $mform->addElement('header', 'Blog', get_string('createpost', 'local_blog'), '');

        $mform->addElement('text', 'title', get_string('name', 'local_blog'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required', null, 'client');

        $mform->addElement('editor', 'description', get_string('blogdescription', 'local_blog'), null);
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $blogimage = get_string('blogimage', 'local_blog', ['wwwroot' => $CFG->wwwroot]);
        $mform->addElement('filemanager', 'attachments', $blogimage, null,
            array('accepted_types' => array('.png', '.jpg', '.jpeg'), 'maxbytes' => 2097152, 'maxfiles' => 1));
        $mform->addRule('attachments', null, 'required');

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges', 'local_blog'));

    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
