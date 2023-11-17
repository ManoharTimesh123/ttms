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
 * Bulk user registration script from a comma separated file
 *
 * @package    local
 * @subpackage self registration
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('user_profile_update.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once('lib.php');
require_once($CFG->dirroot.'/local/profile_management/locallib.php');

require_login();

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);

$PAGE->set_url($CFG->wwwroot . '/local/profile_management/index.php');
$PAGE->set_title(get_string('pluginname', 'local_profile_management'));
$PAGE->set_heading(get_string('pluginname', 'local_profile_management'));
$PAGE->set_pagelayout('admin');

$data = get_user_detail($USER->id);

$mform = new local_user_profile_update(null, array('data' => $data));

$PAGE->set_heading(get_string('updateprofilefields', 'local_profile_management'));

$mform->set_data($data);
// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect("#");
} else if ($fromform = $mform->get_data()) {

    $subject = implode(',', $fromform->subject);
    $position = implode(',', $fromform->position);

    if (!$DB->record_exists_sql("SELECT * FROM {local_user_details} WHERE userid = $USER->id")) {

        $userdetail = new stdClass();
        $userdetail->userid = $USER->id;
        $userdetail->position = $position;
        $userdetail->subject = $subject;
        $userdetail->timecreated = time();
        $DB->insert_record('local_user_details', $userdetail);
        redirect($CFG->wwwroot.'/my');

    } else {

        if (!$DB->record_exists_sql("SELECT * FROM {local_user_details} WHERE subject = '$subject' and userid = $USER->id")) {
            $DB->execute("UPDATE {local_user_details} SET subject = '$subject' WHERE userid = $USER->id");
        }
        if (!$DB->record_exists_sql("SELECT * FROM {local_user_details} WHERE position = '$position' and userid=$USER->id")) {
            $DB->execute("UPDATE {local_user_details} SET position = '$position' WHERE userid = $USER->id");
        }
        redirect($CFG->wwwroot . '/my');
    }
    // In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
    echo $OUTPUT->header();
    // Displays the form.
    $mform->display();
    echo $OUTPUT->footer();
}
