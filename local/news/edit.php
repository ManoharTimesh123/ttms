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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot . '/local/news/edit-form.php');
require($CFG->dirroot . '/local/news/locallib.php');

global $CFG, $DB, $USER;

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$changestatus = optional_param('status', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();

if ($id == 0 && !has_capability('local/news:add', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermissions', 'local_news'));
}

if ($id > 0 && !has_capability('local/news:edit', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermissions', 'local_news'));
}

$data = $DB->get_record('local_news', array('id' => $id));

// When the id passed during edit does not exists.
if ($id > 0 && (empty($data) || !isset($data))) {
    throw new moodle_exception('dataerror', 'error', '', null, get_string('recordmanageerrormsg', 'local_news'));
}

// When the required permission do not match while editing the data.
if (!is_siteadmin() &&
    $id > 0 &&
    !has_capability('local/news:manage', $systemcontext) &&
    (has_capability('local/news:manageown', $systemcontext) && $USER->id != $data->createdby)
) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermissions', 'local_news'));
}

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/news/edit.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'local_news'));
$PAGE->set_heading(get_string('pluginname', 'local_news'));
$PAGE->set_pagelayout('admin');

if (!has_capability('local/news:manage', $systemcontext)) {
    $usershcoolhos = check_user_is_hos_and_get_school();
}

$url = new moodle_url($CFG->wwwroot . '/local/news/listing.php', array('id' => 0));
$returnurl = new moodle_url($CFG->wwwroot . '/local/news/listing.php', array('id' => $id));

if ($data) {
    $attachments = file_get_submitted_draft_itemid('newsimage');
    file_prepare_draft_area($attachments, $systemcontext->id, 'local_news', 'attachment', $data->id,
        array('subdirs' => 0, 'maxbytes' => 2097152, 'maxfiles' => 1));

    $data->newsimage = $attachments;
    $data->description = array('text' => $data->description);
    $data->datefrom = $data->datefrom;
    $data->dateto = $data->dateto;
}

$data->schoolid = $usershcoolhos;
$mform = new news_create_form(null, array('data' => $data));

$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url, get_string('cancellednewsmessage', 'local_news'));

} else if ($data = $mform->get_data()) {
    $newsid = add_news($data);
    $attachments = file_get_submitted_draft_itemid('newsimage');
    file_save_draft_area_files($attachments, $systemcontext->id, 'local_news', 'attachment', $newsid);
    if ($id > 0) {
        redirect($url, get_string('updatenewsmessage', 'local_news'));
    }
    redirect($url, get_string('addnewsmessage', 'local_news'));
}

echo $OUTPUT->header();

if (!$delete && !$changestatus) {
    echo $OUTPUT->heading(get_string('createnews', 'local_news'));
}

if ($delete) {
    require_capability('local/news:delete', $systemcontext);

    $deletenews = get_string('deletenews', 'local_news');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot . '/local/news/listing.php',
        array('agree' => 1, 'id' => $id)), get_string('yes'));

    $formcancel = new single_button(new moodle_url($CFG->wwwroot . '/local/news/listing.php',
        array('agree' => 0, 'id' => $id)), get_string('no'));

    echo $OUTPUT->confirm($deletenews, $formcontinue, $formcancel);

} else if ($changestatus) {
    require_capability('local/news:approve', $systemcontext);

    $changestatusnews = get_string('changenewsstatus', 'local_news');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot . '/local/news/listing.php',
        array('status' => 1, 'id' => $id)), get_string('yes'));

    $formcancel = new single_button(new moodle_url($CFG->wwwroot . '/local/news/listing.php',
        array('status' => 0, 'id' => $id)), get_string('no'));

    echo $OUTPUT->confirm($changestatusnews, $formcontinue, $formcancel);

} else {
    $mform->display();
}

echo $OUTPUT->footer();
