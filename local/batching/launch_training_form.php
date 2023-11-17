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
 * @package    local_batching
 * @author     Tarun Upadhyay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/user_management/locallib.php');

class batching_launch_training_form extends moodleform {

    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $mform->addElement('html', '<div class="proposal-page pt-1">');

        $batching = get_batchings($id)[$id];

        if ($batching->status == 'launched') {
            $trainingurl = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $batching->course));
            $mform->addElement('html', '<div class="p-5" style="text-align: center">');
            $mform->addElement('html', '<h5 class="font-weight-bold pt-2 alert-success">' . get_string('trainingsuccessfullylaunched', 'local_batching') . '</h5>');
            $mform->addElement('html', '<a href="' . $trainingurl . '" class="btn btn-primary d-inline-block font-weight-bold pt-2">' . get_string('viewtraining', 'local_batching') . '</a>');
            $mform->addElement('html', '</div>');
        } else {
            $trainingdays = get_training_days_by_batching($id);

            $launchtrainingurl = new moodle_url($CFG->wwwroot.'/local/batching/launch_training.php', array('id' => $id, 'launch' => 1));

            $mform->addElement('html', '<h5 class="font-weight-bold pt-2">' . get_string('courseselection', 'local_batching') . '</h5>');
            if ($trainingdays) {

                $noofdays = $trainingdays->value;

                for ($i = 1; $i <= $noofdays; $i++) {
                    $mform->addElement('html', '<div class="light-bg p-3"><strong>' . get_string('trainingday', 'local_batching') . $i .'</strong>');
                    $mform->addElement('html', '<ul class="pl-3">');
                    $mform->addElement('html', '<li>' . get_string('morningattendance', 'local_batching') . '</li>');
                    $mform->addElement('html', '<li>' . get_string('pretest', 'local_batching') . '</li>');
                    $mform->addElement('html', '<li>' . get_string('studymaterial', 'local_batching') . '</li>');
                    $mform->addElement('html', '<li>' . get_string('eveningattendance', 'local_batching') . '</li>');

                    if ($i == $noofdays) {
                        $mform->addElement('html', '<li>' . get_string('posttest', 'local_batching') . '</li>');
                        $mform->addElement('html', '<li>' . get_string('feedbackforms', 'local_batching') . '</li>');
                        $mform->addElement('html', '<li>' . get_string('certificate', 'local_batching') . '</li>');
                    }

                    $mform->addElement('html', '</ul></div>');
                }
            }
            // Hidden optional params.
            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);
            $mform->addElement('html', '<div class="row">');

            $mform->addElement('html', '<div class="col-6 text-center">');
            $mform->addElement('html', '<a href="' . $launchtrainingurl . '" class="btn btn-primary d-inline-block font-weight-bold pt-2">' . get_string('launchtraining', 'local_batching') . '</a>');
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '</div>');
        }

        $mform->addElement('html', '</div>');
    }
}
