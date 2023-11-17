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
 * @package    local_training_transcript
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/training_transcript/locallib.php');

function render_training_transcript($filters) {
    $table = new html_table();
    $tableheader = array(
        get_string('serialnumber', 'local_training_transcript'),
        get_string('trainingname', 'local_training_transcript'),
        get_string('trainingimage', 'local_training_transcript'),
        get_string('type', 'local_training_transcript'),
        get_string('startdate', 'local_training_transcript'),
        get_string('enddate', 'local_training_transcript'),
        get_string('trainingnumberofdays', 'local_training_transcript'),
        get_string('yourtrainingdays', 'local_training_transcript'),
        get_string('certificate', 'local_training_transcript'),
        get_string('individualhours', 'local_training_transcript'),
        get_string('action', 'local_training_transcript'),
    );

    $table->head = $tableheader;
    $data = [];
    $output = '';

    $trainingtranscriptdata = get_training_transcript($filters);

    if (!empty($trainingtranscriptdata)) {
        $i = 1;
        $totalhours = 0;
        foreach ($trainingtranscriptdata as $trainingtranscript) {
            $row = array();
            $row[] = $i;
            $row[] = '<span class="table-link">'.$trainingtranscript->traininglink.'</span>';
            $row[] = '<img src="'. $trainingtranscript->trainingimage .'" alt="Training Image">';
            $row[] = $trainingtranscript->trainingtype;
            $row[] = $trainingtranscript->trainingstartdate;
            $row[] = $trainingtranscript->trainingenddate;
            $row[] = $trainingtranscript->trainingnoofdays;
            $row[] = $trainingtranscript->attendingdays;
            $row[] = $trainingtranscript->certificate;
            $row[] = $trainingtranscript->traininghours;
            $row[] = $trainingtranscript->trainindetaillink;
            $totalhours = $totalhours + $trainingtranscript->traininghours;
            $data[] = $row;
            $i++;
        }
        $table->data = $data;
        $table->id = 'training-transcript-list';
        $trainingtranscripts = '';

        $trainingtranscripts .= '
                    <div class="float-right pb-2">' . get_string('totalhours', 'local_training_transcript') . ': ' . $totalhours . '
                    </div>';

        $trainingtranscripts .= html_writer::table($table);
        $trainingtranscripts .= '
                <div class="modal fade customized-modal" id="trainingtranscriptmodelpopup" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="trainingtranscriptmodelpopup">' . get_string('trainingdetails', 'local_training_transcript') . '</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body" id="training-detail">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">' . get_string('close', 'local_training_transcript') . '</button>
                      </div>
                    </div>
                  </div>
                </div>';
        $output .= '<div class="table-responsive">'. $trainingtranscripts .'</div>';
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('datamissingerrormsg', 'local_training_transcript') . '</div>';
    }

    return $output;
}

function render_training_transcript_detail_grid($id, $userid) {
    global $CFG;

    $trainingtranscript = '';
    $filter = new stdClass();
    $filter->user = $userid;
    $trainingdetail = get_training_transcript($filter, $id);

    if (!empty($trainingdetail)) {
        $trainingdetail = $trainingdetail[0];

        $trainingtranscript .= '
                            <div class="row">
                              <div class="col-lg-12">
                                <div class="card">
                                  <div class="card-body">
                                    <div class="row">

                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('trainingname', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingname . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('type', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingtype . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('startdate', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingstartdate . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('enddate', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingenddate . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('trainingnumberofdays', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->trainingnoofdays . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('yourtrainingdays', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->attendingdays . '
                                          </div>
                                        </div>
                                      </div>                                      
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('Venue', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->venue . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('cyclecumbatchid', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->cycleandbatch . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('certificate', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->certificate . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-6 pt-2">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <strong>'. get_string('individualhours', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-6">
                                            ' . $trainingdetail->traininghours . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-12 pt-2">
                                        <div class="row">
                                          <div class="col-sm-3">
                                            <strong>'. get_string('trainingdescription', 'local_training_transcript') .'</strong>
                                          </div>
                                          <div class="col-sm-9">
                                            ' . $trainingdetail->trainingdescription . '
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-12 pt-2">
                                        <div class="row">
                                          <div class="col-sm-3">
                                            <strong>'. get_string('trainingimage', 'local_training_transcript') .'</strong>
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

    return $trainingtranscript;
}
