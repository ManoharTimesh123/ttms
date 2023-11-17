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
 * @package local_modality
 * @author  Nadia Farheen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Nadia Farheen Limited
 */

function xmldb_local_modality_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023040500) {
        // Add new fields to local_coursepackage table.
         $table = new xmldb_table('local_subjects');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Curriculum savepoint reached.
        upgrade_plugin_savepoint(true, 2023040500, 'local', 'modality');
    }

    if ($oldversion < 2023040802) {

        // Add new fields to local_schools table.
        $table = new xmldb_table('local_schools');
        $field = new xmldb_field('venue_capacity', XMLDB_TYPE_INTEGER, '5', null, false,  null, null, 'isvenue');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Curriculum savepoint reached.
        upgrade_plugin_savepoint(true, 2023040800, 'local', 'modality');
    }

    if ($oldversion < 2023042800) {
        // Add new local_castes table.
        $tablelocalcastes = new xmldb_table('local_castes');
        $tablelocalcastes->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalcastes->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalcastes->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalcastes->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalcastes->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalcastes->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalcastes->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalcastes)) {
            $dbman->create_table($tablelocalcastes);
        }

        // Add new local_grades table.
        $tablelocalgrades = new xmldb_table('local_grades');
        $tablelocalgrades->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalgrades->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalgrades->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalgrades->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalgrades->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalgrades->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalgrades->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalgrades)) {
            $dbman->create_table($tablelocalgrades);
        }

        // Add new local_grades table.
        $tablelocalposts = new xmldb_table('local_posts');
        $tablelocalposts->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalposts->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalposts->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalposts->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalposts->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalposts->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalposts->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalposts)) {
            $dbman->create_table($tablelocalposts);
        }

        // Curriculum savepoint reached.
        upgrade_plugin_savepoint(true, 2023042800, 'local', 'modality');
    }

    if ($oldversion < 2023042803) {

        // Define table questionnaire_rate_weightage to be created.
        $table = new xmldb_table('user_ratings_weightage');

        // Adding fields to table questionnaire_rate_weightage.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('provided_by_user_roleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('received_by_user_roleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('rate_weightage', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table questionnaire_rate_weightage.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for questionnaire_rate_weightage.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Questionnaire savepoint reached.
        upgrade_plugin_savepoint(true, 2023042803, 'local', 'modality');
    }

    if ($oldversion < 2023052100) {

        // Add new fields to local_schools table.
        $table = new xmldb_table('local_schools');
        $field = new xmldb_field('address', XMLDB_TYPE_CHAR, '255', null, false,  null, null, 'code');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Curriculum savepoint reached.
        upgrade_plugin_savepoint(true, 2023052100, 'local', 'modality');
    }

    return true;
}

