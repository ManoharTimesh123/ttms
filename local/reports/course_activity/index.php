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
 * @subpackage Course activity report
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/local/reports/renderer.php');

require_login();
$courseid = optional_param('course', 0, PARAM_INT);
global $CFG, $PAGE;

$systemcontext = context_system::instance();
require_capability('local/reports:trainingactivity', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->requires->jquery();
$PAGE->requires->js('/local/reports/js/report.js');
$PAGE->set_url($CFG->wwwroot . '/local/reports/course_activity/index.php');
$PAGE->set_title(get_string('pluginname', 'local_reports'));
$PAGE->set_heading(get_string('pluginname', 'local_reports'));
$PAGE->set_pagelayout('admin');

if ($courseid) {
    $courseurl = new moodle_url($CFG->wwwroot . '/report/outline/index.php', ['id' => $courseid]);
    redirect($courseurl);
}

echo $OUTPUT->header();
render_course_form('course_activity');
echo $OUTPUT->footer();
