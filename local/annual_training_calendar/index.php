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
 * Annual Training Calendar
 * @package    local_annual_training_calendar
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/annual_training_calendar/renderer.php');
require_once($CFG->dirroot . '/local/annual_training_calendar/filter_form.php');
require_once($CFG->dirroot . '/local/annual_training_calendar/locallib.php');

$enablestartdate = optional_param('startdate_enabled', 0, PARAM_INT);
$enablesenddate = optional_param('enddate_enabled', 0, PARAM_INT);

$systemcontext = context_system::instance();
require_login();

require_capability('local/annual_training_calendar:view', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/local/annual_training_calendar/index.php');
$PAGE->set_title(get_string('pluginname', 'local_annual_training_calendar'));
$PAGE->set_heading(get_string('pluginname', 'local_annual_training_calendar'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/annual_training_calendar/js/annual_training_calendar_custom.js', true);

// Get all query parameters.
$queryparams = $_GET;

echo $OUTPUT->header();

$baseurl = new moodle_url($CFG->wwwroot . '/local/annual_training_calendar/index.php');
$data = new stdClass();
$data->startdate_enabled = $enablestartdate;
$data->enddate_enabled = $enablestartdate;

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = optional_param('page', 0, PARAM_INT);
}

$filterform = new annual_training_calendar_filter_form('', array('data' => $data), 'get');

$filterform->display();

$itemperpage = $CFG->itemperpage;

if ($filterform->is_cancelled()) {
    redirect($baseurl);
} else if ($filterformdata = $filterform->get_data()) {
    $totalitems = count(get_annual_training_calendar($filterformdata));
    $filterformdata->page = $page;
    echo render_annual_training_calendar($filterformdata);
    $filterform->set_data($filterformdata);
} else {
    $filterform = new stdClass();
    $totalitems = count(get_annual_training_calendar($filterform));
    $filterform->page = $page;
    echo render_annual_training_calendar($filterform);
}

$paginationurl = $baseurl. '?page=' . $page;

echo $OUTPUT->paging_bar($totalitems, $page, $itemperpage, $paginationurl. '&'. http_build_query($queryparams));

echo $OUTPUT->footer();
