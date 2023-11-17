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
 * MOODLE VERSION INFORMATION
 *
 * This file defines the current version of the core Moodle code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_training_calendar\form\filters_form as filters_form;
require_once("../../config.php");

require_login();

global $CFG, $USER, $PAGE, $OUTPUT;

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$url = new moodle_url('/local/training_calendar/index.php', array());

$context = context_system::instance();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

$PAGE->set_title(get_string('annualtrainingcalendar', 'local_training_calendar'));
$PAGE->set_heading(get_string('annualtrainingcalendar', 'local_training_calendar'));
$PAGE->requires->jquery();
$PAGE->requires->jquery('ui');
$PAGE->requires->css('/local/training_calendar/css/datatable.css', true);

$pagenavurl = new moodle_url('/local/training_calendar/index.php', array('format' => $format));
$PAGE->navbar->add(get_string("annualtrainingcalendar", 'local_training_calendar'));

$output = $PAGE->get_renderer('local_training_calendar');

echo $OUTPUT->header();

if (is_siteadmin() || has_capability('local/training_calendar:manage', $context)) {

        $thisfilters = array('selectview', 'coursetype', 'organiser', 'trainingtimeline', 'trainingyear');
        $enablefilters = true;

        $mform = new filters_form(null, array('filterlist' => $thisfilters));
    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot . '/local/training_calendar/index.php');
    } else {
        $filterdata = $mform->get_data();
        if ($filterdata) {
            $collapse = false;
        } else {
            $collapse = true;
        }
    }
    if (empty($filterdata) && !empty($jsonparam)) {
        $filterdata = json_decode($jsonparam);
        foreach ($thisfilters as $filter) {
            if (empty($filterdata->$filter)) {
                unset($filterdata->$filter);
            }
        }
        $mform->set_data($filterdata);
    }
    if ($filterdata) {
        $collapse = false;
        $show = 'show';
    } else {
        $collapse = true;
        $show = '';
    }

    if ($enablefilters) {
        echo '<a class="btn-link btn-sm" title="Filter" href="javascript:void(0);"
                data-toggle="collapse" data-target="#local_training_calendar-filter_collapse"
                aria-expanded="false" aria-controls="local_training_calendar-filter_collapse">
                <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"> ' . get_string('filters', 'local_training_calendar') . '</i>
            </a>';
        echo '<div class="collapse '.$show.'" id="local_training_calendar-filter_collapse">
            <div id="filters_form" class="card card-body p-2">';
        $mform->display();
        echo '</div>
        </div>';
    }
    $filterparams['submitid'] = 'form#filteringform';
}

echo $output->get_trainingcourses($filterdata, $page, $perpage);
echo $OUTPUT->footer();
