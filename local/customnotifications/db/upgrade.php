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
 * Custom Notifications
 *
 * @package    local_customnotifications
 * @author    Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2023 TTMS Limited
 */

function xmldb_local_customnotifications_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023051600) {

        $sql = 'RENAME TABLE {local_cn_templates} TO {local_notification_templates}';
        $DB->execute($sql, $params = array());

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2023051600, 'local', 'customnotifications');
    }

    if ($oldversion < 2023060102) {

        $table = new xmldb_table('local_notification_records');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('template', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_field('touser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $table->add_field('params', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_field('emailstatus', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_field('smsstatus', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_field('emailmessage', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('smsmessage', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('source', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_field('emailtimecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('emailtimetriggered', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('smstimecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('smstimetriggered', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2023060102, 'local', 'customnotifications');
    }

    if ($oldversion < 2023060105) {

        $table = new xmldb_table('local_notification_templates');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $field1 = new xmldb_field('templatename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $field2 = new xmldb_field('templateid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, 0);
        $field3 = new xmldb_field('ccuser', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $field4 = new xmldb_field('bccuser', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);

        if (!$dbman->table_exists($table)) {

            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field, $continue = true, $feedback = true);
            }

            if ($dbman->field_exists($table, $field1)) {
                $dbman->rename_field($table, $field1, 'code', $continue = true, $feedback = true);
            }

            if ($dbman->field_exists($table, $field2)) {
                $dbman->rename_field($table, $field2, 'smstemplateid', $continue = true, $feedback = true);
            }

            if ($dbman->field_exists($table, $field3)) {
                $dbman->rename_field($table, $field3, 'ccusers', $continue = true, $feedback = true);
            }

            if ($dbman->field_exists($table, $field4)) {
                $dbman->rename_field($table, $field4, 'bccusers', $continue = true, $feedback = true);
            }

        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2023060105, 'local', 'customnotifications');
    }

    if ($oldversion < 2023060800) {

        $notificationrecordstable = new xmldb_table('local_notification_records');
        $typefield = new xmldb_field('type', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $emailtobesentfield = new xmldb_field('emailtobesent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $smstobesentfield = new xmldb_field('smstobesent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if (!$dbman->table_exists($notificationrecordstable)) {

            if (!$dbman->field_exists($notificationrecordstable, $typefield)) {
                $dbman->drop_field($notificationrecordstable, $typefield, $continue = true, $feedback = true);
            }

            if ($dbman->field_exists($notificationrecordstable, $field1)) {
                $dbman->add_field($notificationrecordstable, $field1, $continue = true, $feedback = true);
            }

            if ($dbman->field_exists($notificationrecordstable, $field2)) {
                $dbman->add_field($notificationrecordstable, $field2, $continue = true, $feedback = true);
            }

        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2023060800, 'local', 'customnotifications');
    }

    if ($oldversion < 2023061400) {

        $notificationtemplatestable = new xmldb_table('local_notification_templates');
        $namefield = new xmldb_field('name', XMLDB_TYPE_CHAR, 255);
        $templatenamefield = new xmldb_field('templatename', XMLDB_TYPE_CHAR);
        $templateidfield = new xmldb_field('templateid', XMLDB_TYPE_INTEGER, 10);
        $ccuserfield = new xmldb_field('ccuser', XMLDB_TYPE_CHAR);
        $bccuserfield = new xmldb_field('bccuser', XMLDB_TYPE_CHAR);

        if ($dbman->table_exists($notificationtemplatestable)) {

            if (!$dbman->field_exists($notificationtemplatestable, $namefield)) {
                $dbman->add_field($notificationtemplatestable, $namefield);
            }

            if ($dbman->field_exists($notificationtemplatestable, $templatenamefield)) {
                $dbman->rename_field($notificationtemplatestable, $templatenamefield, 'code');
            }

            if ($dbman->field_exists($notificationtemplatestable, $templateidfield)) {
                $dbman->rename_field($notificationtemplatestable, $templateidfield, 'smstemplateid');
            }

            if ($dbman->field_exists($notificationtemplatestable, $ccuserfield)) {
                $dbman->rename_field($notificationtemplatestable, $ccuserfield, 'ccusers');
            }

            if ($dbman->field_exists($notificationtemplatestable, $bccuserfield)) {
                $dbman->rename_field($notificationtemplatestable, $bccuserfield, 'bccusers');
            }

        }

        $notificationrecordstable = new xmldb_table('local_notification_records');
        $typefield = new xmldb_field('type', XMLDB_TYPE_CHAR);
        $emailtobesentfield = new xmldb_field('emailtobesent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $smstobesentfield = new xmldb_field('smstobesent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if ($dbman->table_exists($notificationrecordstable)) {

            if ($dbman->field_exists($notificationrecordstable, $typefield)) {
                $dbman->drop_field($notificationrecordstable, $typefield, $continue = true, $feedback = true);
            }

            if (!$dbman->field_exists($notificationrecordstable, $emailtobesentfield)) {
                $dbman->add_field($notificationrecordstable, $emailtobesentfield, $continue = true, $feedback = true);
            }

            if (!$dbman->field_exists($notificationrecordstable, $smstobesentfield)) {
                $dbman->add_field($notificationrecordstable, $smstobesentfield, $continue = true, $feedback = true);
            }

        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2023061400, 'local', 'customnotifications');
    }

    return true;
}
