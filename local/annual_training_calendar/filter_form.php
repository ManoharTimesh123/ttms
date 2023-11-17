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
 * Annual Training Calendar
 *
 * @package    local_annual_training_calendar
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class annual_training_calendar_filter_form extends moodleform {
    /**
     * Form definition
     */
    public function definition () {

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $disabled = array('disabled');
        if ($data->startdate_enabled && $data->enddate_enabled) {
            $disabled = '';
        }
        $mform->addElement('html', '<div class="customized-field">');
        $mform->addElement('header', 'annual_training_calendar', get_string('filter', 'local_annual_training_calendar'), '');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('text', 'keyword', get_string('search', 'local_annual_training_calendar'));
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-xl-4">');
        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
        $trainingdateoptions = array(
            'past' => get_string('past', 'local_annual_training_calendar'),
            'ongoing' => get_string('ongoing', 'local_annual_training_calendar'),
            'upcoming' => get_string('upcoming', 'local_annual_training_calendar')
        );
        $trainingtype = get_string('trainingtype', 'local_annual_training_calendar');
        $mform->addElement('autocomplete', 'trainingtype', $trainingtype, $trainingdateoptions, $options);

        $coursemodeoptions = array(
            'online' => get_string('online', 'local_annual_training_calendar'),
            'offline' => get_string('offline', 'local_annual_training_calendar'),
        );
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-xl-4">');
        $trainingmode = get_string('trainingmode', 'local_annual_training_calendar');
        $mform->addElement('autocomplete', 'trainingmode', $trainingmode, $coursemodeoptions, $options);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('date_selector', 'startdate', get_string('fromdate', 'local_annual_training_calendar'), '' , $disabled);
        $mform->setDefault('startdate', date('U', strtotime("-3 month")));
        $mform->addElement('advcheckbox', 'startdate_enabled', get_string('enable'), '', ['class' => 'enable-date']);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('date_selector', 'enddate', get_string('todate', 'local_annual_training_calendar'), '' , $disabled);
        $mform->addElement('advcheckbox', 'enddate_enabled', get_string('enable'), '', ['class' => 'enable-date']);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $this->add_action_buttons(true, get_string('searchsubmitbutton', 'local_annual_training_calendar'));
        $mform->addElement('html', '</div>');
    }
}
