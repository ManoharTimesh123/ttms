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
 * The batching Management
 *
 * @package    local_batching
 * @author     Tarun Upadhyay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require($CFG->dirroot.'/local/batching/create_training_form.php');
require_once($CFG->dirroot.'/local/batching/lib.php');
require($CFG->dirroot.'/local/batching/locallib.php');

global $CFG, $DB;

$id = optional_param('id', 0, PARAM_INT); // Batching id.
$course = optional_param('course', 0, PARAM_INT); // Batching course id.

$systemcontext = context_system::instance();
require_login();

require_capability('local/batching:propose', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/batching/planning.php', array('id' => $id, 'course' => $course));
$PAGE->set_title(get_string('batchingcreate_training', 'local_batching'));
$PAGE->set_heading(get_string('batchingcreate_training', 'local_batching'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/batching/js/display_certificate.js');
if ($id > 0) {
    $data = get_batchings($id)[1];
} else {
    $data = '';
}

$PAGE->navbar->add(get_string('pluginname', 'local_batching'), new moodle_url('/local/batching/index.php'),
    navigation_node::TYPE_SETTING);

$returnurl = new moodle_url($CFG->wwwroot.'/local/batching/index.php');
$url = new moodle_url($CFG->wwwroot.'/local/batching/create_training.php', array('id' => $id, 'course' => $course));
$nexturl = new moodle_url($CFG->wwwroot.'/local/batching/index.php');

$mform = new batching_create_training_form(null, array('id' => $id, 'course' => $course, 'data' => $data));
$data->summary = array('text' => $data->summary, 'format' => $data->summary);

$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url);
} else if ($data = $mform->get_data()) {
    $courseid = create_batching_course($data);
    redirect($nexturl);
}
echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
