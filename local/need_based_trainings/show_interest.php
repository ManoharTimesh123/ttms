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
 * Need Based Trainings
 *
 * @package    local_need_based_trainings
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot . '/local/need_based_trainings/locallib.php');
require_once($CFG->dirroot . '/local/need_based_trainings/show_interest_form.php');
require($CFG->dirroot . '/local/need_based_trainings/renderer.php');

$course = optional_param('course', 0, PARAM_INT);
$topic = optional_param('topic', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_capability('local/need_based_trainings:view', $systemcontext);

$PAGE->set_title(get_string('pluginname', 'local_need_based_trainings'));
$PAGE->set_heading(get_string('pluginname', 'local_need_based_trainings'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/need_based_trainings/js/need_based_training_custom.js', true);

echo $OUTPUT->header();

$data = new stdClass();
$data->course = $course;
$data->topic = $topic;

$returnurl = new moodle_url($CFG->wwwroot . '/local/need_based_trainings/show_interest.php');

$filterform = new show_interest_filter_form('', array('data' => $data), 'get');

$filterform->display();

if ($filterform->is_cancelled()) {
    redirect($returnurl);
} else if ($filterformdata = $filterform->get_data()) {
    $filterform->set_data($filterformdata);
    echo  render_show_interest_form();
} else {
    $filterform = new stdClass();
}



echo $OUTPUT->footer();
