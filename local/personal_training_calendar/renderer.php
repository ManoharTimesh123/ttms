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
 * The personal_training_calendar Management
 *
 * @package    local_personal_training_calendar
 * @author     Lalit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

function render_personal_training_calender_table($filtersql, $userid) {
    $output = '';
    $tableheader = array(
        get_string('sno', 'local_personal_training_calendar'),
        get_string('coursename', 'local_personal_training_calendar'),
        get_string('trainingimage', 'local_personal_training_calendar'),
        get_string('type', 'local_personal_training_calendar'),
        get_string('startdate', 'local_personal_training_calendar'),
        get_string('enddate', 'local_personal_training_calendar'),
        get_string('noofdays', 'local_personal_training_calendar'),
        get_string('status', 'local_personal_training_calendar'),
        get_string('facilitator', 'local_personal_training_calendar'),
        get_string('coordinators', 'local_personal_training_calendar'),
        get_string('action', 'local_personal_training_calendar')
    );
    $table = new html_table();
    $tabledata = get_personal_training_calendar($filtersql, $userid);
    if ($tabledata) {
        $table->head = $tableheader;
        $sno = 1;
        foreach ($tabledata as $tabledataobj) {

            $statusclass = '';

            if ($tabledataobj->status == 'Past') {
                $statusclass = 'past';
            } else if ($tabledataobj->status == 'Ongoing') {
                $statusclass = 'ongoing';
            } else if ($tabledataobj->status == 'Upcoming') {
                $statusclass = 'upcoming';
            } else if ($tabledataobj->status == 'Proposed') {
                $statusclass = 'proposed';
            }

            $row = array();
            $row[] = $sno;
            $row[] = '<span class="table-link">'.$tabledataobj->traininglink.'</span>';
            $row[] = '<img src="'. $tabledataobj->trainingimage .'" alt="Training Image">';
            $row[] = $tabledataobj->trainingtype;
            $row[] = $tabledataobj->startdate;
            $row[] = $tabledataobj->enddate;
            $row[] = $tabledataobj->days;
            $row[] = '<span class="table-status ' . $statusclass . '">' . $tabledataobj->status . '</span>';
            $row[] = $tabledataobj->facilitator;
            $row[] = $tabledataobj->coordinator;
            $row[] = $tabledataobj->trainindetaillink;
            $data[] = $row;
            $sno++;
        }

        $table->data = $data;
        $table->id = 'personal_training_calendar_list';
        $out = html_writer::table($table);
        $out .= '
            <div class="modal fade customized-modal" id="personaltrainingmodelpopup" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="personaltrainingmodelpopupLabel">' . get_string('trainigdetails', 'local_personal_training_calendar') . '</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body" id="training-detail">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"> ' . get_string('close', 'local_personal_training_calendar') . '</button>
                  </div>
                </div>
              </div>
            </div>';
        $output .= '<div class="table-responsive">' . $out . '</div>';
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('datamissingerrormsg', 'local_personal_training_calendar') . '</div>';
    }
    return $output;
}

function render_personal_training_calender_detail_grid($id) {
    global $USER, $CFG;

    $personaltrainingcalendar = '';
    $trainingdetail = get_personal_training_calendar([], $USER->id, $id);

    if (!empty($trainingdetail)) {
        $trainingdetail = $trainingdetail[0];
        $statusclass = '';

        if ($trainingdetail->status == 'Past') {
            $statusclass = 'past';
        } else if ($trainingdetail->status == 'Ongoing') {
            $statusclass = 'ongoing';
        } else if ($trainingdetail->status == 'Upcoming') {
            $statusclass = 'upcoming';
        } else if ($trainingdetail->status == 'Proposed') {
            $statusclass = 'proposed';
        }
        $personaltrainingcalendar .= '
                            <div class="row">
                              <div class="col-lg-12">
                                <div class="card">
                                  <div class="card-body">
                                    <div class="row">
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('coursename', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingname . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('type', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingtype . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('startdate', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->startdate . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('enddate', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->enddate . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('noofdays', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->days . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('traningday', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingdays . '
                                          </div>
                                        </div>
                                      </div>                                      
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('venue', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->venue . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('cycle/batchid', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->cycle . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('status', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            <span class="table-status ' . $statusclass . '">' . $trainingdetail->status . '</span>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('facilitator', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->facilitator . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('coordinators', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->coordinator . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-12 pt-2">
                                        <div class="row">
                                          <div class="col-sm-3">
                                            <strong>'. get_string('coursesummary', 'local_personal_training_calendar') .'</strong>
                                          </div>
                                          <div class="col-sm-9">
                                            ' . $trainingdetail->summary . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-12 pt-2">
                                        <div class="row">
                                          <div class="col-sm-3">
                                            <strong>'. get_string('trainingimage', 'local_personal_training_calendar') .'</strong>
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

    return $personaltrainingcalendar;
}
