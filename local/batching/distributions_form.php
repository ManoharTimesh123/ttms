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

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/local/user_management/locallib.php');

class batching_distribution_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];

        $approvedvenues = get_approved_venues($id);
        $facilitators = get_users_with_role('facilitator', []);
        $facilitatorsarray = array();
        foreach ($facilitators as $facilitator) {
            $facilitatorsarray[$facilitator->id] = $facilitator->firstname . ' ' . $facilitator->lastname;
        }

        $coordinators = get_users_with_role('coordinator', []);
        $coordinatorsarray = array();
        foreach ($coordinators as $coordinator) {
            $coordinatorsarray[$coordinator->id] = $coordinator->username;
        }

        $observers = get_users_with_role('observer', []);
        $observersarray = array();
        foreach ($observers as $observer) {
            $observersarray[$observer->id] = $observer->username;
        }
        $batching = get_batchings($id)[$id];

        $courseid = $batching->course;

        $cycles = get_cycle_by_batching($id);

        $trainingnoofdays = 0;
        $filters = get_filters($id);
        if (!empty($filters)) {
            foreach ($filters as $index => $filter) {
                if ($index == 'trainingnoofdays' && $filter != null) {
                    $trainingnoofdays = $filter;
                }
            }
        }

        if ($cycles) {

            $mform->addElement('html', '<div class="batching-distributions accordion" id="accordionCycle">');

            foreach ($cycles as $cycle) {

                $cycleid = 'INSET00' . $courseid . '/' . $cycle->code;

                $mform->addElement('html', '<div class="cycle mb-3 card">');
                $mform->addElement('html', '<div class="cycle-title px-3 py-3 text-white font-weight-bold h4 mb-0" id="heading'.$cycle->id.'" data-toggle="collapse" data-target="#collapse'.$cycle->id.'" aria-expanded="true" aria-controls="collapse'.$cycle->id.'"><span class="" type="button">' . get_string('cycleid', 'local_batching') . ' : ' . $cycleid . '</span></div>');

                $batches = get_batch_by_batching_and_cycle($id, $cycle->id);
                $cycletimes = get_cycle_times($cycle->id);

                $mform->addElement('html', '<div class="collapse" id="collapse'.$cycle->id.'" aria-labelledby="heading'.$cycle->id.'" data-parent="#accordionCycle">');

                $timecount = 0;
                foreach ($cycletimes as $index => $cycletime) {
                    $timecount++;
                    $mform->addElement('html', '<div class="px-4 pt-3"><div class="row">');
                    // Cycle Start.
                    $mform->addElement('html', '<div class="col-md-6">');
                   $mform->addElement('date_time_selector', 'starttime['.$cycle->id.']['.$cycletime->id.']', 'Day ' . $timecount . ' start',
                        $cycletime->starttime);
                    $mform->setDefault('starttime['.$cycle->id.']['.$cycletime->id.']', $cycletime->starttime);
                    $mform->addHelpButton('starttime['.$cycle->id.']['.$cycletime->id.']', 'trainingstartdate', 'local_batching');
                    $mform->addElement('html', '</div>');
                    // Cycle Start.

                    // Cycle End Time.
                    $mform->addElement('html', '<div class="col-md-6">');
                    $mform->addElement('date_time_selector', 'endtime['.$cycle->id.']['.$cycletime->id.']', 'Day ' . $timecount . ' end', $cycletime->endtime);
                    $mform->setDefault('endtime['.$cycle->id.']['.$cycletime->id.']', $cycletime->endtime);
                    $mform->addHelpButton('endtime['.$cycle->id.']['.$cycletime->id.']', 'trainingenddate', 'local_batching');
                    $mform->addElement('html', '</div>');
                    // Cycle End Time.
                    $mform->addElement('html', '</div></div>');

                }

                if ($batches) {
                    foreach ($batches as $batch) {
                        $batchid = $cycleid . '/' . $batch->code;
                        $totalparticipabts = get_participants_by_batch($batch->id);

                        $mform->addElement('html', '<div class="batch m-4 box-shadow bg-white rounded-lg">');
                        $mform->addElement('html', '<h5 class="font-weight-bold border-bottom p-3"> ' . get_string('batchid', 'local_batching') . ': <span>' . $batchid . '</span> <spn class="">(' . get_string('participants', 'local_batching') . ' : <a style="cursor:pointer;" class="show-participants" id="' . $batch->id . '">' . count
                            ($totalparticipabts) .
                            ')</a></span></h5>');

                        $mform->addElement('html', '<div class="row px-3">');

                        // Venue Start.
                        $mform->addElement('html', '<div class="col-md-3">');
                        $venues = $approvedvenues;
                        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
                       $mform->addElement('autocomplete', 'venue['.$id.']['.$cycle->id.']['.$batch->id.']', get_string('venues', 'local_batching'), $venues, $options);
                        $mform->addRule('venue['.$id.']['.$cycle->id.']['.$batch->id.']', get_string('required'), 'required', null, 'client');
                        $mform->addHelpButton('venue['.$id.']['.$cycle->id.']['.$batch->id.']', 'venues', 'local_batching');
                        $mform->addElement('html', '</div>');
                        $selectedvenues = get_venue_in_batching($batch->id);
                        if (!empty($selectedvenues)) {
                            $mform->getElement('venue['.$id.']['.$cycle->id.']['.$batch->id.']')->setSelected($selectedvenues);
                        }
                        // Venue End.

                        // Facilitator Start.
                        $mform->addElement('html', '<div class="col-md-3">');
                        $facilitators = $facilitatorsarray;
                        $options = array('multiple' => true, 'noselectionstring' => get_string('select'), 'class' => 'facilitators');
                        $mform->addElement('autocomplete', 'facilitator['.$id.']['.$cycle->id.']['.$batch->id.']', get_string('facilitator', 'local_batching'), $facilitators, $options);
                        $mform->addRule('facilitator['.$id.']['.$cycle->id.']['.$batch->id.']', get_string('required'), 'required', null, 'client');
                        $mform->addHelpButton('facilitator['.$id.']['.$cycle->id.']['.$batch->id.']', 'facilitators', 'local_batching');
                        $mform->addElement('html', '</div>');
                        $selectedfacilitators = get_users_in_batching('facilitators', $id, $cycle->id, $batch->id);
                        if (!empty($selectedfacilitators)) {
                             $mform->getElement('facilitator['.$id.']['.$cycle->id.']['.$batch->id.']')->setSelected($selectedfacilitators);
                        }
                        // Facilitator End.

                        // Coordinator Start.
                        $mform->addElement('html', '<div class="col-md-3">');
                        $coordinators = $coordinatorsarray;
                        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
                        $mform->addElement('autocomplete', 'coordinator['.$id.']['.$cycle->id.']['.$batch->id.']', get_string('coordinator', 'local_batching'), $coordinators, $options);
                        $mform->addHelpButton('coordinator['.$id.']['.$cycle->id.']['.$batch->id.']', 'coordinators', 'local_batching');
                        $mform->addElement('html', '</div>');
                        $selectedcoordinators = get_users_in_batching('coordinators', $id, $cycle->id,
                        $batch->id);
                        if (!empty($selectedcoordinators)) {
                            $mform->getElement('coordinator['.$id.']['.$cycle->id.']['.$batch->id.']')->setSelected($selectedcoordinators);
                        }

                        // Venue End.

                        // Observer Start.
                        $mform->addElement('html', '<div class="col-md-3">');
                        $observer = $observersarray;
                        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
                        $mform->addElement('autocomplete', 'observer['.$id.']['.$cycle->id.']['.$batch->id.']', get_string('observer', 'local_batching'), $observer, $options);
                        $mform->addHelpButton('observer['.$id.']['.$cycle->id.']['.$batch->id.']', 'observers', 'local_batching');
                        $mform->addElement('html', '</div>');
                        $selectedobservers = get_users_in_batching('observers', $id, $cycle->id, $batch->id);
                        if (!empty($selectedobservers)) {
                            $mform->getElement('observer['.$id.']['.$cycle->id.']['.$batch->id.']')->setSelected($selectedobservers);
                        }
                        // Venue End.

                        $mform->addElement('html', '</div>');

                        // Batching div end.
                        $mform->addElement('html', '</div>');
                    }
                }
                $mform->addElement('html', '</div>');

                // Cycle div end.
                $mform->addElement('html', '</div>');
            }
            $mform->addElement('html', '</div>');
        }

        $mform->addElement('html', '<div class="modal fade" id="participantsmodelpopup" tabindex="-1" role="dialog" aria-labelledby="participantsmodelpopupLabel" aria-hidden="true">
                                              <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                  <div class="modal-header">
                                                    <h5 class="modal-title" id="participantsmodelpopupLabel">' . get_string('participants', 'local_batching') . '</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                      <span aria-hidden="true">&times;</span>
                                                    </button>
                                                  </div>
                                                  <div class="modal-body" id="participants-list">
                                                  </div>
                                                  <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . get_string('close', 'local_batching') . '</button>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>');

        // Hidden optional params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_batching'));

    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}

