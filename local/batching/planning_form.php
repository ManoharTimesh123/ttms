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
 * The batching Management
 *
 * @package local_batching
 * @author  Nadia Farheen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class batching_planning_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];

        $mform->addElement('text', 'fullname', get_string('trainingname', 'local_batching'), 'maxlength="120" size="25" pattern="[A-Za-z0-9 ]+" ');
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('fullname', 'fullname', 'local_batching');

        $mform->addElement('date_time_selector', 'startdate', get_string('trainingfrom', 'local_batching'));
        $mform->setType('startdate', PARAM_TEXT);
        $mform->addRule('startdate', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('startdate', 'startdate', 'local_batching');

        $mform->addElement('date_time_selector', 'enddate', get_string('trainingto', 'local_batching'));
        $mform->setType('enddate', PARAM_TEXT);
        $mform->addRule('enddate', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('enddate', 'enddate', 'local_batching');

        $editoroptions = array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'accepted_types' => '*');
        $mform->addElement('editor', 'summary', get_string('description'), null, $editoroptions);
        $mform->setType('summary', PARAM_RAW);
        $summaryfields = 'summary';
        $mform->addElement('hidden', 'summaryformat', 1);
        $mform->addHelpButton('summary', 'summary', 'local_batching');

        // Hidden optional params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_batching'));
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        $currenttimestamp = time();
        if (customdateformat('DATE_WITHOUT_TIME', $data['startdate']) < customdateformat('DATE_WITHOUT_TIME', $currenttimestamp)) {
            $errors['startdate'] = get_string('trainingstartdatevalidationerror', 'local_batching');;
        }

        if ($data['enddate'] < $data['startdate']) {
            $errors['enddate'] = get_string('trainingenddatevalidationerror', 'local_batching');
        }
        return $errors;
    }

}
