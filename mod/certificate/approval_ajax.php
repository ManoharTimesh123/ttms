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
 * mod_certificate
 *
 * @package    mod_certificate
 * @copyright  Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* INTG Customization Start : Approval ajax file for getting the data based on the selection of the course and group filter. */
require_once('../../config.php');
require_once('locallib.php');

require_login();

$type = optional_param('type', '', PARAM_RAW);
$action = optional_param('action', '', PARAM_RAW);
$courseid = optional_param('course', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$groupid = optional_param('group', 0, PARAM_INT);
$statuscomment = optional_param('statuscomment', '', PARAM_RAW);
global $DB, $USER;

if ($type == 'filterdata' && $courseid > 1) {
    if (is_siteadmin()) {
        $coursegroups = $DB->get_records('groups', array('courseid' => $courseid));
    } else {
        $coordinatorroleid = $DB->get_field('role', 'id', array('shortname' => 'coordinator'));
        $sql = "SELECT g.id, g.name FROM {groups} AS g
                JOIN {course} AS c ON c.id = g.courseid
                JOIN {context} AS cxt ON cxt.instanceid = c.id
                JOIN {role_assignments} AS ra ON ra.contextid = cxt.id
                WHERE c.id = :courseid AND cxt.contextlevel = :contextlevel
                AND ra.roleid = :coordinatorroleid AND ra.userid = :userid";

        $params = ['courseid' => $courseid,
                    'contextlevel' => 50,
                    'coordinatorroleid' => $coordinatorroleid,
                    'userid' => $USER->id];

        $coursegroups = $DB->get_records_sql($sql, $params);
    }

    $data = [];
    foreach ($coursegroups as $coursegroup) {
        $data[$coursegroup->id] = $coursegroup->name;
    }

    if (!empty($data)) {
        echo json_encode($data);
    } else {
        echo 0;
    }
    exit;
} else if ($type == 'updatedata' && $courseid > 1 && $userid > 2) {

    $data = new stdClass();
    if (!empty($action)) {
        $status = strtolower($action);
    } else {
        $status = '';
    }
    if (!empty($status)) {
        $data->status = $status;
        $data->statuscomment = $statuscomment;
        $return = update_user_certificate_status($userid, $courseid, $data);
        echo $return;
    }
    echo 0;
} else {
    echo 0;
}
/* INTG Customization End. */

