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

global $CFG, $DB, $PAGE;

require($CFG->dirroot . '/local/batching/launch_training_form.php');
require_once($CFG->dirroot . '/local/batching/lib.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/attendance/lib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/certificate/lib.php');
require($CFG->dirroot . '/local/batching/locallib.php');
require_once($CFG->dirroot . '/mod/zoom/lib.php');
require_once($CFG->dirroot . '/mod/zoom/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Batching id.
$launch = optional_param('launch', 0, PARAM_INT);
$agree = optional_param('agree', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();
require_capability('local/batching:launch', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/batching/launch_training.php', array('id' => $id));
$PAGE->set_title(get_string('batchinglaunched', 'local_batching'));
$PAGE->set_heading(get_string('batchinglaunched', 'local_batching'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$data = $DB->get_record('local_batching', array('id' => $id));

$PAGE->navbar->add(get_string('pluginname', 'local_batching'), new moodle_url('/local/batching/index.php'),
    navigation_node::TYPE_SETTING);

$returnurl = new moodle_url($CFG->wwwroot . '/local/batching/distributions.php', array('id' => $id));
$url = new moodle_url($CFG->wwwroot . '/local/batching/launch_training.php', array('id' => $id));
$nexturl = new moodle_url($CFG->wwwroot . '/local/batching/launch_training.php', array('id' => $id));

$mform = new batching_launch_training_form(null, array('id' => $id));

$mform->set_data($data);

echo $OUTPUT->header();

if ($launch) {
    $launchtraining = get_string('launchtraining', 'local_batching');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot . '/local/batching/launch_training.php',
        array('agree' => 1, 'id' => $id)), get_string('yes'));
    $formcancel = new single_button(new moodle_url($CFG->wwwroot . '/local/batching/launch_training.php',
        array('agree' => 0, 'id' => $id)), get_string('no'));
    echo $OUTPUT->confirm($launchtraining, $formcontinue, $formcancel);
} else {
    $mform->display();
}

if ($agree == 1) {

    if (launch_batching($data->id)) {
        $batchting = get_batchings($data->id)[$data->id];
        $data->status = get_string('launched', 'local_batching');
        $data->filenumber = $batchting->file_number;
        $data->comment = $batchting->comment;
        update_proposal($data);
        redirect($nexturl);
    }

}

echo $OUTPUT->footer();

