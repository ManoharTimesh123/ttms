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
 * Plugin library
 *
 * @package local_report
 * @author  Remote-Learner.net Inc
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2016 onwards Microsoft Open Technologies, Inc. (http://msopentech.com/)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/reports/renderer.php');

function local_reports_before_standard_top_of_body_html() {
    global $DB, $PAGE;

    // This funcion is use for view attendence report.
    if ($PAGE->pagetype == 'mod-attendance-report') {
        $PAGE->requires->jquery();
        $PAGE->requires->js('/local/reports/js/report.js');
        $attendancemoduleid = optional_param('id', 0, PARAM_RAW);

        echo '<div class="report-filter"><div class="report-filter-form">';
        render_activity_form('attendance', $attendancemoduleid);
        echo '</div>';
        echo '<a class="filter-toggle"><i class="fa fa-filter"></i> ' . get_string('filters', 'local_reports') . ' </a>';
        echo '</div>';
    }

    // This funcion is use for view feedback report.
    if ($PAGE->pagetype == 'mod-questionnaire-report') {
        $PAGE->requires->jquery();
        $PAGE->requires->js('/local/reports/js/report.js');
        $feedbackid = optional_param('instance', 0, PARAM_RAW);

        echo '<div class="report-filter"><div class="report-filter-form">';
        render_activity_form('questionnaire', $feedbackid);
        echo '</div>';
        echo '<a class="filter-toggle"><i class="fa fa-filter"></i> ' . get_string('filters', 'local_reports') . ' </a>';
        echo '</div>';
    }

    // This funcion is use for view certificate report.
    if ($PAGE->pagetype == 'mod-certificate-report') {
        $PAGE->requires->jquery();
        $PAGE->requires->js('/local/reports/js/report.js');
        $certificatemoduleid = optional_param('id', 0, PARAM_RAW);

        echo '<div class="report-filter"><div class="report-filter-form">';
        render_activity_form('certificate', $certificatemoduleid);
        echo '</div>';
        echo '<a class="filter-toggle"><i class="fa fa-filter"></i> ' . get_string('filters', 'local_reports') . ' </a>';
        echo '</div>';
    }

    // This funcion is use for view course activity report.
    if ($PAGE->pagetype == 'report-outline-index') {
        $PAGE->requires->jquery();
        $PAGE->requires->js('/local/reports/js/report.js');
        $courseid = optional_param('id', 0, PARAM_RAW);

        echo '<div class="report-filter"><div class="report-filter-form">';
        render_course_form('course_activity', $courseid);
        echo '</div>';
        echo '<a class="filter-toggle"><i class="fa fa-filter"></i> ' . get_string('filters', 'local_reports') . ' </a>';
        echo '</div>';
    }

    // This funcion is use for view course completion report.
    if ($PAGE->pagetype == 'report-progress-index') {
        $PAGE->requires->jquery();
        $PAGE->requires->js('/local/reports/js/report.js');
        $courseid = optional_param('course', 0, PARAM_RAW);

        echo '<div class="report-filter"><div class="report-filter-form">';
        render_course_form('course_completion', $courseid);
        echo '</div>';
        echo '<a class="filter-toggle"><i class="fa fa-filter"></i> ' . get_string('filters', 'local_reports') . ' </a>';
        echo '</div>';
    }
}
