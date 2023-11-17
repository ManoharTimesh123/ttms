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
 * Contains class plagiarism_similarity_setup_form
 *
 * @package   local personal_training_calendar
 * @copyright 2017 Dan Marsden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/editlib.php');
/**
 * Class lagiarism_similarity_setup_form
 *
 * @package   plagiarism_similarity
 * @copyright 2017 Dan Marsden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_personal_training_calendar_filter_form extends moodleform {
    /**
     * Form definition
     */
    public function definition () {
        $mform =& $this->_form;

        $systemcontext = \context_system::instance();

        $data = $this->_customdata['data'];
        $disabled = array('disabled');
        if ($data->startdate_enabled && $data->enddate_enabled) {
            $disabled = '';
        }

        $mform->addElement('html', '<div class="customized-field">');
        $mform->addElement('header', 'personal_training_calendar', get_string('filter', 'local_personal_training_calendar'), '');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('text', 'freetextsearch', get_string('search', 'local_personal_training_calendar'));
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->setType('freetextsearch', PARAM_RAW);
        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
        $trainingdateoptions = array(
            'past' => get_string('past', 'local_personal_training_calendar'),
            'ongoing' => get_string('ongoing', 'local_personal_training_calendar'),
            'upcoming' => get_string('upcoming', 'local_personal_training_calendar')
        );
        $trainingdate = get_string('trainingdate', 'local_personal_training_calendar');
        $mform->addElement('autocomplete', 'trainingdate', $trainingdate, $trainingdateoptions, $options);
        $coursemodeoptions = array(
            'online' => get_string('online', 'local_personal_training_calendar'),
            'offline' => get_string('offline', 'local_personal_training_calendar'),
        );
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('autocomplete', 'coursemode', get_string('coursemode', 'local_personal_training_calendar'), $coursemodeoptions, $options);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('date_selector', 'startdate', get_string('from'), '' , $disabled);
        $mform->setDefault('startdate', date('U', strtotime("-3 month")));
        $mform->addElement('advcheckbox', 'startdate_enabled', get_string('enable'), '', ['class' => 'enable-date']);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-xl-4">');
        $mform->addElement('date_selector', 'enddate', get_string('to'), '' , $disabled);
        $mform->addElement('advcheckbox', 'enddate_enabled', get_string('enable'), '', ['class' => 'enable-date']);
        $mform->addElement('html', '</div>');

        if (has_capability('local/local/personal_training_calendar:viewall', $systemcontext)){
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

        $this->add_action_buttons(true);
        $mform->addElement('html', '</div>');
    }
}
