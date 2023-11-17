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

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/user_management/locallib.php');


class batching_proposal_form extends moodleform {


    public function definition() {
        global $USER, $CFG, $DB;

        $systemcontext = context_system::instance();
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $logopath = $CFG->wwwroot . '/theme/edumy/pix/logoscert.jpg';

        $batchings = get_batching_proposal_by_batching_id($id);
        $corrigendum = get_string('corrigendum', 'local_batching');
        $addendum = get_string('addendum', 'local_batching');

        $corrigendumfinancials = get_finanancial_changes_by_batching($id, $corrigendum);

        $addendumfinancials = get_finanancial_changes_by_batching($id, $addendum);
        $hyphen = get_string('hyphen', 'local_batching');
        if (!empty($batchings)) {

            foreach ($batchings as $batching => $proposals) {
                $proposalfileurl = $CFG->wwwroot . "/pluginfile.php/" . $systemcontext->id . "/local_batching/attachment/" . $id . '/' . $proposals->proposal_file;
                $circularfileurl = $CFG->wwwroot . "/pluginfile.php/" . $systemcontext->id . "/local_batching/attachment/" . $id . '/' . $proposals->circular_file;

                $mform->addElement('html', '<div class="proposal-page pt-1">');

                $mform->addElement('html', '<div><a href="' . $proposalfileurl . '" download class="btn btn-primary d-inline-block font-weight-bold pt-2 float-right">' . get_string('downloadproposal', 'local_batching') . '</a>');
                $mform->addElement('html', '<div><a href="' . $circularfileurl . '" download class="btn btn-primary d-inline-block font-weight-bold pt-2 float-right">' . get_string('downloadcircular', 'local_batching') . '</a>');

                $proposed = get_string('proposed', 'local_batching');
                if ($proposals->status == $proposed) {
                    $mform->addElement('html', '<div><h4 class="d-inline-block font-weight-bold pt-2">Status:</h4>');
                    $mform->addElement('html', '<span class="status-new p-1 rounded text-white ml-2">New</span></div>');
                }

                $batched = get_string('batched', 'local_batching');
                if ($proposals->status == $batched) {
                    $mform->addElement('html', '<div><h4 class="d-inline-block font-weight-bold pt-2">' . get_string('status', 'local_batching') . ':</h4>');
                    $mform->addElement('html', '<span class="status-batched p-1 rounded text-white ml-2">' . get_string('batchedstr', 'local_batching') . '</span></div>');
                }

                $approved = get_string('approved', 'local_batching');
                if ($proposals->status == $approved) {
                    $mform->addElement('html', '<div><h4 class="d-inline-block font-weight-bold pt-2">' . get_string('status', 'local_batching') . ':</h4>');
                    $mform->addElement('html', '<span class="status-approved p-1 rounded text-white ml-2">' . get_string('approvedstr', 'local_batching') . '</span></div>');
                }

                $mform->addElement('html', '<div class="p-3" style="text-align: center; clear:both">');
                $mform->addElement('html', '<div class="p-2"><img src="' . $logopath . '" width="60" height="65"/></div>');
                $mform->addElement('html', '<h5 class="font-weight-bold">' . get_string('statecouncil', 'local_batching') . '</h5>');
                $mform->addElement('html', '<h5 class="">(' . get_string('autonomousorganization', 'local_batching') . ')</h5>');
                $mform->addElement('html', '<h5 class="">' . get_string('organizationaddress', 'local_batching') . '</h5>');
                $mform->addElement('html', '</div>');

                $totalfinancialexpenses = 0;
                if (!empty($proposals->financials)) {
                    foreach ($proposals->financials as $financial) {
                        $totalfinancialexpenses += $financial->cost;
                    }
                    custom_money_format($totalfinancialexpenses);
                }

                $custommoney = custom_money_format($totalfinancialexpenses);
                $nodalofficerslist = implode(',' , $proposals->nodalofficers);
                $proposalinfo =  ['custommoney' => $custommoney, 'proposalsname' => $proposals->fullname ,'nodalofficerslist' => $nodalofficerslist ];
                $mform->addElement('html', '<div class="p-3" style="text-align: center">');
                $mform->addElement('html', '<p class="d-inline-block font-weight-bold pt-2">'
                . get_string('administrativeapproval', 'local_batching', $proposalinfo) . '</p>');
                $mform->addElement('html', '</div>');

                $mform->addElement('html', '<div class="header_text" style="text-align: right">');
                $mform->addElement('html', '<div class="header_text">' . get_string('dateformat', 'local_batching'). '</div>');
                $mform->addElement('html', '</div>');

                $readonlyfield = '';
                $readonly = get_string('readonly', 'local_batching');
                if ($proposals->status == $corrigendum || $proposals->status == $addendum) {
                    $readonlyfield = $readonly;
                }
                $mform->addElement('html', '<div class="row" >');
                $mform->addElement('html', '<div class="col-md-6"><div class="form-group">');
                $mform->addElement('text', 'filenumber', get_string('filenumber', 'local_batching'), $readonlyfield);
                $mform->setType('filenumber', PARAM_TEXT);
                $mform->addRule('filenumber', get_string('required'), 'required', null, 'client');
                $mform->addElement('html', '</div></div>');
                $mform->addElement('html', '</div>');

                $mform->addElement('html', '<h5 class="font-weight-bold pt-2">Subject</h5>');
                $mform->addElement('html', '<div class="light-bg p-3">' . $proposals->fullname . '</div>');

                $mform->addElement('html', '<h5 class="font-weight-bold mt-4">Objective</h5>');
                $mform->addElement('html', '<div class="light-bg p-3">' . $proposals->summary . '</div>');

                $mform->addElement('html', '<h5 class="font-weight-bold mt-4">' . get_string('scheduletrainingprogram', 'local_batching') .'</h5>');

                $mform->addElement('html', '<div class="light-bg p-3"><table class="schedule_training_program_table simple-table">');

                $mform->addElement('html', '<thead><tr role="row"><th class"header c0 sorting_asc">' . get_string('cycle', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('startdate', 'local_batching') . '</th> <th class"header c0 sorting_asc">' . get_string('enddate', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('starttime', 'local_batching') . '</th><th class"header c0 sorting_asc">' . get_string('endtime', 'local_batching') . '</th></thead>');

                $venuesdata = [];
                foreach ($proposals->cycles as $cycle => $cycles) {

                    foreach ($cycles->cycletime as $index => $cycletime) {
                        $mform->addElement('html', '<tr class="lastrow odd">');
                        $mform->addElement('html', '<td class="cell c0 sorting_1">' . $cycles->code . '</td>');
                        $mform->addElement('html', '<td class="cell c0 sorting_1">' . customdateformat('DATE_WITHOUT_TIME', $cycletime->starttime) . '</td>');
                        $mform->addElement('html', '<td class="cell c0 sorting_1">' . customdateformat('DATE_WITHOUT_TIME', $cycletime->endtime) . '</td>');
                        $mform->addElement('html', '<td class="cell c0 sorting_1">' . customdateformat('TIME', $cycletime->starttime) . '</td>');
                        $mform->addElement('html', '<td class="cell c0 sorting_1">' . customdateformat('TIME', $cycletime->endtime) . '</td>');
                        $mform->addElement('html', '</tr>');
                    }

                    foreach ($proposals->venues[$cycle] as $diet => $zones) {
                        foreach ($zones as $zone => $school) {
                            $totalbateches = count($proposals->cycles) * count($proposals->cycles[$cycle]->batches);
                            $venue = [
                                'diet' => $diet,
                                'zone' => $zone,
                                'cycles' => count($proposals->cycles),
                                'batches' => count($proposals->cycles[$cycle]->batches),
                                'totalbatches' => $totalbateches,
                            ];
                            $venuesdata[$diet] = $venue;
                        }
                    }

                }

                $mform->addElement('html', '</table></div>');

                $mform->addElement('html', '<h5 class="font-weight-bold mt-4">' . get_string('venues', 'local_batching') . '</h5>');
                $mform->addElement('html', '<div class="light-bg p-3"><p>' . get_string('venuedescription', 'local_batching') . '</p>');

                $mform->addElement('html', '<table class="venue_table simple-table">');

                $mform->addElement('html',
                    "<thead>
                        <tr role='row'>
                            <th class='header c0 sorting_asc'>" . get_string('diets', 'local_batching') . "</th>
                            <th class='header c0 sorting_asc'>" . get_string('zone', 'local_batching') . "</th>
                            <th class='header c0 sorting_asc'>" . get_string('noofcycle', 'local_batching') . "</th>
                            <th class='header c0 sorting_asc'>" . get_string('noofbatchesineachcycle', 'local_batching') . "</th>
                            <th class='header c0 sorting_asc'>" . get_string('totalbatches', 'local_batching') . "</th>
                        </tr>
                    </thead>"
                );
                $venueshtml = '';
                foreach ($venuesdata as $key => $vanue) {
                    $venueshtml .= '<tr class="lastrow odd">';
                    $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['diet'] . '</td>';
                    $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['zone'] . '</td>';
                    $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['cycles'] . '</td>';
                    $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['batches'] . '</td>';
                    $venueshtml .= '<td class="cell c0 sorting_1">' . $vanue['totalbatches'] . '</td>';
                    $venueshtml .= '</tr>';
                }
                $mform->addElement('html', $venueshtml);
                $mform->addElement('html', '</table></div>');

                if ($proposals->beneficiaries) {
                    $mform->addElement('html', '<h5 class="font-weight-bold mt-4">' . get_string('participantsorbeneficiaries', 'local_batching') . '</h5>');
                    $mform->addElement('html', '<div class="light-bg p-3">'.rtrim($proposals->beneficiaries, ',').'</div>');
                }

                $mform->addElement('html', '<h5 class="font-weight-bold mt-4">'. get_string('financialimplications', 'local_batching') . '</h5>');
                $mform->addElement('html', '<div class="light-bg p-3">' . get_string('placedopposite', 'local_batching'));
                $mform->addElement('html', '<ul class="pl-3">');
                $mform->addElement('html', "<li>" . get_string('teaandlunchorganised', 'local_batching') . "</li>");
                $mform->addElement('html', "<li>" . get_string('requiredstationaryandtlm', 'local_batching') . "</li>");
                $mform->addElement('html', "<li>" . get_string('coordinatorassigned', 'local_batching') . "</li>");
                $mform->addElement('html', "<li>" . get_string('filesettledatdiets', 'local_batching') . "</li>");
                $mform->addElement('html', '</ul></div>');

                if (!empty($proposals->financials)) {
                    $financialhtml = '';
                    $totalexpenses = 0;
                    foreach ($proposals->financials as $financial) {
                        $totalexpenses += $financial->cost;
                       
                        $financialhtml .= '<tr class="lastrow odd">';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . (($financial->categoryname) ? $financial->categoryname : $hyphen ) . '</td>';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->title . '</td>';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->unit . '</td>';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . custom_money_format($financial->cost) . '</td>';
                        $financialhtml .= '</tr>';
                    }
                    $financialhtml .= '<tr class="lastrow odd">';
                    $financialhtml .= '<td class="cell c0 sorting_1"></td>';
                    $financialhtml .= '<td class="cell c0 sorting_1"><b>' . get_string('total', 'local_batching') . '</b></td>';
                    $financialhtml .= '<td class="cell c0 sorting_1"></td>';
                    $financialhtml .= '<td class="cell c0 sorting_1"><b>' . custom_money_format($totalexpenses) . '</b></td>';
                    $financialhtml .= '</tr>';

                    $mform->addElement('html', '<h5 class="font-weight-bold mt-4">' . get_string('financials', 'local_batching') . '</h5>');
                    $mform->addElement('html', '<div class="light-bg p-3">');
                    $mform->addElement('html', '<table class="financial_table simple-table">');
                    $mform->addElement('html',
                        '<thead>
                        <tr role="row">
                            <th class"header c0 sorting_asc">' . get_string('category', 'local_batching') . '</th>
                            <th class"header c0 sorting_asc">' . get_string('item', 'local_batching') . '</th>
                            <th class"header c0 sorting_asc">' . get_string('unit', 'local_batching') . '</th>
                            <th class"header c0 sorting_asc">' . get_string('cost', 'local_batching') . '</th>
                        </tr>
                    </thead>'
                    );

                    $mform->addElement('html', $financialhtml);
                    $mform->addElement('html', '</table></div>');
                }
                $mform->addElement('html', '<hr class="my-5" />');
                $mform->addElement('html', '<div class="text-center mt-4">');
                $mform->addElement('html', '<strong class="text-center">' . get_string('annexure_a', 'local_batching') . '</strong>');
                $mform->addElement('html', '</div>');

                $mform->addElement('html', '<div class="batching-distributions accordion" id="accordionCycle">');
                $i = 1;
                foreach ($proposals->participants as $index => $participants) {

                    $mform->addElement('html', '<h5 class="font-weight-bold text-center">' . $proposals->fullname . ' for cycle ' . $i . '</h5>');
                    foreach ($proposals->cycles[$index]->cycletime as $cycletime) {
                        $mform->addElement('html', '<div class="font-weight-bold">Date: ' . customdateformat('DATE_WITHOUT_TIME', $cycletime->starttime) . '</div>');
                        $mform->addElement('html', '<div class="font-weight-bold mb-3">Timings: ' . customdateformat('TIME', $cycletime->starttime) . ' - ' . customdateformat('TIME', $cycletime->endtime) . '</div>');
                    }
                    foreach ($participants as $diet => $users) {

                        $mform->addElement('html', '<div class="cycle mb-3 card">');
                        $mform->addElement('html', '<div class="cycle-title px-3 py-3 text-white font-weight-bold h4 mb-0" id="heading' . $index . '"><span class="" type="button" data-toggle="collapse" data-target="#collapse' . $index . '" aria-expanded="true" aria-controls="collapse' . $index . '">' . $diet . '</span></div>');
                        $mform->addElement('html', '<div class="collapse p-3" id="collapse' . $index . '" aria-labelledby="heading' . $index . '" data-parent="#accordionCycle">');
                        $mform->addElement('html', '<table class="participant_table simple-table">');

                        $mform->addElement('html',
                            '<thead>
                            <tr role="row">
                                <th class="header c0 sorting_asc font-weight-bold">' . get_string('employeeid', 'local_batching') . '</th>
                                <th class="header c0 sorting_asc font-weight-bold">' . get_string('employeename', 'local_batching') . '</th>
                                <th class="header c0 sorting_asc font-weight-bold">' . get_string('schoolidstr', 'local_batching') . '</th>
                                <th class="header c0 sorting_asc font-weight-bold">' . get_string('schoolname', 'local_batching') . '</th>
                                <th class="header c0 sorting_asc font-weight-bold">' . get_string('schooladdress', 'local_batching') . '</th>
                            </tr>
                        </thead>'
                        );

                        foreach ($users as $user) {

                            $mform->addElement('html', '<tr class="lastrow odd">');
                            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $user['id'] . '</td>');
                            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $user['name'] . '</td>');
                            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $user['schoolcode'] . '</td>');
                            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $user['schoolname'] . '</td>');
                            $mform->addElement('html', '<td class="cell c0 sorting_1">' . $user['schooladdress'] ?? $hyphen . '</td>');
                            $mform->addElement('html', '</tr>');
                        }
                        $mform->addElement('html', '</table>');
                        $mform->addElement('html', '</div>');
                        $mform->addElement('html', '</div>');
                    }
                    $i++;
                }
                
                if (!empty($corrigendumfinancials)) {
                    $financialhtml = '';
                    $totalexpenses = 0;
                    foreach ($corrigendumfinancials as $financial) {
                        $totalexpenses += ($financial->cost * $financial->unit);
                        $financialhtml .= '<tr class="lastrow odd">';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . (($financial->categoryname) ? $financial->categoryname : $hyphen) . '</td>';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->title . '</td>';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->unit . '</td>';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . custom_money_format($financial->cost) . '</td>';
                        $financialhtml .= '</tr>';
                    }
                    $financialhtml .= '<tr class="lastrow odd">';
                    $financialhtml .= '<td class="cell c0 sorting_1"></td>';
                    $financialhtml .= '<td class="cell c0 sorting_1"><b>' . get_string('total', 'local_batching') . '</b></td>';
                    $financialhtml .= '<td class="cell c0 sorting_1"></td>';
                    $financialhtml .= '<td class="cell c0 sorting_1"><b>' . custom_money_format($totalexpenses) . '</b></td>';
                    $financialhtml .= '</tr>';

                    $mform->addElement('html', '<h5 class="font-weight-bold mt-4">' . get_string('financialsaftercorrigendum', 'local_batching') . '</h5>');
                    $mform->addElement('html', '<div class="light-bg p-3 mb-4">');
                    $mform->addElement('html', '<table class="financial_table simple-table">');
                    $mform->addElement('html',
                        '<thead>
                        <tr role="row">
                            <th class"header c0 sorting_asc">' . get_string('category', 'local_batching') . '</th>
                            <th class"header c0 sorting_asc">' . get_string('item', 'local_batching') . '</th>
                            <th class"header c0 sorting_asc">' . get_string('unit', 'local_batching') . '</th>
                            <th class"header c0 sorting_asc">' . get_string('cost', 'local_batching') . '</th>
                        </tr>
                    </thead>'
                    );

                    $mform->addElement('html', $financialhtml);
                    $mform->addElement('html', '</table></div></div>');
                }

                if (!empty($addendumfinancials)) {
                    $financialhtml = '';
                    $totalexpenses = 0;
                    foreach ($addendumfinancials as $financial) {
                        $totalexpenses += $financial->cost;

                        $financialhtml .= '<tr class="lastrow odd">';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . (($financial->categoryname) ? $financial->categoryname : $hyphen) . '</td>';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->title . '</td>';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . $financial->unit . '</td>';
                        $financialhtml .= '<td class="cell c0 sorting_1">' . custom_money_format($financial->cost) . '</td>';
                        $financialhtml .= '</tr>';
                    }
                    $financialhtml .= '<tr class="lastrow odd">';
                    $financialhtml .= '<td class="cell c0 sorting_1"></td>';
                    $financialhtml .= '<td class="cell c0 sorting_1"><b>' . get_string('total', 'local_batching') . '</b></td>';
                    $financialhtml .= '<td class="cell c0 sorting_1"></td>';
                    $financialhtml .= '<td class="cell c0 sorting_1"><b>' . custom_money_format($totalexpenses) . '</b></td>';
                    $financialhtml .= '</tr>';

                    $mform->addElement('html', '<h5 class="font-weight-bold mt-4">' . get_string('financialsafteraddendum', 'local_batching') . '</h5>');
                    $mform->addElement('html', '<div class="light-bg p-3 mb-4">');
                    $mform->addElement('html', '<table class="financial_table simple-table">');
                    $mform->addElement('html',
                        '<thead>
                        <tr role="row">
                            <th class"header c0 sorting_asc">' . get_string('category', 'local_batching') . '</th>
                            <th class"header c0 sorting_asc">' . get_string('item', 'local_batching') . '</th>
                            <th class"header c0 sorting_asc">' . get_string('unit', 'local_batching') . '</th>
                            <th class"header c0 sorting_asc">' . get_string('cost', 'local_batching') . '</th>
                        </tr>
                    </thead>'
                    );

                    $mform->addElement('html', $financialhtml);
                    $mform->addElement('html', '</table></div></div>');
                }

            }

            // Hidden optional params.
            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);
            $rejected = get_string('rejected', 'local_batching');
            $approved = get_string('approved', 'local_batching');
            $batched = get_string('batched', 'local_batching');

            if ($proposals->status == $batched ||
                $proposals->status == $approved ||
                $proposals->status == $rejected ||
                $proposals->status == $corrigendum ||
                $proposals->status == $addendum
            ) {
                $mform->addElement('html', '<div class="row" >');
                $mform->addElement('html', '<div class="col-md-6">');
                $mform->addElement('textarea', 'comment', get_string('addcomment', 'local_batching'));
                $mform->setType('comment', PARAM_TEXT);
                $mform->addRule('comment', get_string('required'), 'required', null, 'client');
                $mform->addElement('html', '</div>');
                $mform->addElement('html', '</div>');
            }

            // Buttons.
            if ($proposals->status == $batched) {
                $mform->addElement('html', '<div class="row">');
                if (has_capability('local/batching:approve', $systemcontext)) {
                    $mform->addElement('html', '<div class="col-6">');
                    $this->add_action_buttons(false, get_string('approveproposal', 'local_batching'));
                    $mform->addElement('html', '</div>');
                }

                if (has_capability('local/batching:reject', $systemcontext)) {
                    $mform->addElement('html', '<div class="col-6">');
                    $this->add_action_buttons(false, get_string('rejectproposal', 'local_batching'));
                    $mform->addElement('html', '</div>');
                }
                $mform->addElement('html', '</div>');

            }

            $proposed = get_string('proposed', 'local_batching');
            if ($proposals->status == $proposed || $proposals->status == $rejected) {
                $this->add_action_buttons(true, get_string('savechanges', 'local_batching'));
            }

            if ($proposals->status == $approved ||
                $proposals->status == $corrigendum ||
                $proposals->status == $addendum
            ) {
                $mform->addElement('html', '<div class="row">');
                if (has_capability('local/batching:reject', $systemcontext)) {
                    $mform->addElement('html', '<div class="col-6">');
                    $this->add_action_buttons(false, get_string('rejectproposal', 'local_batching'));
                    $mform->addElement('html', '</div>');
                }
          
                if (has_capability('local/batching:launch', $systemcontext)) {
                    $mform->addElement('html', '<div class="col-6">');
                    $mform->addElement('html', '<div><a href="' . $CFG->wwwroot . '/local/batching/launch_training.php?id=' . $id . ' "  class="btn btn-primary d-inline-block font-weight-bold pt-2">' . get_string('batchinglaunched', 'local_batching') . '</a>');
                    $mform->addElement('html', '</div>');
                }
                $mform->addElement('html', '</div>');

            }
            $mform->addElement('html', '</div>');
        }
    }

    public function validation($data, $files) {
        global $DB;

        $errors = array();
        if ($data['comment'] !== null) {
            if (strlen($data['comment']) > 500) {
                $errors['comment'] = get_string('commentcharacterlimit', 'local_batching');
            }
        }

        $batching = $DB->get_record('local_batching', array('file_number' => $data['filenumber']));
        if ($oldbatching = $DB->get_record('local_batching', array('id' => $data['id']))) {
            if ($batching && $batching->id != $oldbatching->id ) {
                $errors['filenumber'] = get_string('alreadyexists', 'local_batching');
            }
        }

        return $errors;
    }
}
