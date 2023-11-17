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
 * Custom Notifications
 *
 * @package    local_customnotifications
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2023 TTMS Limited
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class notify_template_form extends moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $options = $this->_customdata['options'];

        $mform->addElement('header', 'general', get_string('template', 'local_customnotifications'));

        $mform->addElement('text', 'workflow', get_string('workflowname', 'local_customnotifications'), array('size' => '80'));
        $mform->setType('workflow', PARAM_RAW);
        $mform->addRule('workflow', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('templatename', 'local_customnotifications'), array('size' => '80'));
        $mform->setType('name', PARAM_RAW);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'code', get_string('templatecode', 'local_customnotifications'), array('size' => '80'));
        $mform->setType('code', PARAM_RAW);
        $mform->addRule('code', get_string('required'), 'required', null, 'client');

        $roles = $DB->get_records_sql("SELECT id, name FROM {role}
                                        WHERE shortname IN ('student', 'no', 'cordinator', 'facilitator','adtraining')");
        $rolearray = array();
        $rolearray[0] = 'Select';
        $rolearray[-1] = 'LMS Admin';

        foreach ($roles as $role) {
            $rolearray[$role->id] = $role->name;
        }

        $toselect = $mform->addElement('select', 'touser', get_string('touser', 'local_customnotifications'), $rolearray);
        $toselect->setMultiple(true);

        $ccselect = $mform->addElement('select', 'ccusers', get_string('ccusers', 'local_customnotifications'), $rolearray);
        $ccselect->setMultiple(true);

        $content = '<div class="form-group row fitem">
        <div class="col-md-3"></div>
        <div class="col-md-9 form-inline" style="color:#223087;">
        <i><b>Please use the tags as shown below in the Subject and Message content fields to get the details from the system.</b>
        </i>
        <ul>
        <li>{coursename} - course full name, {courseshortname} - course short name, {courseidnumber} - course id number</li>
        <li>{startdate} - course start date, {enddate} - course end date, {starttime} - course start time,
        {endtime} - course end time</li>
        <li>{coursetype} - Course type of a particular course</li>
        <li>{touser} - Name of mail receivers, {ccusers} - Name of users marked in CC</li>
        <li>{coursetype} - Course type, {modality} - Modality of the course</li>
        <li>{schoolname} - School/venue name, {hos} - Head of school</li>
        <li>{siteurl} - Portal URL, {userrole} - Role of the user</li>
        </ul>
        </div>
        </div>';

        $mform->addElement('html', $content);

        $mform->addElement('text', 'subject', get_string('subject', 'local_customnotifications'), array('size' => '80'));
        $mform->setType('subject', PARAM_RAW);
        $mform->addRule('subject', get_string('required'), 'required', null, 'client');

        $mform->addElement('editor', 'messagecontent', get_string('messagecontent', 'local_customnotifications'), $options);

        $mform->addElement('editor', 'plaintext', get_string("plaintext", "local_customnotifications"), $options);

        $mform->addElement('textarea', 'smstext', get_string("smstext", "local_customnotifications"), 'wrap="virtual"
                            rows="10" cols="93"');

        $mform->addElement('text', 'smstemplateid', get_string('smstemplateid', 'local_customnotifications'),
                            array('size' => '30'));
        $mform->setType('smstemplateid', PARAM_INT);
        $mform->addHelpButton('smstemplateid', 'smstemplateid', 'local_customnotifications');

        // Hidden optional params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_modality'));

    }

    public function validation($data, $files) {
        global $DB;

        $newdata = data_submitted();
        $errors = parent::validation($data, $files);
        // If any validation required can be added.

        if ($data['id'] == 0) {
            if ($DB->record_exists('local_notification_templates', array('code' => $data['code']))) {
                $errors['code'] = 'Template record already exists';
            }
        } else {
            $oldtemplate = $DB->get_record('local_notification_templates', array('id' => $data['id']));
            if ($oldtemplate->id != $data['id'] ) {
                $errors['code'] = 'Template record already exists';
            }
        }

        return $errors;
    }

}
