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
 * The modality Management
 *
 * @package local_modality
 * @author Nadia Farheen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

function add_button($text, $url) {
    $adduserbutton = html_writer::start_tag('div');
    $adduserbutton .= html_writer::link($url,  $text, array('class' => 'btn btn-primary float-right m-0 mb-3'));
    $adduserbutton .= html_writer::end_tag('div');
    return $adduserbutton;
}

function render_modality() {
    global $CFG, $DB, $OUTPUT;
    require_once($CFG->dirroot.'/local/modality/lib.php');
    require_once($CFG->dirroot.'/local/modality/locallib.php');

    $systemcontext = context_system::instance();
    $modalities = get_modalities();
    $output = '';

    $table = new html_table();
    $tableheader = array(get_string('name', 'local_modality'),
                        get_string('shortname', 'local_modality'),
                        get_string('createdby', 'local_modality')
                        );

    if (has_capability('local/modality:delete', $systemcontext) ||
        has_capability('local/modality:edit', $systemcontext)
    ) {
        $tableheader[] = get_string('action', 'local_modality');
    }

    $table->head = $tableheader;
    $data = array();
    if (!empty($modalities)) {
        foreach ($modalities as $modality) {
            $coursename = '';
            $row = array();
            $id = $modality->id;
            $row[] = $modality->name;
            $row[] = $modality->shortname;

            $createdby = $DB->get_record('user', array('id' => $modality->createdby));
            $row[] = fullname($createdby);
            $actionicons = "";
            if (has_capability('local/modality:edit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/modality/edit.php', array('id' => $id));
                $actionicons .= html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:delete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot.'/local/modality/edit.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table->size = array('30%', '30%', '30%', '10%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'modality-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#modality-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }
    return $output;
}

function render_coursetype() {
    global $PAGE, $CFG, $DB, $OUTPUT;
    $modalities = '';

    $context = context_system::instance();
    $coursetypes = $DB->get_records('local_coursetype');
    $output = '';
    $data = array();
    if (!empty($coursetypes)) {
        foreach ($coursetypes as $key => $coursetype) {
            $coursename = '';
            $row = array();
            $id = $coursetype->id;
            $row[] = $coursetype->name;
            $row[] = $coursetype->shortname;

            $createdby = $DB->get_record('user', array('id' => $coursetype->usercreated));
            $createddate = customdateformat('DATE_WITHOUT_TIME', $coursetype->timecreated);
            $row[] = fullname($createdby);
            $row[] = $createddate;

            if (has_capability('local/modality:add', $PAGE->context)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/modality/coursetype.php', array('id' => $id));
                $actionicons = html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
                if (has_capability('local/modality:manage', $PAGE->context)) {
                    $deleteurl = new moodle_url($CFG->wwwroot.'/local/modality/coursetype.php', array('id' => $id, 'delete' => 1));
                    $actionicons .= html_writer::link($deleteurl,
                                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                                    'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
                }
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $tableheader = array(get_string('coursetypename', 'local_modality'),
                            get_string('coursecode', 'local_modality'),
                            get_string('createdby', 'local_modality'),
                            get_string('createddate', 'local_modality'),
                            );

        if (has_capability('local/modality:add', $PAGE->context)) {
            array_push($tableheader, get_string('action', 'local_modality'));
        }

        $table = new html_table();
        $table->head = $tableheader;
        $table->size = array('25%', '25%', '20%', '15', '10%');
        $table->align = array('center', 'center', 'center', 'center', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'coursetype-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#coursetype-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_departments() {
    global $PAGE, $CFG, $DB, $OUTPUT;
    $modalities = '';

    $systemcontext = context_system::instance();
    $departments = $DB->get_records('local_departments');
    $output = '';
    $data = array();
    if (!empty($departments)) {
        foreach ($departments as $key => $department) {
            $coursename = '';
            $row = array();
            $id = $department->id;
            $row[] = $department->name;
            $row[] = $department->code;

            $createdby = $DB->get_record('user', array('id' => $department->usercreated));
            $createddate = customdateformat('DATE_WITHOUT_TIME', $department->timecreated);
            $row[] = fullname($createdby);
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:departmentedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/modality/departments.php', array('id' => $id));
                $actionicons .= html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:departmentdelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot.'/local/modality/departments.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $tableheader = array(get_string('departmentname', 'local_modality'),
                            get_string('departmentcode', 'local_modality'),
                            get_string('createdby', 'local_modality'),
                            get_string('createddate', 'local_modality')
                            );

        if (has_capability('local/modality:departmentdelete', $systemcontext) ||
            has_capability('local/modality:departmentedit', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }
        $table = new html_table();
        $table->head = $tableheader;
        $table->size = array('25%', '25%', '20%', '15', '10%');
        $table->align = array('center', 'center', 'center', 'center', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'department-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#department-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_subjects() {
    global $PAGE, $CFG, $DB, $OUTPUT;
    $modalities = '';

    $systemcontext = context_system::instance();
    $subjects = $DB->get_records('local_subjects');
    $output = '';
    $data = array();
    if (!empty($subjects)) {
        foreach ($subjects as $key => $subject) {
            $coursename = '';
            $row = array();
            $id = $subject->id;
            $row[] = $subject->name;
            $row[] = $subject->code;

            $createdby = $DB->get_record('user', array('id' => $subject->usercreated));
            $createddate = customdateformat('DATE_WITHOUT_TIME', $subject->timecreated);
            $row[] = fullname($createdby);
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:subjectedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/modality/subjects.php', array('id' => $id));
                $actionicons .= html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:subjectdelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot.'/local/modality/subjects.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $tableheader = array(get_string('subjectname', 'local_modality'),
                            get_string('subjectcode', 'local_modality'),
                            get_string('createdby', 'local_modality'),
                            get_string('createddate', 'local_modality')
                            );

        if (has_capability('local/modality:subjectdelete', $systemcontext) ||
            has_capability('local/modality:subjectedit', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table = new html_table();
        $table->head = $tableheader;
        $table->size = array('25%', '25%', '20%', '15', '10%');
        $table->align = array('center', 'center', 'center', 'center', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'subject-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#subject-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_districts() {
    global $CFG, $DB, $OUTPUT;

    $systemcontext = context_system::instance();
    $districts = $DB->get_records('local_districts');
    $output = '';
    $data = array();
    if (!empty($districts)) {
        foreach ($districts as $key => $district) {
            $coursename = '';
            $row = array();
            $id = $district->id;
            $row[] = $district->name;
            $row[] = $district->code;
            $deps = explode(',', $district->departments);
            $depname = array();
            foreach ($deps as $dep) {
                $depname[] = $DB->get_field('local_departments', 'name', array('id' => $dep));
            }
            $row[] = implode(',<br/>', $depname);

            $createdby = $DB->get_record('user', array('id' => $district->usercreated));
            $createddate = customdateformat('DATE_WITHOUT_TIME', $district->timecreated);
            $row[] = fullname($createdby);
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:districtedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/modality/districts.php', array('id' => $id));
                $actionicons .= html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
            }
            if (has_capability('local/modality:districtdelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot.'/local/modality/districts.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $tableheader = array(get_string('districtname', 'local_modality'),
                            get_string('coursecode', 'local_modality'),
                            get_string('departments', 'local_modality'),
                            get_string('createdby', 'local_modality'),
                            get_string('createddate', 'local_modality')
                            );

        if (has_capability('local/modality:districtedit', $systemcontext) ||
            has_capability('local/modality:districtdelete', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table = new html_table();
        $table->head = $tableheader;
        $table->size = array('25%', '20%', '25%', '10%', '10', '10%');
        $table->align = array('left', 'center', 'center', 'center', 'center', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'district-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#district-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_zones() {
    global $PAGE, $CFG, $DB, $OUTPUT;

    $systemcontext = context_system::instance();
    $zones = $DB->get_records('local_zones');

    $output = '';
    $data = array();
    if (!empty($zones)) {
        foreach ($zones as $key => $zone) {
            $coursename = '';
            $row = array();
            $id = $zone->id;
            $row[] = $zone->name;
            $row[] = $zone->code;
            $deps = explode(',', $zone->departments);
            $depname = array();
            foreach ($deps as $dep) {
                $depname[] = $DB->get_field('local_departments', 'name', array('id' => $dep));
            }
            $row[] = implode(',<br/>', $depname);
            $row[] = $DB->get_field('local_diets', 'name', array('id' => $zone->diet));

            $createdby = $DB->get_record('user', array('id' => $zone->usercreated));
            $createddate = customdateformat('DATE_WITHOUT_TIME', $zone->timecreated);
            $row[] = fullname($createdby);
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:zoneedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/modality/zones.php', array('id' => $id));
                $actionicons .= html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:zonedelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot.'/local/modality/zones.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table = new html_table();
        $tableheader = array(get_string('zonename', 'local_modality'),
                            get_string('zonecode', 'local_modality'),
                            get_string('departments', 'local_modality'),
                            get_string('diets', 'local_modality'),
                            get_string('createdby', 'local_modality'),
                            get_string('createddate', 'local_modality')
                            );

        if (has_capability('local/modality:zoneedit', $systemcontext) ||
            has_capability('local/modality:zonedelete', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table->head = $tableheader;
        $table->size = array('20%', '15%', '15%', '15', '10%', '15%', '10%');
        $table->align = array('left', 'center', 'center', 'center', 'center', 'center', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'zones-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#zones-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }
    return $output;
}

function render_diets() {
    global $CFG, $DB, $OUTPUT;

    $systemcontext = context_system::instance();
    $diets = $DB->get_records('local_diets');

    $output = '';
    $data = array();
    if (!empty($diets)) {
        foreach ($diets as $key => $diet) {
            $coursename = '';
            $row = array();
            $id = $diet->id;
            $row[] = $diet->name;
            $row[] = $diet->code;
            $deps = explode(',', $diet->departments);
            $depname = array();
            foreach ($deps as $dep) {
                $depname[] = $DB->get_field('local_departments', 'name', array('id' => $dep));
            }
            $row[] = implode(',<br/>', $depname);
            $row[] = $DB->get_field('local_districts', 'name', array('id' => $diet->district_id));

            $diethead = 'N/A';
            if ($diet->head) {
                $diethead = $DB->get_record('user', array('id' => $diet->head));
                $diethead = fullname($diethead);
            }
            $row[] = $diethead;

            $createdby = $DB->get_record('user', array('id' => $diet->usercreated));
            $createddate = customdateformat('DATE_WITHOUT_TIME', $diet->timecreated);
            $row[] = fullname($createdby);
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:dietedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/modality/diets.php', array('id' => $id));
                $actionicons .= html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:dietdelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot.'/local/modality/diets.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table = new html_table();
        $tableheader = array(get_string('dietname', 'local_modality'),
                            get_string('dietcode', 'local_modality'),
                            get_string('departments', 'local_modality'),
                            get_string('zones', 'local_modality'),
                            'Head',
                            get_string('createdby', 'local_modality'),
                            get_string('createddate', 'local_modality')
                            );

        if (has_capability('local/modality:dietdelete', $systemcontext) ||
            has_capability('local/modality:dietedit', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table->head = $tableheader;
        $table->size = array('15%', '10%', '15%', '15%', '15%', '10%', '10%', '10%');
        $table->align = array('left', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'diets-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#diets-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_schools() {
    global $CFG, $DB, $OUTPUT;

    $systemcontext = context_system::instance();
    $schools = $DB->get_records('local_schools');

    $output = '';
    $data = array();
    if (!empty($schools)) {
        foreach ($schools as $key => $school) {
            $coursename = '';
            $row = array();
            $id = $school->id;
            $row[] = $school->name;
            $row[] = $school->code;

            $deps = explode(',', $school->departments);
            $depname = array();
            foreach ($deps as $dep) {
                $depname[] = $DB->get_field('local_departments', 'name', array('id' => $dep));
            }
            $row[] = implode(',<br/>', $depname);
            $row[] = $DB->get_field('local_zones', 'name', array('id' => $school->zone_id));

            $schoolhos = 'N/A';
            if ($school->hos) {
                $schoolhos = $DB->get_record('user', array('id' => $school->hos));
                $schoolhos = fullname($schoolhos);
            }
            $row[] = $schoolhos;
            $isvenue = 'No';
            if ($school->isvenue == 1) {
                $isvenue = 'Yes';
            }
            $row[] = $isvenue;

            $createdby = $DB->get_record('user', array('id' => $school->usercreated));
            $createddate = customdateformat('DATE_WITHOUT_TIME', $school->timecreated);
            $row[] = fullname($createdby);
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:schooledit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/modality/schools.php', array('id' => $id));
                $actionicons .= html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:schooldelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot.'/local/modality/schools.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table = new html_table();
        $tableheader = array(get_string('schoolname', 'local_modality'),
                            get_string('schoolcode', 'local_modality'),
                            get_string('departments', 'local_modality'),
                            'Zone',
                            'Head of school',
                            'Venue',
                            get_string('createdby', 'local_modality'),
                            get_string('createddate', 'local_modality')
                            );

        if (has_capability('local/modality:schooledit', $systemcontext) ||
            has_capability('local/modality:schooldelete', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table->head = $tableheader;

        $table->size = array('15%', '10%', '15%', '15', '15%', '10%', '10%', '10%');
        $table->align = array('left', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'schools-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#schools-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality'). '</div>';
    }
    return $output;
}

function render_school_positions() {
    global $CFG, $DB, $OUTPUT;

    $systemcontext = context_system::instance();
    $schoolpositions = $DB->get_records('local_school_positions');

    $output = '';
    $data = array();
    if (!empty($schoolpositions)) {
        foreach ($schoolpositions as $key => $schoolposition) {
            $coursename = '';
            $row = array();
            $id = $schoolposition->id;
            $row[] = $schoolposition->name;
            $row[] = $schoolposition->shortname;

            $createdby = $DB->get_record('user', array('id' => $schoolposition->usercreated));
            $createddate = customdateformat('DATE_WITHOUT_TIME', $schoolposition->timecreated);
            $row[] = fullname($createdby);
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:schoolpositionedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot.'/local/modality/school_positions.php', array('id' => $id));
                $actionicons = html_writer::link($actionurl,
                                                html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                                                'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:schoolpositiondelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot.'/local/modality/school_positions.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table = new html_table();
        $tableheader = array(get_string('schoolpositionname', 'local_modality'),
                            get_string('schoolpositioncode', 'local_modality'),
                            get_string('createdby', 'local_modality'),
                            get_string('createddate', 'local_modality')
                            );

        if (has_capability('local/modality:schoolpositionedit', $systemcontext) ||
            has_capability('local/modality:schoolpositiondelete', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table->head = $tableheader;

        $table->size = array('30%', '20%', '25%', '25');
        $table->align = array('left', 'center', 'center', 'center');
        $table->width = '100%';
        $table->data = $data;
        $table->id = 'school_positions-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#school_positions-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_castes() {
    global $PAGE, $CFG, $OUTPUT;

    $systemcontext = context_system::instance();
    $castes = get_castes();
    $output = '';
    $data = array();
    if (!empty($castes)) {
        foreach ($castes as $key => $caste) {
            $row = array();
            $id = $caste->id;
            $row[] = $caste->name;
            $createddate = customdateformat('DATE_WITHOUT_TIME', $caste->timecreated);
            $row[] = $caste->createdby;
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:casteedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot . '/local/modality/castes.php', array('id' => $id));

                $actionicons .= html_writer::link($actionurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                        'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:castedelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot . '/local/modality/castes.php', array('id' => $id, 'delete' => 1));

                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table = new html_table();
        $tableheader = array(get_string('castename', 'local_modality'),
            get_string('createdby', 'local_modality'),
            get_string('createddate', 'local_modality')
        );

        if (has_capability('local/modality:castedelete', $systemcontext) ||
            has_capability('local/modality:casteedit', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table->head = $tableheader;
        $table->data = $data;
        $table->id = 'castes-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#castes-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_grades() {
    global $PAGE, $CFG, $OUTPUT;

    $systemcontext = context_system::instance();
    $grades = get_grades();
    $output = '';
    $data = array();
    if (!empty($grades)) {
        foreach ($grades as $key => $grade) {
            $row = array();
            $id = $grade->id;
            $row[] = $grade->name;
            $createddate = customdateformat('DATE_WITHOUT_TIME', $grade->timecreated);
            $row[] = $grade->createdby;
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:gradeedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot . '/local/modality/grades.php', array('id' => $id));

                $actionicons .= html_writer::link($actionurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                        'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:gradedelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot . '/local/modality/grades.php', array('id' => $id, 'delete' => 1));

                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table = new html_table();
        $tableheader = array(get_string('gradename', 'local_modality'),
            get_string('createdby', 'local_modality'),
            get_string('createddate', 'local_modality')
        );

        if (has_capability('local/modality:gradeedit', $systemcontext) ||
            has_capability('local/modality:gradedelete', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table->head = $tableheader;
        $table->data = $data;
        $table->id = 'grades-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#grades-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_posts() {
    global $PAGE, $CFG, $OUTPUT;

    $systemcontext = context_system::instance();
    $posts = get_posts();
    $output = '';
    $data = array();
    if (!empty($posts)) {
        foreach ($posts as $key => $post) {
            $row = array();
            $id = $post->id;
            $row[] = $post->name;
            $createddate = customdateformat('DATE_WITHOUT_TIME', $post->timecreated);
            $row[] = $post->createdby;
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:postedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot . '/local/modality/posts.php', array('id' => $id));

                $actionicons .= html_writer::link($actionurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                        'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:postdelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot . '/local/modality/posts.php', array('id' => $id, 'delete' => 1));

                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table = new html_table();
        $tableheader = array(get_string('postname', 'local_modality'),
            get_string('createdby', 'local_modality'),
            get_string('createddate', 'local_modality')
        );

        if (has_capability('local/modality:postdelete', $systemcontext) ||
            has_capability('local/modality:postedit', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table->head = $tableheader;
        $table->data = $data;
        $table->id = 'posts-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#posts-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_financial_categories() {
    global $PAGE, $CFG, $OUTPUT;

    $systemcontext = context_system::instance();
    $financialcategories = get_financial_categories();
    $output = '';
    $data = array();
    if (!empty($financialcategories)) {
        foreach ($financialcategories as $key => $financialcategory) {
            $row = array();
            $id = $financialcategory->id;
            $row[] = $financialcategory->name;
            $row[] = $financialcategory->code;
            $createddate = customdateformat('DATE_WITHOUT_TIME', $financialcategory->timecreated);
            $row[] = $financialcategory->createdby;
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:financialcategoryedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot . '/local/modality/financial_categories.php', array('id' => $id));

                $actionicons .= html_writer::link($actionurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                        'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:financialcategorydelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot . '/local/modality/financial_categories.php', array('id' => $id, 'delete' => 1));

                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table = new html_table();
        $tableheader = array(
            get_string('financialcategoryname', 'local_modality'),
            get_string('financialcode', 'local_modality'),
            get_string('createdby', 'local_modality'),
            get_string('createddate', 'local_modality')
        );

        if (has_capability('local/modality:financialcategoryedit', $systemcontext) ||
            has_capability('local/modality:financialcategorydelete', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table->head = $tableheader;
        $table->data = $data;
        $table->id = 'financial-categories-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#financial-categories-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}

function render_financial_details() {
    global $PAGE, $CFG, $OUTPUT;

    $systemcontext = context_system::instance();
    $financialdetails = get_financial_details();
    $output = '';
    $data = array();
    if (!empty($financialdetails)) {
        foreach ($financialdetails as $key => $financialcategorydetail) {
            $row = array();
            $id = $financialcategorydetail->id;
            $row[] = $financialcategorydetail->name;
            $row[] = $financialcategorydetail->group_name;
            $row[] = $financialcategorydetail->lunch_type;
            $row[] = $financialcategorydetail->dependenton;
            $row[] = $financialcategorydetail->fromvalue;
            $row[] = $financialcategorydetail->tovalue;
            $row[] = $financialcategorydetail->value;
            $createddate = customdateformat('DATE_WITHOUT_TIME', $financialcategorydetail->timecreated);
            $row[] = $financialcategorydetail->createdby;
            $row[] = $createddate;
            $actionicons = '';
            if (has_capability('local/modality:financialcategorydetailedit', $systemcontext)) {
                $actionurl = new moodle_url($CFG->wwwroot . '/local/modality/financial_details.php', array('id' => $id));

                $actionicons = html_writer::link($actionurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'),
                        'title' => 'Edit', 'class' => 'iconsmall')));
            }

            if (has_capability('local/modality:financialcategorydetaildelete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot . '/local/modality/financial_details.php', array('id' => $id, 'delete' => 1));

                $actionicons .= html_writer::link($deleteurl,
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete', 'class' => 'iconsmall', 'width' => '16', 'height' => '16')));
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }

        $table = new html_table();
        $tableheader = array(
            get_string('financialcategories', 'local_modality'),
            get_string('financialgradename', 'local_modality'),
            get_string('financiallunchtype', 'local_modality'),
            get_string('financialdependenton', 'local_modality'),
            get_string('financialfromvalue', 'local_modality'),
            get_string('financialtovalue', 'local_modality'),
            get_string('financialvalue', 'local_modality'),
            get_string('createdby', 'local_modality'),
            get_string('createddate', 'local_modality'),
        );

        if (has_capability('local/modality:add', $PAGE->context) ||
            has_capability('local/modality:financialcategorydetailedit', $systemcontext) ||
            has_capability('local/modality:financialcategorydetaildelete', $systemcontext)
        ) {
            $tableheader[] = get_string('action', 'local_modality');
        }

        $table->head = $tableheader;
        $table->data = $data;
        $table->id = 'financial-categories-list';
        $out = html_writer::table($table);
        $output .= '<div class="table-responsive">'.$out.'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#financial-categories-list').dataTable({
                                    'bSort': false,
                                });
                            } );
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('nodataavailable', 'local_modality') . '</div>';
    }

    return $output;
}
