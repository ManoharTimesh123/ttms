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
require_once($CFG->dirroot.'/local/batching/renderer.php');
require($CFG->dirroot.'/local/batching/filters_form.php');
require_once($CFG->dirroot.'/local/batching/lib.php');
require($CFG->dirroot.'/local/batching/locallib.php');

global $CFG, $DB;

$id = optional_param('id', 0, PARAM_INT); // Batching id.

require_login();

$systemcontext = context_system::instance();
require_capability('local/batching:perform', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/batching/filters.php', array('id' => $id));
$PAGE->set_title(get_string('batchingfilters', 'local_batching'));
$PAGE->set_heading(get_string('batchingfilters', 'local_batching'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/batching/js/batching_custom.js', true);

if ($id > 0) {
    $data = get_filters($id);
} else {
    $data = '';
}

$PAGE->navbar->add(get_string('pluginname', 'local_batching'), new moodle_url('/local/batching/index.php'),
    navigation_node::TYPE_SETTING);

$returnurl = new moodle_url($CFG->wwwroot.'/local/batching/planning.php', array('id' => $id));
$url = new moodle_url($CFG->wwwroot.'/local/batching/filters.php', array('id' => $id));
$nexturl = new moodle_url($CFG->wwwroot.'/local/batching/venues.php', ['id' => $id]);

$mform = new batching_filters_form(null, array('id' => $id, 'data' => $data));
$data->summary = array('text' => $data->summary, 'format' => $data->summary);

$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url);
} else if ($data = $mform->get_data()) {
    add_update_filter_data($data);
    redirect($nexturl);
}
echo $OUTPUT->header();

echo batching_stepper(3);

$mform->display();

echo $OUTPUT->footer();

