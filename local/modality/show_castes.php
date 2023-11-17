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
 * The modality Management
 *
 * @package local_modality
 * @author Nadia Farheen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_once($CFG->dirroot . '/local/modality/renderer.php');
require_once($CFG->dirroot . '/local/modality/locallib.php');

$id = optional_param('id', 0, PARAM_INT);
$agree = optional_param('agree', 0, PARAM_INT);

require_login();
$systemcontext = context_system::instance();
require_capability('local/modality:castemanage', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot.'/local/modality/show_castes.php');
$PAGE->set_title(get_string('castes', 'local_modality'));
$PAGE->set_heading(get_string('castes', 'local_modality'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();

$PAGE->requires->css('/local/modality/css/jquery.dataTables.css');
$PAGE->requires->js('/local/modality/js/jquery.dataTables.min.js', true);

echo $OUTPUT->header();

// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url($CFG->wwwroot.'/local/modality/show_castes.php');

if ($agree == 1 && $id > 0) {
    delete_caste($id);
}
$text = get_string('createcaste', 'local_modality');
$url = $CFG->wwwroot.'/local/modality/castes.php';

if (has_capability('local/modality:casteadd', $systemcontext) || is_siteadmin()) {
    echo add_button($text, $url);
}
// Get the list of castes and display.
echo render_castes();

echo $OUTPUT->footer();
