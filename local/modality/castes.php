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
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot . '/local/modality/castes_form.php');
require($CFG->dirroot . '/local/modality/locallib.php');

global $CFG, $DB;

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();

if ($id == 0 && !has_capability('local/modality:casteadd', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermission', 'local_modality'));
}

if ($id > 0 && !has_capability('local/modality:casteedit', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermission', 'local_modality'));
}

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/modality/castes.php', array('id' => $id));
$PAGE->set_title(get_string('createcaste', 'local_modality'));
$PAGE->set_heading(get_string('createcaste', 'local_modality'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();

$data = get_caste_by_id($id);

$PAGE->navbar->add(get_string('castes', 'local_modality'), new moodle_url('/local/modality/show_castes.php'),
    navigation_node::TYPE_SETTING);
if ($data) {
    $PAGE->navbar->add($data->name, new moodle_url('/local/modality/castes.php', array('id' => $data->id)));
} else {
    $PAGE->navbar->add(get_string('createcaste', 'local_modality'));
}
$url = new moodle_url($CFG->wwwroot.'/local/modality/show_castes.php', array('id' => 0));

$returnurl = new moodle_url($CFG->wwwroot.'/local/modality/show_castes.php', array('id' => $id));

$mform = new castes_form(null, array('id' => $id, 'data' => $data));


$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url);
} else if ($data = $mform->get_data()) {
    $ref = add_or_update_caste($data);
    redirect($url);
}

// Check if the caste is associated with something.
if ($id && $delete) {
    check_castes($id);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('createcaste', 'local_modality'), 2);

if ($delete) {
    require_capability('local/modality:castedelete', $systemcontext);
    // Delete the caste with confirmation.
    $deletecaste = get_string('deletecaste', 'local_modality');

    $formcontinue = new single_button(
        new moodle_url($CFG->wwwroot.'/local/modality/show_castes.php', array('agree' => 1, 'id' => $id)),
        get_string('yes')
    );

    $formcancel = new single_button(
        new moodle_url($CFG->wwwroot.'/local/modality/show_castes.php',
        array('agree' => 0, 'id' => $id)), get_string('no')
    );

    echo $OUTPUT->confirm($deletecaste, $formcontinue, $formcancel);
} else {
    $mform->display();
}

echo $OUTPUT->footer();
