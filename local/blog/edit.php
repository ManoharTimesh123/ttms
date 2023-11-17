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
 * The blog Management
 * @package    local_blog
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot . '/local/blog/edit-form.php');
require($CFG->dirroot . '/local/blog/locallib.php');

global $CFG, $DB, $USER;

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$changestatus = optional_param('status', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();

if ($id == 0 && !has_capability('local/blog:add', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermissions', 'local_blog'));
}

if ($id > 0 && !has_capability('local/blog:edit', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermissions', 'local_blog'));
}

$data = $DB->get_record('local_blogs', array('id' => $id));

// When the id passed during edit does not exists.
if ($id > 0 && (empty($data) || !isset($data))) {
    throw new moodle_exception('dataerror', 'error', '', null, get_string('managerecorderrormsg', 'local_blog'));
}

// When the required permission do not match while editing the data.
if (!is_siteadmin() &&
    $id > 0 &&
    !has_capability('local/blog:manage', $systemcontext) &&
    (has_capability('local/blog:manageown', $systemcontext) && $USER->id != $data->usercreated)
) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermissions', 'local_blog'));
}

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/blog/edit.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'local_blog'));
$PAGE->set_heading(get_string('pluginname', 'local_blog'));
$PAGE->set_pagelayout('admin');

$url = new moodle_url($CFG->wwwroot . '/local/blog/listing.php', array('id' => 0));
$returnurl = new moodle_url($CFG->wwwroot . '/local/blog/listing.php', array('id' => $id));
$maxbytes = get_user_max_upload_file_size($systemcontext, $CFG->maxbytes);

if ($data) {
    $attachments = file_get_submitted_draft_itemid('attachments');
    file_prepare_draft_area($attachments, $systemcontext->id, 'local_blog', 'attachment', $data->id,
        array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));

    $data->attachments = $attachments;
    $data->description = array('text' => $data->description);
}

$mform = new blog_create_form(null, array('data' => $data));
$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url, get_string('cancelledpostmessage', 'local_blog'));

} else if ($data = $mform->get_data()) {
    $postid = add_post($data);
    $attachments = file_get_submitted_draft_itemid('attachments');
    file_save_draft_area_files($attachments, $systemcontext->id, 'local_blog', 'attachment', $postid);
    redirect($url, get_string('createpostmessage', 'local_blog'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('createpost', 'local_blog'), 2);

if ($delete) {
    require_capability('local/blog:delete', $systemcontext);

    $deleteblog = get_string('deletepost', 'local_blog');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot . '/local/blog/listing.php',
        array('agree' => 1, 'id' => $id)), get_string('yes'));
    $formcancel = new single_button(new moodle_url($CFG->wwwroot . '/local/blog/listing.php',
        array('agree' => 0, 'id' => $id)), get_string('no'));
    echo $OUTPUT->confirm($deleteblog, $formcontinue, $formcancel);

} else if ($changestatus) {
    require_capability('local/blog:approve', $systemcontext);

    $changestatusblog = get_string('changepoststatus', 'local_blog');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot . '/local/blog/listing.php',
        array('status' => 1, 'id' => $id)), get_string('yes'));
    $formcancel = new single_button(new moodle_url($CFG->wwwroot . '/local/blog/listing.php',
        array('status' => 0, 'id' => $id)), get_string('no'));
    echo $OUTPUT->confirm($changestatusblog, $formcontinue, $formcancel);

} else {
    $mform->display();
}

echo $OUTPUT->footer();
