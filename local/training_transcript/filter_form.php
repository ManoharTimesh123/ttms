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
 * Training Transcript
 *
 * @package    local_training_transcript
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class training_transcript_filter_form extends moodleform {
    /**
     * Form definition
     */
    public function definition () {
        $mform = $this->_form;

        $systemcontext = \context_system::instance();

        $data = $this->_customdata['data'];

        $disabled = array('disabled');
        if ($data->startdate_enabled && $data->enddate_enabled) {
            $disabled = '';
        }

        $mform->addElement('html', '<div class="customized-field">');
        $mform->addElement('header', 'training_transcript', get_string('filter', 'local_training_transcript'), '');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('text', 'keyword', get_string('search', 'local_training_transcript'));
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-xl-4">');
        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
        $coursemodeoptions = array(
            'online' => get_string('online', 'local_training_transcript'),
            'offline' => get_string('offline', 'local_training_transcript'),
        );
        $trainingmode = get_string('trainingmode', 'local_training_transcript');
        $mform->addElement('autocomplete', 'trainingmode', $trainingmode, $coursemodeoptions, $options);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('date_selector', 'startdate', get_string('fromdate', 'local_training_transcript'), '' , $disabled);
        $mform->setDefault('startdate', date('U', strtotime("-3 month")));
        $mform->addElement('advcheckbox', 'startdate_enabled', get_string('enable'), '', ['class' => 'enable-date']);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('date_selector', 'enddate', get_string('todate', 'local_training_transcript'), '' , $disabled);
        $mform->addElement('advcheckbox', 'enddate_enabled', get_string('enable'), '', ['class' => 'enable-date']);
        $mform->addElement('html', '</div>');
        if (has_capability('local/training_transcript:viewall', $systemcontext)){
            $options = array('multiple' => false, 'noselectionstring' => get_string('select'));

            $userdata = get_all_users();
            $users = array();
            $users[] = '';
            foreach ($userdata as $user) {
                $users[$user->id] = $user->firstname . ' ' . $user->lastname;
            }

            $mform->addElement('html', '<div class="col-xl-4">');
            $mform->addElement('autocomplete', 'user', get_string('user', 'local_training_transcript'), $users, $options);
            $mform->addElement('html', '</div>');
        }
        $mform->addElement('html', '</div>');

        $this->add_action_buttons(true, get_string('searchsubmitbutton', 'local_training_transcript'));

        $mform->addElement('html', '</div>');
    }
}
