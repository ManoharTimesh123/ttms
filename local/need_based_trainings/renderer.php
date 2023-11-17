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
 * Need Based Trainings
 * @package    local_need_based_trainings
 */
defined('MOODLE_INTERNAL') || die();

function render_show_interest_form() {
    $showinterestform = '';
    $showinterestform .= '<form name="needbasedtrainingfrom" id="needbasedtrainingfrom">';

    $showinterestform .= '<div class="form-group row">';
    $showinterestform .= '<label class="col-sm-2 col-form-label"></label>';
    $showinterestform .= '<div class="col-sm-6">';
    $showinterestform .= '<input type="checkbox" name="jointraining" class="jointraining form-check-input" id="jointraining">';
    $showinterestform .= '<label class="form-check-label" for="jointraining">Are you interested?</label>';
    $showinterestform .= '<div class="response"></div>';
    $showinterestform .= '</div>';
    $showinterestform .= '</div>';

    $showinterestform .= '<div class="showreasonarea" style="display:none">';
    $showinterestform .= '<div class="form-group row">';
    $showinterestform .= '<label for="reason" class="col-sm-2 col-form-label">Reason</label>';
    $showinterestform .= '<div class="col-sm-6">';
    $showinterestform .= '<textarea class="form-control" name="reason" id="reason" rows="3"></textarea>';
    $showinterestform .= '<div class="error"></div>';
    $showinterestform .= '</div>';

    $showinterestform .= '</div>';
    $showinterestform .= '<div class="form-group row"><label class="col-sm-2 col-form-label"></label><div class="col-sm-6">';
    $showinterestform .= '<button type="submit" class="btn btn-primary">Submit</button>';
    $showinterestform .= '</div></div>';
    $showinterestform .= '</div>';
    $showinterestform .= '</form>';

    return $showinterestform;
}

function render_need_based_trainings($filter) {
    $systemcontext = context_system::instance();

    $table = new html_table();

    $tableheader = array(
        get_string('serialnumber', 'local_need_based_trainings'),
        get_string('trainingname', 'local_need_based_trainings'),
        get_string('trainingimage', 'local_need_based_trainings'),
        get_string('type', 'local_need_based_trainings'),
        get_string('startdate', 'local_need_based_trainings'),
        get_string('enddate', 'local_need_based_trainings'),
    );
    if (has_capability('local/need_based_trainings:viewall', $systemcontext)) {
        $newheaderelement = get_string('user', 'local_need_based_trainings');
        $index = 3;
        array_splice($tableheader, $index, 0, $newheaderelement);
    }
    $table->head = $tableheader;
    $data = [];
    $output = '';

    $needbasedtrainings = get_need_based_requested_training($filter);

    if (!empty($needbasedtrainings)) {
        $i = 1;
        foreach ($needbasedtrainings as $needbasedtrainings) {

            $row = array();
            $row[] = $i;
            $row[] = '<span class="table-link">' . $needbasedtrainings->traininglink . '</span>';
            $row[] = '<img src="'. $needbasedtrainings->trainingimage .'" alt="Training Image">';
            if (has_capability('local/need_based_trainings:viewall', $systemcontext)) {
                $row[] = $needbasedtrainings->user;
            }
            $row[] = $needbasedtrainings->trainingtype;
            $row[] = $needbasedtrainings->trainingstartdate;
            $row[] = $needbasedtrainings->trainingenddate;
            $data[] = $row;
            $i++;
        }

        $table->data = $data;
        $table->id = 'need-based-training-list';
        $needbasedtrainingdata = html_writer::table($table);
        $output .= '<div class="table-responsive">'. $needbasedtrainingdata .'</div>';
    } else {
        $output = '<div class="alert alert-info w-100 float-left">No data available</div>';
    }

    return $output;
}
