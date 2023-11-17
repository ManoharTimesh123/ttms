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
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/local/user_management/locallib.php');
require_once($CFG->dirroot . '/mod/certificate/locallib.php');

class batching_create_training_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $course = $this->_customdata['course'];

        $mform->addElement('text', 'fullname', get_string('trainingname', 'local_batching'), 'maxlength="120" size="25" pattern="[A-Za-z0-9 ]+" ');
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('fullname', 'fullname', 'local_batching');

        $mform->addElement('text', 'shortname', get_string('trainingshortname', 'local_batching'), 'maxlength="120" size="25" pattern="[A-Za-z0-9 ]+" ');
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('shortname', 'shortname', 'local_batching');

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

       $mform->addElement('filemanager', 'trainingimage', get_string('courseimage', 'local_batching', ['wwwroot' => $CFG->wwwroot]), null, array('accepted_types' => array('.png', '.jpg', '.jpeg', '.gif')), array('maxfiles' => 1));
        $mform->addRule('trainingimage', null, 'required');
        $mform->addHelpButton('trainingimage', 'trainingimage', 'local_batching');

        // Hidden optional params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $modality = optional_param('modality', 0, PARAM_TEXT);
        $mform->addElement('hidden', 'modality', $modality);
        $mform->setType('modality', PARAM_TEXT);

        $nodalofficersformatteddata = array();
        $nodalofficers = get_users_with_role('nodalofficer', []);
        foreach ($nodalofficers as $key => $nodalofficer) {
            $nodalofficersformatteddata[$key] = $nodalofficer->firstname.' '.$nodalofficer->lastname;
        }
        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
        $select = $mform->addElement('autocomplete', 'nodalofficers', get_string('nodalofficer', 'local_batching'), $nodalofficersformatteddata, $options);
        $mform->addRule('nodalofficers', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('nodalofficers', 'nodalofficers', 'local_batching');
        $select->setSelected(explode($data['nodalofficers']));

        $dietheadsformatteddata = array();
        $dietheads = get_diet_head();
        foreach ($dietheads as $key => $diethead) {
            $dietheadsformatteddata[$key] = $diethead->name;
        }
        $select = $mform->addElement('autocomplete', 'dietheads', get_string('diethead', 'local_batching'), $dietheadsformatteddata, $options);
        $select->setSelected(explode($data['dietheads']));
        $mform->addHelpButton('dietheads', 'dietheads', 'local_batching');

        // Certificate template selection.
        $mform->addElement('select', 'certificatetemplate', get_string('certificatetemplate', 'local_batching'), certificate_get_images('borders'), array('onchange' => 'javascript:display_certificate(this.value);'));
        $mform->addRule('certificatetemplate', get_string('required'), 'required', null, 'client');
        $mform->setDefault('certificatetemplate', '0');

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_batching'));
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate shortname.
        if ($course = $DB->get_record('course', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $course->id != $data['id']) {
                $errors['shortname'] = get_string('shortnametaken', 'local_batching', $course->fullname);
            }
        }

        $currenttimestamp = time();
        if (customdateformat('DATE_WITHOUT_TIME', $data['startdate']) < customdateformat('DATE_WITHOUT_TIME', $currenttimestamp)) {
            $errors['startdate'] = get_string('trainingstartdatevalidationerror', 'local_batching');
        }

        if ($data['enddate'] < $data['startdate']) {
            $errors['enddate'] = get_string('trainingenddatevalidationerror', 'local_batching');
        }
        return $errors;
    }

}
