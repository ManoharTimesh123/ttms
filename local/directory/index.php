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
require_once('lib.php');
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$query = optional_param('search', null, PARAM_RAW);

global $CFG, $DB, $OUTPUT, $PAGE;
require_once($CFG->dirroot.'/user/lib.php');
$PAGE->requires->jquery();
$PAGE->requires->js('/local/directory/js/directory.js');

require_login();

$systemcontext = context_system::instance();
require_capability('local/directory:view', $systemcontext);

$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_directory'));
$PAGE->set_heading('Teachers Directory');
echo $OUTPUT->header();

$offset = ($page) * $perpage;
$publicfields = $DB->get_records('local_user_public_fields', array('visible' => 1));

if ($query) {
    $sqlusercount = "SELECT count(id) as count FROM {user} WHERE deleted = 0 and confirmed = 1 and timecreated != 0 and (username LIKE '%$query%'
    OR firstname LIKE '%$query%' OR lastname LIKE '%$query%' OR email LIKE '%$query%')";
    $sql = "SELECT * FROM {user} WHERE deleted = 0 and confirmed = 1 and timecreated != 0 and (username LIKE '%$query%' OR firstname LIKE '%$query%'
    OR lastname LIKE '%$query%' OR email LIKE '%$query%') LIMIT $perpage OFFSET $offset";
    $userscount = $DB->get_record_sql($sqlusercount);
    $users = $DB->get_records_sql($sql);
} else {
    $userscount = $DB->get_record_sql("SELECT count(id) as count FROM {user} WHERE deleted = 0 and confirmed = 1 and timecreated != 0");
    $users = $DB->get_records_sql("SELECT * FROM {user} WHERE deleted = 0 and confirmed = 1 and timecreated != 0 LIMIT $perpage OFFSET $offset");
}

$content = [];
$headers = [];

foreach ($users as $usersvalue) {

    $localfields = $DB->get_record('local_user_details', array('userid' => $usersvalue->id));

    $userpublicfileds = get_user_public_fields($usersvalue->id);

    $content[] = array('data' => $userpublicfileds[0]);

    if (empty($headers)) {
        $headers = $userpublicfileds[1]; // This has the header.
    }
}

foreach ($headers as $index => $header) {
    $headers[$index] = '<th scope="col">' . get_string(trim($header), 'local_directory') . '</th>';
}

echo $OUTPUT->render_from_template('local_directory/directory',
    [
        'content' => $content,
        'headers' => implode($headers),
    ]
);

$baseurl = new moodle_url('/local/directory');
$count = $userscount->count;

echo $OUTPUT->paging_bar($count, $page, $perpage, $baseurl);
echo $OUTPUT->footer();
