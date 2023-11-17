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

class topic_create_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $id = $this->_customdata['id'];

        $data = $this->_customdata['data'];
        $disabledstartdate = array('disabled');
        $disabledenddate = array('disabled');
        if ($data->startdate) {
            $disabledstartdate = '';
            $data->startdate_enabled = 1;
        }

        if ($data->enddate) {
            $disabledenddate = '';
            $data->enddate_enabled = 1;
        }

        $mform->addElement('header', 'topics', get_string('topic', 'local_need_based_trainings'), '');

        $mform->addElement('text', 'name', get_string('name', 'local_need_based_trainings'), 'maxlength="120" size="25" pattern="[A-Za-z0-9 ]+" ');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('shortname', 'local_need_based_trainings'), 'maxlength="120" size="25" pattern="[A-Za-z0-9 ]+" ');
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');

        $mform->addElement('editor', 'description', get_string('description', 'local_need_based_trainings'), null);
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', get_string('required'), 'required', null, 'client');

        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_need_based_trainings'), '', $disabledstartdate);
        $mform->addElement('advcheckbox', 'startdate_enabled', get_string('enable'), '', ['class' => 'enable-date']);

        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_need_based_trainings'), '', $disabledenddate);
        $mform->addElement('advcheckbox', 'enddate_enabled', get_string('enable'), '', ['class' => 'enable-date']);

        $statusoptions = array(
            'Active' => get_string('active', 'local_need_based_trainings'),
            'Inactive' => get_string('inactive', 'local_need_based_trainings'),
        );
        $status = get_string('status', 'local_need_based_trainings');
        $mform->addElement('select', 'status', $status, $statusoptions);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges', 'local_need_based_trainings'));

    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        $topic = $DB->get_record('local_nbt_topics', array('shortname' => $data->shortname));
        if ($data->id == 0) {
            if ($topic) {
                $errors['shortname'] = get_string('alreadyexists', 'local_need_based_trainings');
            }
        } else {
            if ($oldtopic = $DB->get_record('local_nbt_topics', array('id' => $data->id))) {
                if ($topic->id != $oldtopic->id ) {
                    $errors['shortname'] = get_string('alreadyexists', 'local_need_based_trainings');
                }
            }
        }

        return $errors;
    }
}
