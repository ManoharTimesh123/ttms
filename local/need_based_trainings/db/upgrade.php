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
 * Need Based Trainings
 *
 * @package    local_need_based_trainings
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_need_based_trainings_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    $result = true;

    if ($result && (int) $oldversion < 2023061500) {
        $tablenbttopics = new xmldb_table('local_nbt_topics');
        $tablenbttopics->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablenbttopics->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablenbttopics->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablenbttopics->add_field('description', XMLDB_TYPE_TEXT, '', null, XMLDB_NOTNULL, null);
        $tablenbttopics->add_field('startdate', XMLDB_TYPE_INTEGER, '10', null, false, null, null);
        $tablenbttopics->add_field('enddate', XMLDB_TYPE_INTEGER, '10', null, false, null, null);
        $tablenbttopics->add_field('status', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablenbttopics->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablenbttopics->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablenbttopics->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablenbttopics->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablenbttopics->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablenbttopics)) {
            $dbman->create_table($tablenbttopics);
        }

        $tablenbttopicinterests = new xmldb_table('local_nbt_topic_interests');
        $tablenbttopicinterests->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablenbttopicinterests->add_field('topic', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablenbttopicinterests->add_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablenbttopicinterests->add_field('reason', XMLDB_TYPE_TEXT, '', null, XMLDB_NOTNULL, null);
        $tablenbttopicinterests->add_field('status', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablenbttopicinterests->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablenbttopicinterests->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablenbttopicinterests->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablenbttopicinterests->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablenbttopicinterests->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablenbttopicinterests)) {
            $dbman->create_table($tablenbttopicinterests);
        }

        $tablelocalnbtinterests = new xmldb_table('local_nbt_interests');

        if ($dbman->table_exists($tablelocalnbtinterests)) {
            $dbman->rename_table($tablelocalnbtinterests, 'local_nbt_training_interests', $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023061500, 'local', 'need_based_trainings');
    }

    return $result;
}
