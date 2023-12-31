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
 * Executes on plugin upgrade.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

defined('MOODLE_INTERNAL') || die;

/**
 * When upgrading plugin, execute the following code.
 *
 * @param int $oldversion Previous version of plugin (from DB).
 * @return bool Successful upgrade or not.
 */
function xmldb_block_advnotifications_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // This was needed to add tables during development - probably not even need anymore. Leaving just-in-case (for now).
    if ($oldversion < 2017100217) {
        // Define table block_advnotifications to be created.
        $table = new xmldb_table('block_advnotifications');

        // Adding fields to table block_advnotifications.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('title', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('districtid', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('zoneid', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('dietid', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('schoolid', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'info');
        $table->add_field('aicon', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('global', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('blockid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('dismissible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('date_from', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0000000000');
        $table->add_field('date_to', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0000000000');
        $table->add_field('times', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('deleted_at', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0000000000');
        $table->add_field('deleted_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '-1');
        $table->add_field('created_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '-1');

        // Adding keys to table block_advnotifications.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_advnotifications.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_advnotificationsdissed to be created.
        $table = new xmldb_table('block_advnotificationsdissed');

        // Adding fields to table block_advnotificationsdissed.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('not_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('dismissed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('seen', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_advnotificationsdissed.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_advnotificationsdissed.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Advnotifications savepoint reached.
        upgrade_block_savepoint(true, 2017100217, 'advnotifications');
    }

    // If upgrading from version earlier than v1.4.1 - max for 'seen' needs to be increased.
    if ($oldversion < 2021010616) {

        // Increase length/precision of field 'seen' in table 'block_advnotificationsdissed' to '10'.
        $table = new xmldb_table('block_advnotificationsdissed');
        $field = new xmldb_field('seen', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Update DB.
        $dbman->change_field_precision($table, $field);

        // Upgrade savepoint reached.
        upgrade_block_savepoint(true, 2021010616, 'advnotifications');
    }

    // Add future upgrade points here.

    return true;
}