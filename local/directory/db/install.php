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
 * Plugin installation script.
 *
 * @package local_directory
 * @author James McQuillan <james.mcquillan@remote-learner.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

 /**
  * Installation script.
  */
function xmldb_local_directory_install() {
    global $DB;
    // Add navigation item to custom menu.
    $fields = array(
        array(
            'userfields' => 'firstname',
        ),
        array(
            'userfields' => 'lastname',
        ),
        array(
            'userfields' => 'email',
        ),
        array(
            'userfields' => 'position',
        ),
    );
    $DB->insert_records('local_user_public_fields', $fields);

    return true;
}
