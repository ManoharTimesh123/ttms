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
 * The News Management
 *
 * @package    local_news
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class news_create_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $id = ($this->_customdata['data']) ? $this->_customdata['data']->id : null;
        $schoolid = $this->_customdata['data']->schoolid;
        $mform->addElement('header', 'News', get_string('createnews', 'local_news'), '');

        $mform->addElement('text', 'title', get_string('name', 'local_news'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required', null, 'client');

        $mform->addElement('editor', 'description', get_string('newsdescription', 'local_news'), null);
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', get_string('required'), 'required', null, 'client');

        $mform->addElement('filemanager', 'newsimage', get_string('uploadfile', 'local_news'), null,
                        array('accepted_types' => array('.png', '.jpg', '.jpeg'), 'maxbytes' => 2097152, 'maxfiles' => 1));
        $mform->addRule('newsimage', get_string('newsimage', 'local_news'), 'required');

        $mform->addElement('date_selector', 'datefrom', get_string('newsvisiblefrom', 'local_news'));
        $mform->addElement('date_selector', 'dateto', get_string('newsvisibleto', 'local_news'));

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'schoolid', $id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges', 'local_news'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['dateto'] < $data['datefrom']) {
            $errors['dateto'] = get_string('enddateerrormsg', 'local_news');
        }
        return $errors;
    }

}
