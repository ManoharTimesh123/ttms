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

function add_button($text, $url) {
    $adduserbutton = html_writer::start_tag('div');
    $adduserbutton .= html_writer::link($url,  $text, array('class' => 'btn btn-primary float-right'));
    $adduserbutton .= html_writer::end_tag('div');
    return $adduserbutton;
}

function batching_stepper($step = 1) {
    global $CFG;
    $classes = 'align-items-center d-flex flex-column position-relative text-center ';
    $textcolor = 'mb-1 rounded-pill line-height-5';

    $id = optional_param('id', 0, PARAM_INT); // Batching id.

    for ($i = 1; $i <= 7; $i++) {

        if ($i <= $step) {
            ${"step" . $i} = 'active ';
        }

        if ($i == 1) {
            ${"link" . $i} = new moodle_url($CFG->wwwroot.'/local/batching/index.php', ['id' => $id]);
            ${"linktext" . $i} = 'Proposed Trainings';
        }
        if ($i == 2) {
            ${"link" . $i} = new moodle_url($CFG->wwwroot.'/local/batching/planning.php', ['id' => $id]);
            ${"linktext" . $i} = 'Training Overview';
        }
        if ($i == 3) {
            ${"link" . $i} = new moodle_url($CFG->wwwroot.'/local/batching/filters.php', ['id' => $id]);
            ${"linktext" . $i} = 'Filters';
        }
        if ($i == 4) {
            ${"link" . $i} = new moodle_url($CFG->wwwroot.'/local/batching/venues.php', ['id' => $id]);
            ${"linktext" . $i} = 'Venue Selection';
        }
        if ($i == 5) {
            ${"link" . $i} = new moodle_url($CFG->wwwroot.'/local/batching/distributions.php', ['id' => $id]);
            ${"linktext" . $i} = 'Distributions';
        }
        if ($i == 6) {
            ${"link" . $i} = new moodle_url($CFG->wwwroot.'/local/batching/financials.php', ['id' => $id]);
            ${"linktext" . $i} = 'Financials';
        }
        if ($i == 7) {
            ${"link" . $i} = new moodle_url($CFG->wwwroot.'/local/batching/proposals.php', ['id' => $id]);
            ${"linktext" . $i} = 'Proposal';
        }

        if ($i > $step) {
            ${"link" . $i} = '#';
        }
    }

    $stepper = '<div class="steps-container">';
    $stepper .= '<ul class="stepper py-3 d-flex">';
    for ($i = 1; $i <= 7; $i++) {
        $steps = "step" . $i;
        $stepper .= '<li class="'. ${"step" . $i} . $classes . $steps .'" id="' . $i . '">
        <span class="' . $textcolor . '">';
        if ($i > $step) {
            $stepper .= $i;
        } else {
            $stepper .= '<a href="' . ${"link" . $i} . '">' . $i . '</a>';
        }
        $stepper .= '</span>' . ${"linktext" . $i} . '</li>';
    }
    $stepper .= '</ul>';
    $stepper .= '</div>';

    $stepper .= '<br />';

    echo $stepper;

}

function render_batching() {
    global $CFG, $DB, $OUTPUT;
    require_once($CFG->dirroot.'/local/batching/lib.php');
    require_once($CFG->dirroot.'/local/batching/locallib.php');

    $context = context_system::instance();
    $batchings = get_batchings(null, ['proposed']);
    $output = '';

    $table = new html_table();
    $tableheader = array(
            get_string('trainingname', 'local_batching'),
            get_string('trainingtype', 'local_batching'),
            get_string('nodelofficerassigned', 'local_batching'),
            get_string('dietadminassigned', 'local_batching'),
            get_string('trainingfrom', 'local_batching'),
            get_string('trainingto', 'local_batching'),
            get_string('numberofbatches', 'local_batching'),
            get_string('numberofcycles', 'local_batching'),
            get_string('status', 'local_batching'),
            get_string('createdby', 'local_batching')
        );

        if (has_capability('local/batching:perform', $context)) {
            $tableheader[] = get_string('action', 'local_batching');
        }

    $table->head = $tableheader;
    $data = array();
    $hyphen = get_string('hyphen', 'local_batching');
    if (!empty($batchings)) {
        foreach ($batchings as $batching) {

            if ($batching->status == get_string('proposed', 'local_batching')) {
                $statusclass = 'proposed';
            }

            $coursename = '';
            $row = array();
            $id = $batching->batchingid;
            $course = $batching->course;
            $row[] = $batching->fullname;
            $row[] = $batching->modalitydetails->name;
            $row[] = (!empty($batching->nodalofficers)) ? implode(',', $batching->nodalofficers) : $hyphen;
            $row[] = (!empty($batching->dietheads)) ? implode(',', $batching->dietheads) : $hyphen;
            $row[] = customdateformat('DATE_WITH_TIME', $batching->startdate);
            $row[] = customdateformat('DATE_WITH_TIME', $batching->enddate);
            $row[] = $batching->total_batches;
            $row[] = count($batching->cycles);
            $row[] = '<span class="table-status ' . $statusclass . '">' . $batching->status . '</span>';

            $createdby = $DB->get_record('user', array('id' => $batching->createdby));
            $row[] = fullname($createdby);
            $actionicons = '';
            if (has_capability('local/batching:perform', $context)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/batching/planning.php', array('id' => $id));
                $actionicons = html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }
        $table->size = array('30%', '30%', '20%', '20%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'batching-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#batching-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">No data available</div>';
    }
    return $output;
}

function render_batching_for_approvals() {
    global $CFG, $DB, $OUTPUT;
    require_once($CFG->dirroot.'/local/batching/lib.php');
    require_once($CFG->dirroot.'/local/batching/locallib.php');

    $context = context_system::instance();
    $batchings = get_batchings(null, ['batched', 'approved']);
    $output = '';

    $table = new html_table();
    $tableheader = array(
        get_string('trainingname', 'local_batching'),
        get_string('trainingtype', 'local_batching'),
        get_string('trainingfrom', 'local_batching'),
        get_string('trainingto', 'local_batching'),
        get_string('numberofbatches', 'local_batching'),
        get_string('numberofcycles', 'local_batching'),
        get_string('status', 'local_batching'),
        get_string('createdby', 'local_batching'),
        get_string('updatedby', 'local_batching'),
        get_string('view', 'local_batching')
    );

    $table->head = $tableheader;
    $data = array();
    if (!empty($batchings)) {
        foreach ($batchings as $batching) {

            if ($batching->status == get_string('batched', 'local_batching')) {
                $statusclass = 'proposed';
            } else if ($batching->status == get_string('approved', 'local_batching')) {
                $statusclass = 'ongoing';
            }

            $coursename = '';
            $row = array();
            $id = $batching->batchingid;
            $course = $batching->course;
            $row[] = $batching->fullname;
            $row[] = $batching->modalitydetails->name;
            $row[] = customdateformat('DATE_WITH_TIME', $batching->startdate);
            $row[] = customdateformat('DATE_WITH_TIME', $batching->enddate);
            $row[] = $batching->total_batches;
            $row[] = count($batching->cycles);
            $row[] = '<span class="table-status ' . $statusclass . '">' . $batching->status . '</span>';

            $createdby = $DB->get_record('user', array('id' => $batching->createdby));
            $updatedby = $DB->get_record('user', array('id' => $batching->updatedby));
            $row[] = fullname($createdby);
            $row[] = fullname($updatedby);
            $actionicons = '';
            if (has_capability('local/batching:approve', $context) ||
                has_capability('local/batching:launch', $context)
            ) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/batching/proposals.php', array('id' => $id));
                $actionicons = html_writer::link($actionurl,
                              html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                             'title' => 'View Proposal', 'class' => 'iconsmall')));
            }
            $row[] = $actionicons;
            $data[] = $row;
        }

        $table->size = array('30%', '30%', '20%', '20%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'batching-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#batching-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">No data available</div>';
    }
    return $output;
}


function render_batchingplanning() {
    global $CFG, $DB, $OUTPUT;
    require_once($CFG->dirroot.'/local/batching/lib.php');
    require_once($CFG->dirroot.'/local/batching/locallib.php');

    $context = context_system::instance();
    $batchings = get_batchings();
    $output = '';

    $table = new html_table();
    $tableheader = array(
        get_string('trainingname', 'local_batching'),
        get_string('trainingtype', 'local_batching'),
        get_string('trainingfrom', 'local_batching'),
        get_string('trainingto', 'local_batching'),
        get_string('numberofbatches', 'local_batching'),
        get_string('numberofcycles', 'local_batching'),
        get_string('status', 'local_batching'),
        get_string('createdby', 'local_batching')
    );

    if (has_capability('local/batching:add', $context)) {
        array_push($tableheader, get_string('action', 'local_batching'));
    }

    $table->head = $tableheader;
    $data = array();
    if (!empty($batchings)) {
        foreach ($batchings as $batching) {

            if ($batching->status == get_string('approved', 'local_batching')) {
                $statusclass = 'ongoing';
            }

            $coursename = '';
            $row = array();
            $id = $batching->id;
            $course = $batching->course;
            $row[] = $batching->fullname;
            $row[] = $batching->name;
            $row[] = customdateformat('DATE_WITH_TIME', $batching->startdate);
            $row[] = customdateformat('DATE_WITH_TIME', $batching->enddate);
            $row[] = count($batching->batches);
            $row[] = count($batching->cycles);
            $row[] = '<span class="table-status ' . $statusclass . '">' . $batching->status . '</span>';

            $createdby = $DB->get_record('user', array('id' => $batching->createdby));
            $row[] = fullname($createdby);

            if (has_capability('local/batching:add', $context)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/batching/planning.php', array('id' => $id, 'course' => $course));
                $actionicons = html_writer::link($actionurl,
                              html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                             'title' => 'Edit', 'class' => 'iconsmall')));

                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table->size = array('30%', '30%', '20%', '20%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'batching-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#batching-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">No data available</div>';
    }
    return $output;
}

function render_batching_approved() {
    global $CFG, $DB, $OUTPUT;
    require_once($CFG->dirroot.'/local/batching/lib.php');
    require_once($CFG->dirroot.'/local/batching/locallib.php');

    $context = context_system::instance();
    $batchings = get_batchings(null, ['approved']);
    $output = '';

    $table = new html_table();
    $tableheader = array(
        get_string('trainingname', 'local_batching'),
        get_string('trainingtype', 'local_batching'),
        get_string('trainingfrom', 'local_batching'),
        get_string('trainingto', 'local_batching'),
        get_string('numberofbatches', 'local_batching'),
        get_string('numberofcycles', 'local_batching'),
        get_string('status', 'local_batching'),
        get_string('createdby', 'local_batching'),
        get_string('updatedby', 'local_batching'),
    );

    $table->head = $tableheader;
    $data = array();
    if (!empty($batchings)) {
        foreach ($batchings as $batching) {

            if ($batching->status == get_string('approved', 'local_batching')) {
                $statusclass = 'ongoing';
            }

            $coursename = '';
            $row = array();
            $id = $batching->batchingid;
            $course = $batching->course;
            $row[] = $batching->fullname;
            $row[] = $batching->modalitydetails->name;
            $row[] = customdateformat('DATE_WITH_TIME', $batching->startdate);
            $row[] = customdateformat('DATE_WITH_TIME', $batching->enddate);
            $row[] = $batching->total_batches;
            $row[] = count($batching->cycles);
            $row[] = '<span class="table-status ' . $statusclass . '">' . $batching->status . '</span>';

            $createdby = $DB->get_record('user', array('id' => $batching->createdby));
            $updatedby = $DB->get_record('user', array('id' => $batching->updatedby));
            $row[] = fullname($createdby);
            $row[] = fullname($updatedby);
            $data[] = $row;
        }

        $table->size = array('30%', '30%', '20%', '20%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'batching-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#batching-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">No data available</div>';
    }
    return $output;
}
