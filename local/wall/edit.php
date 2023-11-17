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
 * The Wall Post
 * @package    local_wall
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot . '/local/wall/edit_form.php');
require($CFG->dirroot . '/local/wall/locallib.php');

global $CFG, $DB, $USER;

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();
require_capability('local/wall:edit', $systemcontext);

$data = $DB->get_record('local_wall_posts', array('id' => $id, 'deleted' => 0));

// When the id passed during edit does not exists.
if ($id > 0 && (empty($data) || !isset($data))) {
    throw new moodle_exception('dataerror', 'error', '', null, get_string('manageissue', 'local_wall'));
}

// When the required permission do not match while editing the data.
if (!is_siteadmin() &&
    $id > 0 &&
    !has_capability('local/wall:manage', $systemcontext) &&
    (has_capability('local/wall:manageown', $systemcontext) && $USER->id != $data->createdby)
) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermissions', 'local_wall'));
}

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/wall/edit.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'local_wall'));
$PAGE->set_heading(get_string('pluginname', 'local_wall'));
$PAGE->set_pagelayout('admin');

$url = new moodle_url($CFG->wwwroot . '/local/wall/manage.php', array('id' => 0));
$returnurl = new moodle_url($CFG->wwwroot . '/local/wall/manage.php', array('id' => $id));
$maxbytes = get_user_max_upload_file_size($systemcontext, $CFG->maxbytes);

$attachments = file_get_submitted_draft_itemid('postfile');
file_prepare_draft_area($attachments, $systemcontext->id, 'local_wall', 'attachment', $data->id,
    array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));

$data->attachments = $attachments;
$mform = new post_edit_form(null, array('id' => $id, 'data' => $data));
$data->description = array('text' => $data->description);
$data->postfile = $attachments;

$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url, get_string('cancelledpostmessage', 'local_wall'));

} else if ($data = $mform->get_data()) {
    $postid = add_post($data);
    $attachments = file_get_submitted_draft_itemid('postfile');

    file_save_draft_area_files($attachments, $systemcontext->id, 'local_wall', 'attachment', $postid, $options);
    redirect($url, get_string('updatepostmessage', 'local_wall'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editpost', 'local_wall'), 2);

$mform->display();

echo $OUTPUT->footer();
