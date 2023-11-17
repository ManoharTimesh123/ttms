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
require_once($CFG->dirroot.'/local/batching/lib.php');
require_once($CFG->dirroot.'/local/batching/locallib.php');

$agree = optional_param('agree', 0, PARAM_INT); // Confirmation to delete the batching.
$id = optional_param('id', 0, PARAM_INT); // Batching id.

require_login();

$systemcontext = context_system::instance();

if (!has_capability('local/batching:propose', $systemcontext) && !has_capability('local/batching:perform', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermission', 'local_batching'));
}

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot.'/local/batching/index.php');
$PAGE->set_title(get_string('batchinglist', 'local_batching'));
$PAGE->set_heading(get_string('batchinglist', 'local_batching'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$PAGE->requires->css('/local/batching/css/jquery.dataTables.css');
$PAGE->requires->js('/local/batching/js/jquery.dataTables.min.js', true);

echo $OUTPUT->header();

// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url($CFG->wwwroot.'/local/batching/index.php');

// Get the list of batching and display.

echo batching_stepper(1);

echo render_batching();

echo $OUTPUT->footer();
