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
 * @package local_directory
 * @author  Remote-Learner.net Inc
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2016 onwards Microsoft Open Technologies, Inc. (http://msopentech.com/)
 */

function col_userpicture($id) {
    global $DB, $OUTPUT, $USER, $CFG;
    $values = $DB->get_record('user', array('id' => $id));
    $userimage = $OUTPUT->user_picture($values, array('size' => 150, 'class' => 'img-fluid rounded-circle'));

    // Use DOMDocument to parse the HTML.
    $dom = new DOMDocument();
    $dom->loadHTML($userimage, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // Find the anchor tag.
    $anchortag = $dom->getElementsByTagName('a')->item(0);

    // Find the img tag within the anchor tag.
    $imgtag = $anchortag->getElementsByTagName('img')->item(0);

    // Get the src and alt attributes of the img tag.
    $src = $imgtag->getAttribute('src');
    $alt = $imgtag->getAttribute('alt');

    // Build a new HTML string with just the img tag.
    $newhtml = '<img src="' . $src . '" alt="' . $alt . '">';
    if ($newhtml) {
        return '<div class="relative"><div class="absolute">'.$newhtml.'</div>'.$newhtml.'</div>';
    } else {
        return "No Image";
    }
}

function get_user_public_fields($id) {
    global $DB, $USER, $CFG;

    $publicfields = $DB->get_records('local_user_public_fields', array('visible' => 1));

    $thead = [];
    $tbody = '';

    foreach ($publicfields as $customfieldvalue) {

        $fieldname = $customfieldvalue->userfields;
        $thead[] = $fieldname;

        if ($customfieldvalue->userfields == 'picture') {
            $tbody .= '<td>'.col_userpicture($id).'</td>';
        } else {

            $paramsuserdetails = [
                'table_name' => $CFG->prefix.'local_user_details',
                'columnname' => $customfieldvalue->userfields
            ];

            $paramsuser = [
                'table_name' => $CFG->prefix.'user',
                'columnname' => $customfieldvalue->userfields
            ];

            $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table_name AND COLUMN_NAME = :columnname";

            if ($DB->get_record_sql($sql, $paramsuserdetails)) {

                if ($customfieldvalue->dependent) {

                    $sqluser = "SELECT * FROM $CFG->prefix$customfieldvalue->dependent WHERE $customfieldvalue->dependant_on
                    IN (SELECT $customfieldvalue->userfields FROM {local_user_details} WHERE userid = $id)";
                    $sch = $DB->get_records_sql($sqluser);

                    if (!empty($sch)) {
                        $scharr = [];
                        foreach ($sch as $schvalue) {
                            array_push($scharr, $schvalue->name);
                        }
                        $scharr = implode(',', $scharr);
                        $tbody .= '<td>t' . $scharr . '</td>';

                    } else {
                        $tbody .= '<td>Not Available</td>';
                    }

                } else {

                    if ($data = $DB->record_exists("local_user_details", array('userid' => $id))) {
                        $tbody .= '<td>'.$data->$fieldname.'</td>';
                    } else {
                        $tbody .= '<td>Not Available</td>';
                    }

                }

            } else if ($DB->get_record_sql($sql, $paramsuser)) {
                if ($DB->record_exists("user", array('id' => $id))) {
                    $userdata = $DB->get_record("user", array("id" => $id));
                    $tbody .= '<td>'.$userdata->$fieldname.'</td>';
                } else {
                    $tbody .= '<td>Not Available</td>';
                }
            }
        }
    }

    return [
        $tbody,
        $thead
        ];
}
