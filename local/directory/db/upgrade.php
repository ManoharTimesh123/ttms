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
 * Plugin upgrade script.
 *
 * @package local_directory
 * @author James McQuillan <james.mcquillan@remote-learner.net>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

 /**
  * Update plugin.
  *
  * @param int $oldversion the version we are upgrading from
  * @return bool result
  */

function xmldb_local_directory_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_user_public_fields');

    if ($oldversion < 2023041426) {

        $field = new xmldb_field('dependent', XMLDB_TYPE_TEXT, null, null, null, null, 0, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('dependant_on', XMLDB_TYPE_TEXT, null, null, null, null, 0, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('custom', XMLDB_TYPE_INTEGER, 10, null, null, null, 0, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hierarchy savepoint reached.
        upgrade_plugin_savepoint(true, 2023041426, 'local', 'directory');
    }

    if ($oldversion < 2023041500) {

        $field = new xmldb_field('custom');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Hierarchy savepoint reached.
        upgrade_plugin_savepoint(true, 2023041500, 'local', 'directory');
    }

    return true;
}
