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
 * User Query
 * @package    block_user_query
 */

require_once('../../config.php');

global $DB;


$useremail = $_POST['useremail'];
$userquery = $_POST['userquery'];

$existrecord = $DB->get_record('block_user_query', array('user_email' => $useremail));
$userdata = new stdClass();
if (empty($existrecord)) {
    $userdata->user_email = $useremail;
    $userdata->user_query = $userquery;
    $userdata->timecreated = time();
    $DB->insert_record('block_user_query' , $userdata);
} else {
    $userdata->id = $existrecord->id;
    $userdata->user_query = $userquery;
    $DB->update_record('block_user_query' , $userdata);
}
