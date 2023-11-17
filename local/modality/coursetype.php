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
 * @author  Nadia Farheen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot.'/local/modality/coursetype_form.php');
require($CFG->dirroot.'/local/modality/locallib.php');

global $CFG, $DB;

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);

$systemcontext = context_system::instance();
require_login();

require_capability('local/modality:add', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/modality/coursetype.php', array('id' => $id));
$PAGE->set_title(get_string('createcoursetype', 'local_modality'));
$PAGE->set_heading(get_string('createcoursetype', 'local_modality'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$data = $DB->get_record('local_coursetype', array('id' => $id));

$PAGE->navbar->add(get_string('administrationsite'), new moodle_url($CFG->wwwroot.'/admin/search.php'), navigation_node::TYPE_SETTING);
$PAGE->navbar->add(get_string('coursetype', 'local_modality'), new moodle_url('/local/modality/show_coursetype.php'), navigation_node::TYPE_SETTING);

if ($data) {
    $PAGE->navbar->add($data->name, new moodle_url('/local/modality/coursetype.php', array('id' => $data->id)));
}

$url = new moodle_url($CFG->wwwroot.'/local/modality/show_coursetype.php', array('id' => 0));
$returnurl = new moodle_url($CFG->wwwroot.'/local/modality/show_coursetype.php', array('id' => $id));

$mform = new coursetype_form(null, array('id' => $id, 'data' => $data));
$data->description = array('text' => $data->description, 'format' => $data->descriptionformat);


$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url);

} else if ($data = $mform->get_data()) {

    $ref = add_coursetype($data);
    redirect($url);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('createcoursetype', 'local_modality'), 2);

if ($delete) {
    // Delete the modality with confirmation.
    $deletecoursetype = get_string('deletecoursetype', 'local_modality');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot.'/local/modality/show_coursetype.php',
                                        array('agree' => 1, 'id' => $id)), get_string('yes'));
    $formcancel = new single_button(new moodle_url($CFG->wwwroot.'/local/modality/show_coursetype.php',
                                        array('agree' => 0, 'id' => $id)), get_string('no'));
    echo $OUTPUT->confirm($deletecoursetype, $formcontinue, $formcancel);
} else {
    $mform->display();
}

echo $OUTPUT->footer();
