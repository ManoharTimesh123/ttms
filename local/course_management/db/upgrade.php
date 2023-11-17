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
 * Local Course Management
 *
 * @package    local_course_management
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2023 TTMS Limited
 */

function xmldb_local_course_management_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    $result = true;

    if ($result && (int) $oldversion < 2023052800) {

        $table = new xmldb_table('local_course_details');
        $field = new xmldb_field('certificatetemplate', XMLDB_TYPE_CHAR, '100', null, false, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint($result, 2023052800, 'local', 'course_management');
    }

    if ($result && (int) $oldversion < 2023061202) {

        $localcoursedetailstable = new xmldb_table('local_course_details');
        $enablegroupingfield = new xmldb_field('grouping', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if (!$dbman->field_exists($localcoursedetailstable, $enablegroupingfield)) {
            $dbman->rename_field($localcoursedetailstable, $enablegroupingfield, 'enablegrouping',
                                    $continue = true, $feedback = true);
        }

        $localgroupdetailstable = new xmldb_table('local_group_details');
        $groupfield = new xmldb_field('group', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if (!$dbman->field_exists($localgroupdetailstable, $groupfield)) {
            $dbman->rename_field($localgroupdetailstable, $groupfield, 'groupid',
                                    $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023061202, 'local', 'course_management');
    }

    if ($result && (int) $oldversion < 2023061400) {

        $localcoursedetailstable = new xmldb_table('local_course_details');
        $enablegroupingfield = new xmldb_field('grouping', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if ($dbman->field_exists($localcoursedetailstable, $enablegroupingfield)) {
            $dbman->rename_field($localcoursedetailstable, $enablegroupingfield, 'enablegrouping',
                                    $continue = true, $feedback = true);
        }

        $localgroupdetailstable = new xmldb_table('local_group_details');
        $groupfield = new xmldb_field('group', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if ($dbman->field_exists($localgroupdetailstable, $groupfield)) {
            $dbman->rename_field($localgroupdetailstable, $groupfield, 'groupid',
                                    $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023061400, 'local', 'course_management');
    }

    if ($result && (int) $oldversion < 2023061402) {

        $localcoursedetailstable = new xmldb_table('local_course_details');
        $enablegroupingfield = new xmldb_field('grouping', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if ($dbman->field_exists($localcoursedetailstable, $enablegroupingfield)) {
            $dbman->rename_field($localcoursedetailstable, $enablegroupingfield, 'enablegrouping',
                                    $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023061402, 'local', 'course_management');
    }

    return $result;
}
