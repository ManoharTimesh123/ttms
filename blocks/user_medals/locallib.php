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
 * User Medals
 *
 * @package    block_user_medals
 */

function get_medals() {
    global $DB, $USER, $CFG;

    $usermedals = 0;

    $encryptedvalue = $DB->get_field('block_instances', 'configdata', array('blockname' => 'user_medals'));

    $caculationcriteriavalue = unserialize(base64_decode($encryptedvalue));

    if ($caculationcriteriavalue) {
        $caculationcriteriavalue = $caculationcriteriavalue->calculation_criteria;
    } else {
        $caculationcriteriavalue = 3;
    }

    $usercertificatessql = <<<SQL
                SELECT count(ci.id) FROM {certificate} c
                INNER JOIN {certificate_issues} ci ON c.id = ci.certificateid
                WHERE ci.userid = :userid
                SQL;

    $params = [
        'userid' => $USER->id
    ];

    $certificates = $DB->count_records_sql($usercertificatessql, $params);

    if ($certificates > 0) {
        $usermedals = $certificates / $caculationcriteriavalue;
        $usermedals = (int)$usermedals;
    }

    return $usermedals;
}
