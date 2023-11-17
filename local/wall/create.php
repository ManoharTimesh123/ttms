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
 * The Wall Management
 * @package local_wall
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot . '/local/wall/create_form.php');
require($CFG->dirroot . '/local/wall/locallib.php');

global $CFG, $DB, $USER;

$id = optional_param('id', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();
require_capability('local/wall:add', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/wall/create.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'local_wall'));
$PAGE->set_heading(get_string('pluginname', 'local_wall'));
$PAGE->set_pagelayout('admin');

$userenrolledcourses = enrol_get_all_users_courses($USER->id);

if (!$userenrolledcourses) {
    throw new moodle_exception(get_string('noenrolementincourse', 'local_wall'), '', '', get_string('notautorisedmsg', 'local_wall'));
}

$data = $DB->get_record('local_wall_posts', array('id' => $id));


$url = new moodle_url($CFG->wwwroot . '/local/wall/manage.php', array('id' => 0));
$returnurl = new moodle_url($CFG->wwwroot . '/local/wall/manage.php', array('id' => $id));

$mform = new post_create_form(null, array('data' => $data));

$mform->set_data($data);
if ($mform->is_cancelled()) {
    redirect($url, get_string('cancelledpostmessage', 'local_wall'));

} else if ($data = $mform->get_data()) {
    $postid = add_post($data);
    $attachments = file_get_submitted_draft_itemid('postfile');
    file_save_draft_area_files($attachments, $systemcontext->id, 'local_wall', 'attachment', $postid);

    redirect($url, get_string('addnpostmessage', 'local_wall'), null, 'info');
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('createpost', 'block_wall'));

$mform->display();

echo $OUTPUT->footer();
