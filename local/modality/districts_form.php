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

class districts_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $customdata = ($this->_customdata['data']) ? $this->_customdata['data'] : null;

        $mform->addElement('text', 'name', get_string('districtname', 'local_modality'), 'maxlength="120" size="25"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'code', get_string('districtcode', 'local_modality'), 'maxlength="120" size="25"');
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', get_string('required'), 'required', null, 'client');
        
        $departments = array();
        $depts = $DB->get_records_menu('local_departments');
        foreach ($depts as $key => $dept) {
            $departments[$key] = $dept;
        }
        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
        $mform->addElement('autocomplete', 'departments', get_string('departments', 'local_modality'), $departments, $options);
        if ($customdata) {
            $selecteddepartments = explode(',', $customdata->departments);
            $mform->getElement('departments')->setSelected($selecteddepartments);
        }
        $editoroptions = array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'accepted_types' => '*');
        $mform->addElement('editor', 'description', get_string('coursedescription', 'local_modality'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
        $summaryfields = 'description';
        $mform->addElement('hidden', 'descriptionformat', $id);
        $mform->setType('descriptionformat', PARAM_RAW);
        $mform->addElement('hidden', 'state_id', 1);
        $mform->setType('state_id', PARAM_RAW);

        // Hidden optional params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_modality'));

    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        $district = $DB->get_record('local_districts', array('code' => $data->code));
        if ($data->id == 0) {
            if ($DB->record_exists('local_districts', array('code' => $data->code))) {
                $errors['code'] = get_string('alreadyexists', 'local_modality');
            }
        } else {
            if ($olddistrict = $DB->get_record('local_districts', array('id' => $data->id))) {
                if ($district->id != $olddistrict->id ) {
                    $errors['code'] = get_string('alreadyexists', 'local_modality');
                }
            }
        }
        return $errors;
    }
    
    public function set_data($data) {
        global $DB;

        if ($data->id > 0) {
            $district = $DB->get_record('local_districts', array('code' => $data->id));
            if (!empty($data->departments)) {
                $data->departments = explode($data->departments, ',');
            }
        }
        parent::set_data($data);
    }
}
