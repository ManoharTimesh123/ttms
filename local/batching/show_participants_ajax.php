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
require_once($CFG->dirroot.'/local/batching/locallib.php');

$batchid = $_POST['batchid'];

$participantdata = get_participants_by_batch($batchid);
$output = '';

$table = new html_table();
$tableheader = array(
    get_string('serialnumber', 'local_batching'),
    get_string('participantname', 'local_batching'),
    get_string('schoolname', 'local_batching')
);

$table->head = $tableheader;
$data = array();
$i = 1;
foreach ($participantdata as $participant) {
    $row = array();
    $row[] = $i;
    $row[] = $participant->uname;
    $row[] = $participant->schoolname;
    $data[] = $row;
    $i++;
}

$table->data = $data;
$table->id = 'batching-list';
$out = html_writer::table($table);
$output .= '<div class="table-responsive">'.$out.'</div>';
echo $output;
exit();
