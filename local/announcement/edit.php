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
 * Announcement
 * @package local_announcement
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot . '/local/announcement/edit-form.php');
require($CFG->dirroot . '/local/announcement/locallib.php');

global $CFG, $DB, $USER, $PAGE;

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);

$systemcontext = context_system::instance();

if ($id == 0 && !has_capability('local/announcement:add', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermission', 'local_announcement'));
}

if ($id > 0 && !has_capability('local/announcement:edit', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermission', 'local_announcement'));
}

require_login();

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/announcement/edit.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'local_announcement'));
$PAGE->set_heading(get_string('pluginname', 'local_announcement'));
$PAGE->set_pagelayout('admin');

$data = $DB->get_record('local_announcements', array('id' => $id));

$url = new moodle_url($CFG->wwwroot.'/local/announcement/listing.php', array('id' => 0));
$returnurl = new moodle_url($CFG->wwwroot.'/local/announcement/listing.php', array('id' => $id));

if ($data) {
    $attachments = file_get_submitted_draft_itemid('announcementimage');
    file_prepare_draft_area($attachments, $systemcontext->id, 'local_announcement', 'attachment', $data->id,
        array('subdirs' => 0, 'maxbytes' => 2097152, 'maxfiles' => 1));

    $data->announcementimage = $attachments;
    $data->description = array('text' => $data->description);
    $data->datefrom = $data->startdate;
    $data->dateto = $data->enddate;
}

$mform = new announcement_create_form(null, array('data' => $data));

$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url, get_string('cancelannouncement', 'local_announcement'));

} else if ($data = $mform->get_data()) {
    $announcementid = add_announcement($data);
    $attachments = file_get_submitted_draft_itemid('announcementimage');
    file_save_draft_area_files($attachments, $systemcontext->id, 'local_announcement', 'attachment', $announcementid);
    if ($id > 0) {
        redirect($url, get_string('updateannouncement', 'local_announcement'));
    }
    redirect($url, get_string('addannouncement', 'local_announcement'));
}

echo $OUTPUT->header();

if (!$delete) {
    echo $OUTPUT->heading(get_string('createannouncement', 'local_announcement'));
}

if ($delete) {
    require_capability('local/announcement:delete', $systemcontext);

    $deletenews = get_string('deleteannouncement', 'local_announcement');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot.'/local/announcement/listing.php',
        array('agree' => 1, 'id' => $id)), get_string('yes'));

    $formcancel = new single_button(new moodle_url($CFG->wwwroot.'/local/announcement/listing.php',
        array('agree' => 0, 'id' => $id)), get_string('no'));

    echo $OUTPUT->confirm($deletenews, $formcontinue, $formcancel);

} else {
    $mform->display();
}

echo $OUTPUT->footer();
