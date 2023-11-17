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
 * The modality Management
 *
 * @package local_modality
 * @author Nadia Farheen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

function get_modalities() {
    global $DB;
    $modalities = $DB->get_records('local_modality');
    return $modalities;
}

/**
 * Serve the files from the MYPLUGIN file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function local_modality_pluginfile($context, $filearea, $args, $forcedownload, $options) {
    global $CFG, $DB, $USER;

    $fs = get_file_storage();

    $filename = 'FieldTrip.png';
    $filepath = '/';
    $filearea = $args[0];

    $file = $fs->get_file($context->id, 'local_modality', 'icon', $filearea, $filepath, $filename);
    if (!$file) {
        send_file_not_found();
    }

    $forcedownload = false;
    send_stored_file($file, null, 0, $forcedownload, $options);
}
