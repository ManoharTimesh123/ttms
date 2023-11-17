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
 * @package    local_annual_training_calendar
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/annual_training_calendar/locallib.php');
require_once($CFG->dirroot . '/local/user_management/locallib.php');

function render_annual_training_calendar($filters) {

    $table = new html_table();

    $tableheader = array(
        get_string('serialnumber', 'local_annual_training_calendar'),
        get_string('trainingname', 'local_annual_training_calendar'),
        get_string('trainingimage', 'local_annual_training_calendar'),
        get_string('type', 'local_annual_training_calendar'),
        get_string('startdate', 'local_annual_training_calendar'),
        get_string('enddate', 'local_annual_training_calendar'),
        get_string('trainingnumberofdays', 'local_annual_training_calendar'),
        get_string('totalparticipants', 'local_annual_training_calendar'),
        get_string('totalcycles', 'local_annual_training_calendar'),
        get_string('totalbatches', 'local_annual_training_calendar'),
        get_string('status', 'local_annual_training_calendar'),
        get_string('totalfinancialcost', 'local_annual_training_calendar'),
        get_string('action', 'local_annual_training_calendar'),
    );

    $table->head = $tableheader;
    $data = [];
    $output = '';

    $annualtrainingcalendars = get_annual_training_calendar($filters);

    if (!empty($annualtrainingcalendars)) {
        $i = 1;
        foreach ($annualtrainingcalendars as $annualtrainingcalendar) {

            $statusclass = '';

            if ($annualtrainingcalendar->status == get_string('past', 'local_annual_training_calendar')) {
                $statusclass = 'past';
            } else if ($annualtrainingcalendar->status == get_string('ongoing', 'local_annual_training_calendar')) {
                $statusclass = 'ongoing';
            } else if ($annualtrainingcalendar->status == get_string('upcoming', 'local_annual_training_calendar')) {
                $statusclass = 'upcoming';
            } else if ($annualtrainingcalendar->status == get_string('proposed', 'local_annual_training_calendar')) {
                $statusclass = 'proposed';
            }

            $row = array();
            $row[] = $i;
            $row[] = '<span class="table-link">' . $annualtrainingcalendar->traininglink . '</span>';
            $row[] = '<img src="'. $annualtrainingcalendar->trainingimage .'" alt="Training Image">';
            $row[] = $annualtrainingcalendar->trainingtype;
            $row[] = $annualtrainingcalendar->trainingstartdate;
            $row[] = $annualtrainingcalendar->trainingenddate;
            $row[] = $annualtrainingcalendar->trainingnoofdays;
            $row[] = $annualtrainingcalendar->totalparticipants;
            $row[] = $annualtrainingcalendar->totalcycles;
            $row[] = $annualtrainingcalendar->totalbatches;
            $row[] = '<span class="table-status ' . $statusclass . '">' . $annualtrainingcalendar->status . '</span>';
            $row[] = custom_money_format($annualtrainingcalendar->totalcost);
            $row[] = $annualtrainingcalendar->trainindetaillink;
            $data[] = $row;
            $i++;
        }

        $table->data = $data;
        $table->id = 'annual-trainincalendar-list';
        $annualtrainincalendar = html_writer::table($table);
        $annualtrainincalendar .= '
                    <div class="modal fade customized-modal" id="annualtrainingmodelpopup" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="annualtrainingmodelpopupLabel">' . get_string('trainingdetail', 'local_annual_training_calendar') . '</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body" id="training-detail">
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">' . get_string('close', 'local_annual_training_calendar') . '</button>
                          </div>
                        </div>
                      </div>
                    </div>';
        $output .= '<div class="table-responsive">'. $annualtrainincalendar .'</div>';
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_annual_training_calendar') . '</div>';
    }

    return $output;
}

function render_annual_training_calendar_detail_grid($id) {
    global $CFG;

    $annualtrainingcalendar = '';
    $trainingdetail = get_annual_training_calendar([], $id);

    if (!empty($trainingdetail)) {
        $trainingdetail = $trainingdetail[0];

        $annualtrainingcalendar .= '
                            <div class="row">
                              <div class="col-lg-12">
                                <div class="card">
                                  <div class="card-body">
                                    <div class="row">
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('trainingname', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingname . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('type', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingtype . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('startdate', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingstartdate . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('enddate', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingenddate . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('trainingnumberofdays', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingnoofdays . '
                                          </div>
                                        </div>
                                      </div>                                      
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('totalparticipants', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->totalparticipants . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('totalcycles', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->totalcycles . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('totalbatches', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->totalbatches . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('status', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->status . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('totalfinancialcost', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . custom_money_format($trainingdetail->totalcost) . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('totalvenueinvolved', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->totalvenueinvolved . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('totalfacilitators', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->totalfacilitators . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('totalcoordinators', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->totalcoordinators . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-12 pt-2">
                                        <div class="row">
                                          <div class="col-sm-3">
                                            <strong>'. get_string('trainingdescription', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-9">
                                            ' . $trainingdetail->description . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-12 pt-2">
                                        <div class="row">
                                          <div class="col-sm-3">
                                            <strong>'. get_string('trainingimage', 'local_annual_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-9">
                                            <img class="main-img" src="' . $trainingdetail->trainingimage . '" title="Training Image">
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>';
    }

    return $annualtrainingcalendar;
}
