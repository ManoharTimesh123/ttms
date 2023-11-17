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
 * The Wall Post Management
 * @package local_wall
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot . '/local/wall/create_form.php');
require($CFG->dirroot . '/local/wall/locallib.php');

global $CFG, $DB, $USER;

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$changestatus = optional_param('status', 0, PARAM_INT);

$systemcontext = context_system::instance();
require_login();

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/wall/action.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'local_wall'));
$PAGE->set_heading(get_string('pluginname', 'local_wall'));
$PAGE->set_pagelayout('admin');

$data = $DB->get_record('local_wall_posts', array('id' => $id));


$url = new moodle_url($CFG->wwwroot . '/local/wall/manage.php', array('id' => 0));
$returnurl = new moodle_url($CFG->wwwroot . '/local/wall/manage.php', array('id' => $id));


echo $OUTPUT->header();

if ($delete) {
    require_capability('local/wall:delete', $systemcontext);

    $deletenews = get_string('deletepost', 'local_wall');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot . '/local/wall/manage.php',
        array('agree' => 1, 'id' => $id)), get_string('yes'));

    $formcancel = new single_button(new moodle_url($CFG->wwwroot . '/local/wall/manage.php',
        array('agree' => 0, 'id' => $id)), get_string('no'));

    echo $OUTPUT->confirm($deletenews, $formcontinue, $formcancel);

}
if ($changestatus) {
    require_capability('local/wall:approve', $systemcontext);

    $changestatusnews = get_string('changepoststatus', 'local_wall');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot . '/local/wall/manage.php',
        array('status' => 1, 'id' => $id)), get_string('yes'));

    $formcancel = new single_button(new moodle_url($CFG->wwwroot . '/local/wall/manage.php',
        array('status' => 0, 'id' => $id)), get_string('no'));

    echo $OUTPUT->confirm($changestatusnews, $formcontinue, $formcancel);

}

echo $OUTPUT->footer();
