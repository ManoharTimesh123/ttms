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
 * @author  Tarun Upadhyay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class batching_venue_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];

        $minparticipant = 35;
        $maxparticipant = 55;

        $batches = get_batches_based_on_filters($id);
        
        $cycles = $batches['cycles'];
        $schools = $cycles['totalschools'];

        $mform->addElement('html', '
                            <ul class="venues-count">
                                <li class="shadow-sm d-inline-block py-2 px-3 rounded-lg mr-2 mb-3"> ' . get_string('schoolsimpacted', 'local_batching') .' : <span class="h2 m-0 pl-2">' . $schools . '</span> </li>
                                <li class="shadow-sm d-inline-block py-2 px-3 rounded-lg mr-2 mb-3">' . get_string('cycles', 'local_batching') . ' : <span class="h2 m-0 pl-2">' . $cycles["totalcycles"] . '</span> </li>
                                <li class="shadow-sm d-inline-block py-2 px-3 rounded-lg mr-2 mb-3">' . get_string('Batchesheading', 'local_batching') . ' : <span class="h2 m-0 pl-2">' . $batches["totalbatches"] . '</span> </li>
                                <li class="shadow-sm d-inline-block py-2 px-3 rounded-lg mr-2 mb-3">' . get_string('participantperbatch', 'local_batching') . ': <span class="h2 m-0 pl-2">' . $batches["participantsperbatch"] . '</span> </li>
                                <li class="shadow-sm d-inline-block py-2 px-3 rounded-lg mr-2 mb-3">' . get_string('participantafteradjustment', 'local_batching') . ' : <span class="h2 m-0 pl-2">' . $batches["participantsperbatchadjusted"] . '</span> </li>
                                <li class="shadow-sm d-inline-block py-2 px-3 rounded-lg mr-2 mb-3">' . get_string('participantpicked', 'local_batching') . ': <span class="h2 m-0 pl-2">' . $cycles['totalparticipantsofschoolswithoutpercentage'] . '</span> </li>
                            </ul>');

        $venues = get_venues_based_on_filters($id);
        $batchingvenues = get_batching_venues($id);

        $mform->addElement('html', '<h4 class="lastrow odd">' . get_string('availablevenuesfilter', 'local_batching') . '</h4>');
        $mform->addElement('html', '<table id="venue_table" class="venue_table generaltable dataTable no-footer">');
        $mform->addElement('html', '<thead><tr role="row"><th class"header c0 sorting_asc">' . get_string('sno', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('schoolname', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('schoolid', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('zone', 'local_batching') . '</th><th class"header c0 sorting_asc">'. get_string('district', 'local_batching') . '</th><th class"header c0 sorting_asc">'. get_string('capacity', 'local_batching') . '</th><th class"header c0 sorting_asc"><input type="checkbox" id="selectall" class="mr-2"><label for="selectall">'. get_string('selectall', 'local_batching') . '</label></th></tr></thead>');

        $canproceed = false;
        $approvedvenues = 0;

        foreach ($venues as $index => $d) {
            $requestalreadysent = check_venue_already_send_for_approval($index, $id);

            $showvenue = true;

            if ($requestalreadysent) {
                $showvenue = false;
            }

            if ($showvenue) {
                $mform->addElement('html', '<tr class="lastrow odd">');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . $index . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->name . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->code . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->zone_id . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->districtname . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->venue_capacity . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1">');

                if ($d->venuestatus == null) {
                    $mform->addElement('checkbox', 'sendforapproval[' . $d->id . ']', '');
                    $mform->setType('sendforapproval[' . $d->id . ']', PARAM_TEXT);
                } else {
                    $mform->addElement('html', '<div class="circle" id="">' . get_string('hyphen', 'local_batching') . '</div>');
                }
            }

            $mform->addElement('html', '</td>');
            $mform->addElement('html', '</tr>');
        }

        $mform->addElement('html', '</table>');

        // Hidden optional params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_batching'));


        $mform->addElement('html', '<h4 class="lastrow odd">' . get_string('batchingvenues', 'local_batching') . '</h4>');
        $mform->addElement('html', '<table class="venue_table generaltable dataTable no-footer">');
        $mform->addElement('html', '<thead><tr role="row">
            <th class"header c0 sorting_asc">' . get_string('sno', 'local_batching') . '</th>
            <th class"header c0 sorting_asc">' . get_string('schoolname', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('schoolid', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('zone', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('district', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('capacity', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('status', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('action', 'local_batching') . '</th></tr></thead>');

        $approvedvenues = 0;
        $pendingapproval = get_string('pendingapproval', 'local_batching');
        $approved = get_string('approvedstr', 'local_batching');
        foreach ($batchingvenues as $index => $d) {
            if (empty($d->venuestatus)) {
           
                $venuestatus = '<span class="badge-secondary p-2 rounded-pill">Request not sent yet</span>';
            } else if ($d->venuestatus == $pendingapproval) {
                
                $venuestatus = '<span class="badge-primary p-2 rounded-pill">' . $d->venuestatus . '</span>';
            } else if ($d->venuestatus == $approved) {
               
                $approvedvenues++;
                $venuestatus = '<span class="badge-success p-2 rounded-pill">' . $d->venuestatus . '</span>';
            }

           
            if (
                $approvedvenues >= $batches['totalbatches'] &&
                $cycles["totalcycles"] > 0 &&
                $batches["totalbatches"] > 0 &&
                $batches["participantsperbatchadjusted"] >= $minparticipant &&
                $batches["participantsperbatchadjusted"] <= $maxparticipant
            ) {
                $canproceed = true;
            }

            $mform->addElement('html', '<tr class="lastrow odd">');
            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $index . '</td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->name . '</td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->code . '</td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->zone_id . '</td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->districtname . '</td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $d->venue_capacity . '</td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $venuestatus . '</td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1">');

            if ($d->venuestatus == null) {
                $mform->addElement('checkbox', 'sendforapproval[' . $d->id . ']', '');
                $mform->setType('sendforapproval[' . $d->id . ']', PARAM_TEXT);
            } else {
                $mform->addElement('html', '<div class="circle" id="">' . get_string('hyphen', 'local_batching') . '</div>');
            }

            $mform->addElement('html', '</td>');
            $mform->addElement('html', '</tr>');
        }

        $mform->addElement('html', '</table>');

        if ($canproceed) {
            $distributionurl = new moodle_url($CFG->wwwroot.'/local/batching/distributions.php', array('id' => $id));
            $mform->addElement('hidden', 'cycles', $cycles["totalcycles"]);
            $mform->addElement('hidden', 'batches', $batches['totalbatches']);
            $mform->addElement('hidden', 'distributionurl', $distributionurl);

            $mform->addElement('html', '<input type="submit" id="distribution_button" name="submitbutton" value="' . get_string('proceeddistribution', 'local_batching') . '" class="btn btn-primary" style="float:right; background-color:green !important; border: green !important;">');
        } else {
            $mform->addElement('html', '<div class="">' . get_string('userapprovalmsg', 'local_batching') . '</div>');
        }

    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
