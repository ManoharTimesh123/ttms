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
 * This main view page for a questionnaire.
 *
 * @package mod_questionnaire
 * @copyright  2016 Mike Churchward (mike.churchward@poetgroup.org)
 * @author     Mike Churchward
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once('../../config.php');
require_once($CFG->dirroot.'/local/modality/user_overall_ratings_form.php');
require_once($CFG->libdir . '/formslib.php');
require_once('locallib.php');
require_login();
global $DB , $OUTPUT , $CFG , $COURSE, $USER;

$context = context_system::instance();
$PAGE->set_context($context);
$pagetitle = get_string('rateweightage', 'local_modality');
$PAGE->set_title($pagetitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/modality/user_overall_ratings.php');
$pagenav = get_string('navigation', 'local_modality');
$previewnode = $PAGE->navbar->add($pagenav , url);
echo $OUTPUT->header();

$mform = new user_overall_ratings_form();

if ($mform->is_cancelled()) {
    $url = new moodle_url('/');
    redirect($url);
} else if ($data = $mform->get_data()) {
    $userid = $USER->id;
    $param = array(
        'provided_by_user_roleid' => $data->provided_by_user_roleid,
        'received_by_user_roleid' => $data->received_by_user_roleid
    );
    $existingdata = $DB->get_record('user_ratings_weightage' , $param);
    $rateweightagedata = new stdClass();
    if (empty($existingdata)) {
        $rateweightagedata->provided_by_user_roleid = $data->provided_by_user_roleid;
        $rateweightagedata->received_by_user_roleid = $data->received_by_user_roleid;
        $rateweightagedata->rate_weightage = $data->rate_weightage;
        $rateweightagedata->usercreated = $userid;
        $rateweightagedata->usermodified = $userid;
        $rateweightagedata->timecreated = time();
        $rateweightagedata->timemodified = time();
        $insertedid = $DB->insert_record('user_ratings_weightage' , $rateweightagedata);
        if (!empty($insertedid)) {
            $sucssmsg = get_string('insertmessage' , 'local_modality');
            echo $OUTPUT->notification($sucssmsg , 'notifysuccess');
        }

    } else {
        $rateweightagedata->id = $existingdata->id;
        $rateweightagedata->rate_weightage = $data->rate_weightage;
        $rateweightagedata->usermodified = $userid;
        $rateweightagedata->timemodified = time();
        $DB->update_record('user_ratings_weightage' , $rateweightagedata);
        $sucssmsg = get_string('updatemsg' , 'local_modality');
        echo $OUTPUT->notification($sucssmsg , 'notifysuccess');

    }
}

$headingdata = "";
$headingdata .= html_writer::start_tag('div' , array('class' => ' row '));
$headingdata .= html_writer::start_tag('div' , array('class' => 'col-md-6'));
$headingdata .= html_writer::start_tag('h4');
$headingdata .= get_string('rateweightage' , 'local_modality');
$headingdata .= html_writer::end_tag('h4');
$headingdata .= html_writer::end_tag('div');
$headingdata .= html_writer::start_tag('div' , array('class' => 'col-md-6'));
$headingdata .= html_writer::start_tag('a' , array('href' => '/admin/search.php#linklocal_modality',
    'class' => 'btn btn-primary' , 'title' => get_string('backviewpagetitel' , 'local_modality')));
$headingdata .= html_writer::start_tag('i' ,  array('class' => 'fa fa-home'));
$headingdata .= html_writer::end_tag('i');
$headingdata .= get_string('backviewpage' , 'local_modality');
$headingdata .= html_writer::end_tag('a');
$headingdata .= html_writer::end_tag('div');
$headingdata .= html_writer::end_tag('div');

 echo $headingdata;
echo  '<hr>';
$mform->display();
echo  '<hr>';

$availblelists = $DB->get_records('user_ratings_weightage');
$tabledisplay = '';
if (!empty($availblelists)) {
    $tabledisplay .= html_writer::start_tag('div',  array('class' => 'table-responsive'));
    $table = new html_table();
    $table->head = (array) get_strings(array('sno' , 'rateprovidedby' , 'ratereceivedby' , 'rateweightage') , 'local_modality');
    $table->id = 'example1';
    $i = 1;
    foreach ($availblelists as $availblelist) {
        $provideuserroleid = $availblelist->provided_by_user_roleid;
        $provideuserrole = get_users_role($provideuserroleid);
        $receiveuserroleid = $availblelist->received_by_user_roleid;
        $receiveuserrole = get_users_role($receiveuserroleid);
        // Data inserted into table.
        $table->data[] = array($i++ , $provideuserrole , $receiveuserrole , $availblelist->rate_weightage);
    }
    $tabledisplay .= html_writer::table($table);
    $tabledisplay .= html_writer::end_tag('div');
    echo $tabledisplay;
} else {
    $sucssmsg = get_string('datanotavailable' , 'local_modality');
    echo $OUTPUT->notification($sucssmsg , 'notifywarning');
}
echo $OUTPUT->footer();





