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
 * @package local_profile_management
 * @author  Remote-Learner.net Inc
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2016 onwards Microsoft Open Technologies, Inc. (http://msopentech.com/)
 */

function local_profile_management_before_standard_top_of_body_html() {
    global $DB, $USER, $PAGE, $CFG;

    if (!is_siteadmin() && $USER->id > 0 &&
        $PAGE->pagetype != 'local-profile_management-index' &&
        !$USER->preference['auth_forcepasswordchange']
    ) {
        if (!$DB->record_exists_sql("SELECT * FROM {local_user_details} WHERE `subject` != '' and `position` != '' and userid = $USER->id")) {
             header('Location: ' . $CFG->wwwroot . '/local/profile_management/');
        }
    }
}
