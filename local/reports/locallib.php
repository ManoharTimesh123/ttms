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
 * The reports
 *
 * @package    local_reports
 * @author     Lalit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

require_once(dirname(__FILE__) . '/../../config.php');

function activity_list($course, $activityname) {
    global $DB;

    if ($activityname == 'questionnaire') {
        $activitymoduleid = $DB->get_field('modules', 'id', ['name' => $activityname]);

        $sql = 'SELECT a.id, a.name
        FROM {course_modules} cm
        JOIN {questionnaire} a ON a.id = cm.instance
        JOIN {questionnaire_response} qr ON qr.questionnaireid = a.id
        WHERE cm.course = ' . $course . ' AND cm.module = ' . $activitymoduleid.'';

        $activitydetails = $DB->get_records_sql_menu($sql);
    } else {
        $activitymoduleid = $DB->get_field('modules', 'id', ['name' => $activityname]);

        $sql = 'SELECT cm.id, a.name
        FROM {course_modules} cm
        JOIN {' . $activityname . '} a ON a.id = cm.instance
        WHERE cm.course = ' . $course . ' AND cm.module = ' . $activitymoduleid.'';

        $activitydetails = $DB->get_records_sql_menu($sql);
    }
    return $activitydetails;
}
