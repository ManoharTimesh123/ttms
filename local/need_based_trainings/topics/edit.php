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
 * The News Management
 * @package    local_news
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require($CFG->dirroot . '/local/need_based_trainings/topics/edit_form.php');
require($CFG->dirroot . '/local/need_based_trainings/locallib.php');

global $CFG, $DB, $USER;

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();

if (!has_capability('local/need_based_trainings:topicadd', $systemcontext) ||
    !has_capability('local/need_based_trainings:topicedit', $systemcontext)
) {
    throw new moodle_exception('nopermission', 'error', '', null, 'You do not have permission to access this page.');
}

$data = $DB->get_record('local_nbt_topics', array('id' => $id));

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/need_based_trainings/topics/edit.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'local_need_based_trainings'));
$PAGE->set_heading(get_string('pluginname', 'local_need_based_trainings'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/need_based_trainings/js/need_based_training_custom.js', true);

$url = new moodle_url($CFG->wwwroot . '/local/need_based_trainings/topics/listing.php', array('id' => 0));
$returnurl = new moodle_url($CFG->wwwroot . '/local/need_based_trainings/topics/listing.php', array('id' => $id));

$mform = new topic_create_form(null, array('id' => $id, 'data' => $data));

$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url, get_string('cancelledtopicmessage', 'local_need_based_trainings'));

} else if ($data = $mform->get_data()) {
    add_topic($data);
    if ($id > 0) {
        redirect($url, get_string('updatetopicmessage', 'local_need_based_trainings'));
    }
    redirect($url, get_string('addtopicmessage', 'local_need_based_trainings'));
}

echo $OUTPUT->header();

if (!$delete) {
    echo $OUTPUT->heading(get_string('createtopic', 'local_need_based_trainings'));
}

if ($delete) {
    require_capability('local/need_based_trainings:topicdelete', $systemcontext);

    $deletetopic = get_string('deletetopic', 'local_need_based_trainings');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot . '/local/need_based_trainings/topics/listing.php',
        array('agree' => 1, 'id' => $id)), get_string('yes'));

    $formcancel = new single_button(new moodle_url($CFG->wwwroot . '/local/need_based_trainings/topics/listing.php',
        array('agree' => 0, 'id' => $id)), get_string('no'));

    echo $OUTPUT->confirm($deletetopic, $formcontinue, $formcancel);

} else {
    $mform->display();
}

echo $OUTPUT->footer();

