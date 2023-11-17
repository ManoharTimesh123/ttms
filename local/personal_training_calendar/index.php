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
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/local/training_transcript/locallib.php');
require_once('filter_form.php');
require_once('renderer.php');
require_once('locallib.php');

global $CFG, $DB, $USER;

$enablestartdate = optional_param('startdate_enabled', 0, PARAM_INT);
$enablesenddate = optional_param('enddate_enabled', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();
require_capability('local/personal_training_calendar:view', $systemcontext);

$PAGE->set_context($systemcontext);

$PAGE->set_url('/local/personal_training_calendar/index.php');
$PAGE->set_title(get_string('pluginname', 'local_personal_training_calendar'));
$PAGE->set_heading(get_string('pluginname', 'local_personal_training_calendar'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/personal_training_calendar/js/personal_training_calendar_custom.js', true);

// Get all query parameters.
$queryparams = $_GET;

echo $OUTPUT->header();

$data = new stdClass();
$data->startdate_enabled = $enablestartdate;
$data->enddate_enabled = $enablestartdate;
$mform = new local_personal_training_calendar_filter_form('', array('data' => $data), 'get');

$baseurl = new moodle_url($CFG->wwwroot . '/local/personal_training_calendar/index.php');

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = optional_param('page', 0, PARAM_INT);
}

$mform = new local_personal_training_calendar_filter_form('', '', 'get');

$mform->display();
// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    $url = new moodle_url("/local/personal_training_calendar/");
    redirect($url);
    // Handle form cancel operation, if cancel button is present on form.
} else if ($fromform = $mform->get_data()) {
    $totalitems = count(get_personal_training_calendar($fromform, $USER->id));
    $fromform->page = $page;

    echo render_personal_training_calender_table($fromform, $USER->id);
    $mform->set_data($fromform);
    // In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
    $fromform = new stdClass();
    $totalitems = count(get_personal_training_calendar($fromform, $USER->id));
    $fromform->page = $page;
    echo render_personal_training_calender_table($fromform, $USER->id);
}

$itemperpage = $CFG->itemperpage;
$paginationurl = $baseurl. '?page=' . $page;

echo $OUTPUT->paging_bar($totalitems, $page, $itemperpage, $paginationurl. '&'. http_build_query($queryparams));

echo $OUTPUT->footer();
