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
class local_training_calendar_renderer extends plugin_renderer_base {

    public function get_trainingcourses($filterdata = false, $format = false) {
        global $DB, $CFG, $USER;
        $filterjson = '';
        $context = context_system::instance();
        if ($format) {
            $filterdata->format = $format;
        }
        $filterjson = json_encode($filterdata);
        $this->page->requires->js_call_amd('local_training_calendar/customview', 'viewCalendarCoursesJSDatatable', array('filterdata' => $filterjson));

        $table = new html_table();
        $table->id = "viewCalendarCoursesTable";
        $table->width = '100%';

        if (is_siteadmin() || has_capability('local/training_calendar:manage', $context)) {
            $coursename = get_string('coursename', 'local_training_calendar');
            $coursetype = get_string('coursetype', 'local_training_calendar');
            $coursecoordinator = get_string('coursecoordinator', 'local_training_calendar');
            $participationlevel = get_string('participationlevel', 'local_training_calendar');
            $courseduration = get_string('courseduration', 'local_training_calendar');
            $dates = get_string('dates', 'local_training_calendar');
            $courseprogress = get_string('courseprogress', 'local_training_calendar');
            $actions = get_string('actions', 'local_training_calendar');

            $table->head = [$coursename, $coursetype, $coursecoordinator, $participationlevel, $courseduration, $dates, $courseprogress, $actions];
            $table->align = array('left', 'left', 'left', 'left', 'center', 'center', 'center');
        }
        $output = '<div class="w-full pull-left">'. html_writer::table($table).'</div>';
        return $output;
    }

    public function courses_view($filterdata, $page, $perpage) {
        global $DB, $CFG, $USER;
        $systemcontext = context_system::instance();
        $formsql = '';
        $sql = "SELECT * FROM {course} lb WHERE lb.visible = 1";
        $recordsperpage = $page * $perpage;
        $formsql .= " ORDER BY lb.id DESC";
        $coursesdata = $DB->get_records_sql($sql.$formsql);
        $table = new html_table();

        $table->head = array('Course Name', 'Course Type', 'Course Coordinator', 'Participation Level', 'Course Duration', 'Dates', 'Course Progress', 'Session Details');
        $data = array();
        foreach ($coursesdata as $course) {
            $list = array();
            $list[] = $course->fullname;
            $list[] = $course->shortname;
            $list[] = $course->fullname;
            $list[] = $course->shortname;
            $list[] = $course->fullname;
            $list[] = $course->shortname;
            $list[] = "<span style = 'color:#2739c1'>123</span>";
            $list[] = "<span style = 'color:#2739c1'>123</span>";
            $data[] = $list;
        }
        $table->align = array('left', 'left', 'center', 'center');
        $table->width = '100%';
        $table->data = ($data) ? $data : get_string('norecordsfound', 'local_training_calendar');
        $table->id = 'course-index';
        $output = html_writer::table($table);
        return $output;
    }

    public function get_different_actions($board) {
        global $DB, $USER;

        $context = context_system::instance();
        $buttons = array();
        $boardfaculties = 0;
        $buttons[] = html_writer::link('javascript:void(0)', '<i class="fa fa-cog fa-fw" title="Edit Board"></i>',
                                        [
                                            'data-action' => 'createboardmodal',
                                            'class' => 'createboardmodal',
                                            'data-value' => $board->id,
                                            'class' => '',
                                            'onclick' => "(function(e){ require(\"local_training_calendar/newboard\").init({selector:\"createboardmodal\", contextid:$context->id, boardid:$board->id}) })(event)",
                                            'style' => 'cursor:pointer',
                                            'title' => 'edit'
                                        ]);

        $buttons[] = html_writer::link('javascript:void(0)', '<i class="fa fa-trash fa-fw" aria-hidden="true" title="Delete Board" aria-label="Delete"></i>',
                                        [
                                            'title' => get_string('delete'),
                                            'onclick' => '(function(e){ require("local_training_calendar/newboard").deleteConfirm({ action: "delete_board" ,id:'.$board->id.',context:'.$context->id.',fullname:"'.$board->name.'"}) })(event)'
                                        ]);
        return implode('', $buttons);
    }
}
