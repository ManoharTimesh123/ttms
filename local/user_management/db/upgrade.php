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
 * The Course Package
 *
 * @package    local_user_management
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Nadia Farheen Limited
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_local_user_management_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    $result = true;

    if ($oldversion < 2023042800) {
        // Add new fields in local_user_details table.
        $tablelocaluserdetails = new xmldb_table('local_user_details');
        $tablelocaluserdetailsadddojfield = new xmldb_field('doj', XMLDB_TYPE_CHAR, '50', null, false,  null, null, 'dob');
        $tablelocaluserdetailsaddjobtypefield = new xmldb_field('jobtype', XMLDB_TYPE_CHAR, '100', null, false,  null, null, 'doj');
        $tablelocaluserdetailsaddgradefield = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, false,  null, null, 'subject');
        $tablelocaluserdetailsaddpostfield = new xmldb_field('post', XMLDB_TYPE_INTEGER, '10', null, false,  null, null, 'grade');
        $tablelocaluserdetailsaddcastefield = new xmldb_field('caste', XMLDB_TYPE_INTEGER, '10', null, false,  null, null, 'post');

        if (!$dbman->field_exists($tablelocaluserdetails, $tablelocaluserdetailsadddojfield)) {
            $dbman->add_field($tablelocaluserdetails, $tablelocaluserdetailsadddojfield);
        }

        if (!$dbman->field_exists($tablelocaluserdetails, $tablelocaluserdetailsaddjobtypefield)) {
            $dbman->add_field($tablelocaluserdetails, $tablelocaluserdetailsaddjobtypefield);
        }

        if (!$dbman->field_exists($tablelocaluserdetails, $tablelocaluserdetailsaddgradefield)) {
            $dbman->add_field($tablelocaluserdetails, $tablelocaluserdetailsaddgradefield);
        }

        if (!$dbman->field_exists($tablelocaluserdetails, $tablelocaluserdetailsaddpostfield)) {
            $dbman->add_field($tablelocaluserdetails, $tablelocaluserdetailsaddpostfield);
        }

        if (!$dbman->field_exists($tablelocaluserdetails, $tablelocaluserdetailsaddcastefield)) {
            $dbman->add_field($tablelocaluserdetails, $tablelocaluserdetailsaddcastefield);
        }

        // Curriculum savepoint reached.
        upgrade_plugin_savepoint(true, 2023042800, 'local', 'user_management');
    }

    if ($oldversion < 2023051600) {
        // Add new fields in local_user_details table.
        $tablelocaluserdetails = new xmldb_table('local_user_details');
        $tablelocaluserdetailsaddoverallratingfield = new xmldb_field('overallrating', XMLDB_TYPE_CHAR, '50', null, false,  null, 0, 'caste');

        if (!$dbman->field_exists($tablelocaluserdetails, $tablelocaluserdetailsaddoverallratingfield)) {
            $dbman->add_field($tablelocaluserdetails, $tablelocaluserdetailsaddoverallratingfield);
        }

        upgrade_plugin_savepoint(true, 2023051600, 'local', 'user_management');
    }

    if ($result && (int) $oldversion < 2023062000) {
        $tablelocaluserdetails = new xmldb_table('local_user_details');
        $tablelocaluserdetailsaddfielddeleted = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NULL, null, 0, 'subject');
        $tablelocaluserdetailsaddfielddusereleted = new xmldb_field('userdeleted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NULL, null, 0, 'usermodified');

        if (!$dbman->field_exists($tablelocaluserdetails, $tablelocaluserdetailsaddfielddeleted)) {
            $dbman->add_field($tablelocaluserdetails, $tablelocaluserdetailsaddfielddeleted, $continue = true, $feedback = true);
        }

        if (!$dbman->field_exists($tablelocaluserdetails, $tablelocaluserdetailsaddfielddusereleted)) {
            $dbman->add_field($tablelocaluserdetails, $tablelocaluserdetailsaddfielddusereleted, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023062000, 'local', 'user_management');
    }

    return true;
}
