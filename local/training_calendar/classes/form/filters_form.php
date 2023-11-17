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
 * MOODLE VERSION INFORMATION
 *
 * This file defines the current version of the core Moodle code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_training_calendar\form;
use core;
use moodleform;
use context_system;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
class filters_form extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $systemcontext = context_system::instance();
        $mform = $this->_form;
        $filterlist = $this->_customdata['filterlist']; // This contains the data of this form.

        $selectviewoptions = [
            'multiple' => false,
            'id' => 'id_selectview',
            'placeholder' => get_string('defaultview', 'local_training_calendar')
        ];

        $selectviewlist = array('defaultview' => get_string('defaultview', 'local_training_calendar'));
        $selectview = get_string('selectview', 'local_training_calendar');
        $mform->addElement('select', 'selectview', $selectview, $selectviewlist, $selectviewoptions);
        $mform->setType('selectview', PARAM_INT);

        $coursetypeoptions = array(
            'id' => 'id_coursetype',
            'multiple' => false,
            'placeholder' => get_string('all')
        );

        $coursetype = array('training' => 'Training', 'mock' => 'Mock');
        $mform->addElement('select', 'coursetype', get_string('coursetype', 'local_training_calendar'), $coursetype, $coursetypeoptions);

        $orgoptions = [
            'multiple' => false,
            'id' => 'id_organiser',
            'placeholder' => get_string('all')
        ];
        $orgtypes = array();
        $mform->addElement('select', 'organiser', get_string('organiser', 'local_training_calendar'), $orgtypes, $orgoptions);
        $mform->setType('organiser', PARAM_RAW);

        $trainingtimelineoptions = [
            'multiple' => false,
            'id' => 'id_trainingtimeline',
            'placeholder' => get_string('ongoing_upcoming', 'local_training_calendar'),
        ];
        $trainingtimelinelist = array('ongoing' => 'Ongoing', 'upcoming' => 'Upcoming');
        $mform->addElement('select', 'trainingtimeline', get_string('trainingtimeline', 'local_training_calendar'), $trainingtimelinelist, $trainingtimelineoptions);
        $mform->setType('trainingtimeline', PARAM_RAW);

        $yearsrange = range(2010, 2030); // Create an array of years from 2010 to 2030.
        $years = array();
        foreach ($yearsrange as $year) {
            $years[$year] = $year;
        }
        $mform->addElement('select', 'trainingyear', get_string('trainingyear', 'local_training_calendar'), $years);
        $mform->setType('trainingyear', PARAM_INT);

        $buttonarray = array();
        $classarray = array('class' => 'form-submit');
        $buttonarray[] = &$mform->createElement('submit', 'saveanddisplay', get_string('apply', 'local_training_calendar'), $classarray);
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('reset', 'local_training_calendar'), $classarray);
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
     /**
      * Validation.
      *
      * @param array $data
      * @param array $files
      * @return array the errors that were found
      */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
