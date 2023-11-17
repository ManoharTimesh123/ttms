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
 * Dynamic data
 *
 * @package    local_reports
 * @subpackage reports value
 * @copyright  2007 Dan Poltawski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/reports/locallib.php');
require_login();
$courseid = optional_param('courseid', 0, PARAM_INT);
$reportmodule = optional_param('reportmodule', 0, PARAM_RAW);

if ($reportmodule) {
    $attendancedetails = activity_list($courseid, $reportmodule);
    $data = [];
    foreach ($attendancedetails as $key => $attendancedetail) {
        $data[$key] = $attendancedetail;
    }

    if (!empty($data)) {
        echo json_encode($data);
    } else {
        echo 0;
    }
    exit;
}
