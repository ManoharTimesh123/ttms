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
 * Custom Notifications
 *
 * @package    local_customnotifications
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2023 TTMS Limited
 */

global $CFG, $DB, $OUTPUT;
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot.'/local/customnotifications/renderer.php');

$systemcontext = context_system::instance();
require_login();

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/customnotifications/index.php');
$PAGE->set_title(get_string('template', 'local_customnotifications'));
$PAGE->set_heading(get_string('template', 'local_customnotifications'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/local/customnotifications/js/jquery.dataTables.min.js', true);
$PAGE->requires->css('/local/customnotifications/css/jquery.dataTables.css');
$PAGE->navbar->add(get_string('administrationsite'), new moodle_url($CFG->wwwroot.'/admin/search.php'),
    navigation_node::TYPE_SETTING);
$PAGE->navbar->add(get_string('templates', 'local_customnotifications'));

echo $OUTPUT->header();

$renderlist = $PAGE->get_renderer('local_customnotifications');

echo '<div style="float:right;padding:20px 0px;">'.$renderlist->render_template_button().'</div>';
echo $renderlist->list_templates();

echo $OUTPUT->footer();
