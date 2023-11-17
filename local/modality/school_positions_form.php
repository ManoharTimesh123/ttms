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
 * The modality Management
 *
 * @package local_modality
 * @author  Nadia Farheen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class school_positions_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $options = $this->_customdata['options'];

        $mform->addElement('header', 'Course type', get_string('createschoolposition', 'local_modality'), '');

        $mform->addElement('text', 'name', get_string('schoolpositionname', 'local_modality'), 'maxlength="120" size="25"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('schoolpositioncode', 'local_modality'), 'maxlength="120" size="25"');
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');
		
        $mform->addElement('editor', 'description', get_string('coursedescription', 'local_modality'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
        $summaryfields = 'description';

        // Hidden optional params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_modality'));

    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
		
        $position = $DB->get_record('local_school_positions', array('shortname' => $data->shortname));
        if ($data->id == 0) {
            if ($position) {
                $errors['shortname'] = get_string('alreadyexists', 'local_modality');
            }
        } else {
            if ($oldposition = $DB->get_record('local_school_positions', array('id' => $data->id))) {
                if ($position->id != $oldposition->id ) {
                    $errors['shortname'] = get_string('alreadyexists', 'local_modality');
                }
            }
        }
        return $errors;

    }

}

