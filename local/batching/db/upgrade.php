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
 * The batching Management
 *
 * @package local_batching
 * @author  Nadia Farheen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_batching_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    $result = TRUE;

    if ($result && (int) $oldversion < 2023041100) {

        $tablelocalbatchingparticipants = new xmldb_table('local_batching_participants');

        if ($dbman->table_exists($tablelocalbatchingparticipants)) {

            $tablelocalbatchingparticipantsdropparticipantfield = new xmldb_field('participant');
            $tablelocalbatchingparticipantsdropfacilitatorfield = new xmldb_field('facilitator');
            $tablelocalbatchingparticipantsdropcoordinatorfield = new xmldb_field('coordinator');
            $tablelocalbatchingparticipantsdropobserverfield = new xmldb_field('observer');

            if ($dbman->field_exists($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsdropparticipantfield)) {
                $dbman->drop_field($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsdropparticipantfield, $continue = true, $feedback = true);
            }
            if ($dbman->field_exists($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsdropfacilitatorfield)) {
                $dbman->drop_field($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsdropfacilitatorfield, $continue = true, $feedback = true);
            }
            if ($dbman->field_exists($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsdropcoordinatorfield)) {
                $dbman->drop_field($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsdropcoordinatorfield, $continue = true, $feedback = true);
            }
            if ($dbman->field_exists($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsdropobserverfield)) {
                $dbman->drop_field($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsdropobserverfield, $continue = true, $feedback = true);
            }

            $tablelocalbatchingparticipantsaddusercreatedfield = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'batch');
            $tablelocalbatchingparticipantsaddusercreatedfield = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usercreated');
            $tablelocalbatchingparticipantsaddtimecreatedfield = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'usermodified');
            $tablelocalbatchingparticipantsaddtimemodifiedfield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timecreated');

            if (!$dbman->field_exists($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsaddusercreatedfield)) {
                $dbman->add_field($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsaddusercreatedfield, $continue = true, $feedback = true);
            }
            if (!$dbman->field_exists($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsaddusercreatedfield)) {
                $dbman->add_field($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsaddusercreatedfield, $continue = true, $feedback = true);
            }
            if (!$dbman->field_exists($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsaddtimecreatedfield)) {
                $dbman->add_field($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsaddtimecreatedfield, $continue = true, $feedback = true);
            }
            if (!$dbman->field_exists($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsaddtimemodifiedfield)) {
                $dbman->add_field($tablelocalbatchingparticipants, $tablelocalbatchingparticipantsaddtimemodifiedfield, $continue = true, $feedback = true);
            }

            $tablelocalbatchingparticipantschangedatatypeuserfield = new xmldb_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
            $table_local_batching_participants_changedatatype_batch_field = new xmldb_field('batch', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);

            if ($dbman->field_exists($tablelocalbatchingparticipants, $tablelocalbatchingparticipantschangedatatypeuserfield)) {
                $dbman->change_field_type($tablelocalbatchingparticipants, $tablelocalbatchingparticipantschangedatatypeuserfield, $continue = true, $feedback = true);
            }
            if ($dbman->field_exists($tablelocalbatchingparticipants, $table_local_batching_participants_changedatatype_batch_field)) {
                $dbman->change_field_type($tablelocalbatchingparticipants, $table_local_batching_participants_changedatatype_batch_field, $continue = true, $feedback = true);
            }
        }

        $tablelocalbatchingfilters = new xmldb_table('local_batching_filters');
        $tablelocalbatchingfiltersaddusercreatedfield = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'value');
        $tablelocalbatchingfiltersaddusermodifiedfield = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usercreated');
        $tablelocalbatchingfiltersaddtimecreatedfield = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usermodified');
        $tablelocalbatchingfiltersaddtimemodifiedfield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timecreated');

        if (!$dbman->field_exists($tablelocalbatchingfilters, $tablelocalbatchingfiltersaddusercreatedfield)) {
            $dbman->add_field($tablelocalbatchingfilters, $tablelocalbatchingfiltersaddusercreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingfilters, $tablelocalbatchingfiltersaddusermodifiedfield)) {
            $dbman->add_field($tablelocalbatchingfilters, $tablelocalbatchingfiltersaddusermodifiedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingfilters, $tablelocalbatchingfiltersaddtimecreatedfield)) {
            $dbman->add_field($tablelocalbatchingfilters, $tablelocalbatchingfiltersaddtimecreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingfilters, $tablelocalbatchingfiltersaddtimemodifiedfield)) {
            $dbman->add_field($tablelocalbatchingfilters, $tablelocalbatchingfiltersaddtimemodifiedfield, $continue = true, $feedback = true);
        }

        $tablelocalbatchingvenue = new xmldb_table('local_batching_venue');
        $tablelocalbatchingvenueaddusercreatedfield = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'status');
        $tablelocalbatchingvenueaddusermodifiedfield = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usercreated');
        $tablelocalbatchingvenueaddtimecreatedfield = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usermodified');
        $tablelocalbatchingvenueaddtimemodifiedfield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timecreated');

        if (!$dbman->field_exists($tablelocalbatchingvenue, $tablelocalbatchingvenueaddusercreatedfield)) {
            $dbman->add_field($tablelocalbatchingvenue, $tablelocalbatchingvenueaddusercreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingvenue, $tablelocalbatchingvenueaddusermodifiedfield)) {
            $dbman->add_field($tablelocalbatchingvenue, $tablelocalbatchingvenueaddusermodifiedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingvenue, $tablelocalbatchingvenueaddtimecreatedfield)) {
            $dbman->add_field($tablelocalbatchingvenue, $tablelocalbatchingvenueaddtimecreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingvenue, $tablelocalbatchingvenueaddtimemodifiedfield)) {
            $dbman->add_field($tablelocalbatchingvenue, $tablelocalbatchingvenueaddtimemodifiedfield, $continue = true, $feedback = true);
        }

        $tablelocalbatchingcycles = new xmldb_table('local_batching_cycles');
        $tablelocalbatchingcyclesaddusercreatedfield = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'status');
        $tablelocalbatchingcyclesaddusermodifiedfield = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usercreated');
        $tablelocalbatchingcyclesaddtimecreatedfield = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usermodified');
        $tablelocalbatchingcyclesaddtimemodifiedfield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timecreated');

        if (!$dbman->field_exists($tablelocalbatchingcycles, $tablelocalbatchingcyclesaddusercreatedfield)) {
            $dbman->add_field($tablelocalbatchingcycles, $tablelocalbatchingcyclesaddusercreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingcycles, $tablelocalbatchingcyclesaddusermodifiedfield)) {
            $dbman->add_field($tablelocalbatchingcycles, $tablelocalbatchingcyclesaddusermodifiedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingcycles, $tablelocalbatchingcyclesaddtimecreatedfield)) {
            $dbman->add_field($tablelocalbatchingcycles, $tablelocalbatchingcyclesaddtimecreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingcycles, $tablelocalbatchingcyclesaddtimemodifiedfield)) {
            $dbman->add_field($tablelocalbatchingcycles, $tablelocalbatchingcyclesaddtimemodifiedfield, $continue = true, $feedback = true);
        }

        $tablelocalbatchingbatches = new xmldb_table('local_batching_batches');
        $tablelocalbatchingbatchesaddusercreatedfield = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'status');
        $tablelocalbatchingbatchesaddusermodifiedfield = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usercreated');
        $tablelocalbatchingbatchesaddtimecreatedfield = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usermodified');
        $tablelocalbatchingbatchesaddtimemodifiedfield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timecreated');

        if (!$dbman->field_exists($tablelocalbatchingbatches, $tablelocalbatchingbatchesaddusercreatedfield)) {
            $dbman->add_field($tablelocalbatchingbatches, $tablelocalbatchingbatchesaddusercreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingbatches, $tablelocalbatchingbatchesaddusermodifiedfield)) {
            $dbman->add_field($tablelocalbatchingbatches, $tablelocalbatchingbatchesaddusermodifiedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingbatches, $tablelocalbatchingbatchesaddtimecreatedfield)) {
            $dbman->add_field($tablelocalbatchingbatches, $tablelocalbatchingbatchesaddtimecreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingbatches, $tablelocalbatchingbatchesaddtimemodifiedfield)) {
            $dbman->add_field($tablelocalbatchingbatches, $tablelocalbatchingbatchesaddtimemodifiedfield, $continue = true, $feedback = true);
        }

        $tablelocalbatchingfinancials = new xmldb_table('local_batching_financials');
        $tablelocalbatchingfinancialsaddusercreatedfield = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'unit');
        $tablelocalbatchingfinancialsaddusermodifiedfield = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usercreated');
        $tablelocalbatchingfinancialsaddtimecreatedfield = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usermodified');
        $tablelocalbatchingfinancialsaddtimemodifiedfield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timecreated');

        if (!$dbman->field_exists($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddusercreatedfield)) {
            $dbman->add_field($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddusercreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddusermodifiedfield)) {
            $dbman->add_field($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddusermodifiedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddtimecreatedfield)) {
            $dbman->add_field($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddtimecreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddtimemodifiedfield)) {
            $dbman->add_field($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddtimemodifiedfield, $continue = true, $feedback = true);
        }

        $tablelocalbatchingproposals = new xmldb_table('local_batching_proposals');
        $tablelocalbatchingproposalsaddusercreatedfield = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'status');
        $tablelocalbatchingproposalsaddusermodifiedfield = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usercreated');
        $tablelocalbatchingproposalsaddtimecreatedfield = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'usermodified');
        $tablelocalbatchingproposalsaddtimemodifiedfield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timecreated');

        if (!$dbman->field_exists($tablelocalbatchingproposals, $tablelocalbatchingproposalsaddusercreatedfield)) {
            $dbman->add_field($tablelocalbatchingproposals, $tablelocalbatchingproposalsaddusercreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingproposals, $tablelocalbatchingproposalsaddusermodifiedfield)) {
            $dbman->add_field($tablelocalbatchingproposals, $tablelocalbatchingproposalsaddusermodifiedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingproposals, $tablelocalbatchingproposalsaddtimecreatedfield)) {
            $dbman->add_field($tablelocalbatchingproposals, $tablelocalbatchingproposalsaddtimecreatedfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatchingproposals, $tablelocalbatchingproposalsaddtimemodifiedfield)) {
            $dbman->add_field($tablelocalbatchingproposals, $tablelocalbatchingproposalsaddtimemodifiedfield, $continue = true, $feedback = true);
        }

        $tablelocalbatchingfacilitators = new xmldb_table('local_batching_facilitators');
        $tablelocalbatchingfacilitators->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalbatchingfacilitators->add_field('batching', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingfacilitators->add_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingfacilitators->add_field('batch', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingfacilitators->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingfacilitators->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingfacilitators->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingfacilitators->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingfacilitators->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalbatchingfacilitators)) {
            $dbman->create_table($tablelocalbatchingfacilitators);
        }

        $tablelocalbatchingcoordinators = new xmldb_table('local_batching_coordinators');
        $tablelocalbatchingcoordinators->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalbatchingcoordinators->add_field('batching', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingcoordinators->add_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingcoordinators->add_field('batch', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingcoordinators->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingcoordinators->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingcoordinators->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingcoordinators->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingcoordinators->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalbatchingcoordinators)) {
            $dbman->create_table($tablelocalbatchingcoordinators);
        }

        $tablelocalbatchingobservers = new xmldb_table('local_batching_observers');
        $tablelocalbatchingobservers->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalbatchingobservers->add_field('batching', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingobservers->add_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingobservers->add_field('batch', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingobservers->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingobservers->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingobservers->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingobservers->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingobservers->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalbatchingobservers)) {
            $dbman->create_table($tablelocalbatchingobservers);
        }

        upgrade_plugin_savepoint($result, 2023041100, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023041200) {

        $tablelocalbatchingvenuefinal = new xmldb_table('local_batching_venue_final');
        $tablelocalbatchingvenuefinal->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalbatchingvenuefinal->add_field('batchingvenueid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingvenuefinal->add_field('batch', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingvenuefinal->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $tablelocalbatchingvenuefinal->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingvenuefinal->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,0);
        $tablelocalbatchingvenuefinal->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingvenuefinal->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalbatchingvenuefinal)) {
            $dbman->create_table($tablelocalbatchingvenuefinal);
        }

        $tablelocalbatchingbatches = new xmldb_table('local_batching_batches');
        $tablelocalbatchingbatchesdropvenuefield = new xmldb_field('venue');

        if ($dbman->field_exists($tablelocalbatchingbatches, $tablelocalbatchingbatchesdropvenuefield)) {
            $dbman->drop_field($tablelocalbatchingbatches, $tablelocalbatchingbatchesdropvenuefield, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023041200, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023041300) {

        $tablelocalbatchingfinancials = new xmldb_table('local_batching_financials');
        $tablelocalbatchingfinancialsrenamevaluefield = new xmldb_field('value',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);

        if ($dbman->field_exists($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsrenamevaluefield)) {
            $dbman->rename_field($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsrenamevaluefield, 'cost', $continue = true, $feedback = true);
        }

        $tablelocalbatchingfinancialschangedatatypeunitfield = new xmldb_field('unit', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);

        if ($dbman->field_exists($tablelocalbatchingfinancials, $tablelocalbatchingfinancialschangedatatypeunitfield)) {
            $dbman->change_field_type($tablelocalbatchingfinancials, $tablelocalbatchingfinancialschangedatatypeunitfield, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023041300, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023041400) {

        $tablelocalcycletimes = new xmldb_table('local_batching_cycle_times');
        $tablelocalcycletimes->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalcycletimes->add_field('cycle', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalcycletimes->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalcycletimes->add_field('endtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalcycletimes->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalcycletimes->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalcycletimes->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalcycletimes->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalcycletimes->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalcycletimes)) {
            $dbman->create_table($tablelocalcycletimes);
        }

        upgrade_plugin_savepoint($result, 2023041400, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023051301) {

        $tablelocalbatching = new xmldb_table('local_batching');
        $tablelocalbatchingadddietheadsfield = new xmldb_field('diet_heads', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'course');
        $tablelocalbatchingaddnodalofficersfield = new xmldb_field('nodal_officers', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'diet_heads');
        $tablelocalbatchingaddproposalfilefullversionfield = new xmldb_field('proposal_file_full_version', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'nodal_officers');
        $tablelocalbatchingaddproposalfilelimitedversionfield = new xmldb_field('proposal_file_limited_version', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'proposal_file_full_version');

        if (!$dbman->field_exists($tablelocalbatching, $tablelocalbatchingadddietheadsfield)) {
            $dbman->add_field($tablelocalbatching, $tablelocalbatchingadddietheadsfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatching, $tablelocalbatchingaddnodalofficersfield)) {
            $dbman->add_field($tablelocalbatching, $tablelocalbatchingaddnodalofficersfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatching, $tablelocalbatchingaddproposalfilefullversionfield)) {
            $dbman->add_field($tablelocalbatching, $tablelocalbatchingaddproposalfilefullversionfield, $continue = true, $feedback = true);
        }
        if (!$dbman->field_exists($tablelocalbatching, $tablelocalbatchingaddproposalfilelimitedversionfield)) {
            $dbman->add_field($tablelocalbatching, $tablelocalbatchingaddproposalfilelimitedversionfield, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023051301, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023051302) {
        $tablelocalfinancialcategories = new xmldb_table('local_financial_categories');
        $tablelocalfinancialcategories->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalfinancialcategories->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialcategories->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialcategories->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinancialcategories->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialcategories->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinancialcategories->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalfinancialcategories)) {
            $dbman->create_table($tablelocalfinancialcategories);
        }

        upgrade_plugin_savepoint($result, 2023051302, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023051303) {
        $localbatchingfinancials = new xmldb_table('local_batching_financials');
        $localbatchingfinancialsaddcategoryfield = new xmldb_field('category', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'batching');

        if (!$dbman->field_exists($localbatchingfinancials, $localbatchingfinancialsaddcategoryfield)) {
            $dbman->add_field($localbatchingfinancials, $localbatchingfinancialsaddcategoryfield, $continue = true, $feedback = true);
        }
        upgrade_plugin_savepoint($result, 2023051303, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023051500) {

        $tablelocalbatching = new xmldb_table('local_batching');
        $tablelocalbatchingproposalfilefullversionfield = new xmldb_field('proposal_file_full_version',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingproposalfilelimitedversionfield = new xmldb_field('proposal_file_limited_version',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);

        if ($dbman->field_exists($tablelocalbatching, $tablelocalbatchingproposalfilefullversionfield)) {
            $dbman->rename_field($tablelocalbatching, $tablelocalbatchingproposalfilefullversionfield, 'proposal_file', $continue = true, $feedback = true);
        }

        if ($dbman->field_exists($tablelocalbatching, $tablelocalbatchingproposalfilelimitedversionfield)) {
            $dbman->rename_field($tablelocalbatching, $tablelocalbatchingproposalfilelimitedversionfield, 'circular_file', $continue = true, $feedback = true);
        }
        upgrade_plugin_savepoint($result, 2023051500, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023051700) {

        $tablelocaltempusers = new xmldb_table('local_batching_temp_users');
        $tablelocaltempusers->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocaltempusers->add_field('batching', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocaltempusers->add_field('school', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocaltempusers->add_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocaltempusers->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocaltempusers)) {
            $dbman->create_table($tablelocaltempusers);
        }

        upgrade_plugin_savepoint($result, 2023051700, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023051801) {

        $tablelocalbatchingcycletimes = new xmldb_table('local_batching_cycle_times');
        $tablelocalbatchingcycletimesdayfield = new xmldb_field('day',XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'endtime');

        if (!$dbman->field_exists($tablelocalbatchingcycletimes, $tablelocalbatchingcycletimesdayfield)) {
            $dbman->add_field($tablelocalbatchingcycletimes, $tablelocalbatchingcycletimesdayfield, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023051801, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052000) {
        $tablelocalfinancialdetails = new xmldb_table('local_financial_details');
        $tablelocalfinancialdetails->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocalfinancialdetails->add_field('category', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdetails->add_field('sub_category', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdetails->add_field('dependenton', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdetails->add_field('fromvalue', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdetails->add_field('tovalue', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdetails->add_field('value', XMLDB_TYPE_FLOAT, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdetails->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdetails->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinancialdetails->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdetails->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinancialdetails->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalfinancialdetails)) {
            $dbman->create_table($tablelocalfinancialdetails);
        }

        $localfinancialcategories = new xmldb_table('local_financial_categories');
        $localfinancialcategoriesfield = new xmldb_field('code', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 0, 'name');

        if (!$dbman->field_exists($localfinancialcategories, $localfinancialcategoriesfield)) {
            $dbman->add_field($localfinancialcategories, $localfinancialcategoriesfield, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023052000, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052001) {

        $tablelocalfinancialgrades= new xmldb_table('local_financial_grades');
        $tablelocalfinancialgrades->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $tablelocalfinancialgrades->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialgrades->add_field('code', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialgrades->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialgrades->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinancialgrades->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialgrades->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinancialgrades->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalfinancialgrades)) {
            $dbman->create_table($tablelocalfinancialgrades);
        }

        upgrade_plugin_savepoint($result, 2023052001, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052002) {

        $tablelocalfinancialdependents = new xmldb_table('local_financial_dependents');
        $tablelocalfinancialdependents->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $tablelocalfinancialdependents->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdependents->add_field('code', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdependents->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdependents->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinancialdependents->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinancialdependents->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinancialdependents->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalfinancialdependents)) {
            $dbman->create_table($tablelocalfinancialdependents);
        }

        upgrade_plugin_savepoint($result, 2023052002, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052003) {

        $tablelocalbatchingfinancials = new xmldb_table('local_batching_financials');
        $tablelocalbatchingfinancialsrenamenamefield = new xmldb_field('name',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);

        if ($dbman->field_exists($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsrenamenamefield)) {
            $dbman->rename_field($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsrenamenamefield, 'title', $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023052003, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052200) {

        $tablelocalfinancialdetails = new xmldb_table('local_financial_details');

        $field = new xmldb_field('sub_category', XMLDB_TYPE_CHAR, '255', null, false, null, NULL, 'category');

        if ($dbman->field_exists($tablelocalfinancialdetails, $field)) {
            $dbman->change_field_default($tablelocalfinancialdetails, $field);
        }

        $field = new xmldb_field('dependenton', XMLDB_TYPE_CHAR, '255', null, false, null, NULL, 'sub_category');

        if ($dbman->field_exists($tablelocalfinancialdetails, $field)) {
            $dbman->change_field_default($tablelocalfinancialdetails, $field);
        }

        upgrade_plugin_savepoint($result, 2023052200, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052401) {

        $tablelocalbatchinglogs = new xmldb_table('local_batching_logs');
        $tablelocalbatchinglogs->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $tablelocalbatchinglogs->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('diet_heads', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('nodal_officers', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('proposal_file', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('circular_file', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('comment', XMLDB_TYPE_TEXT, '', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('section_number', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('status', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchinglogs->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchinglogs->add_field('updatedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchinglogs->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalbatchinglogs)) {
            $dbman->create_table($tablelocalbatchinglogs);
        }

        $tablelocalbatching = new xmldb_table('local_batching');
        $tablelocalbatchingaddcommentfield = new xmldb_field('comment', XMLDB_TYPE_TEXT, '', null, false, null, 0, 'circular_file');
        $tablelocalbatchingaddsectionnumberfield = new xmldb_field('section_number', XMLDB_TYPE_CHAR, '255', null, false, null, 0, 'comment');

        if (!$dbman->field_exists($tablelocalbatching, $tablelocalbatchingaddcommentfield)) {
            $dbman->add_field($tablelocalbatching, $tablelocalbatchingaddcommentfield, $continue = true, $feedback = true);
        }

        if (!$dbman->field_exists($tablelocalbatching, $tablelocalbatchingaddsectionnumberfield)) {
            $dbman->add_field($tablelocalbatching, $tablelocalbatchingaddsectionnumberfield, $continue = true, $feedback = true);
        }

        $tablelocalbatchingproposals = new xmldb_table('local_batching_proposals');

        if ($dbman->table_exists($tablelocalbatchingproposals)) {
            $dbman->drop_table($tablelocalbatchingproposals, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023052401, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052402) {

        $tablelocalbatchinglogs = new xmldb_table('local_batching_logs');
        $tablelocalbatchinglogsaddbatchingfield = new xmldb_field('batching', XMLDB_TYPE_INTEGER, '10', null, false, null, NULL, 'id');

        if (!$dbman->field_exists($tablelocalbatchinglogs, $tablelocalbatchinglogsaddbatchingfield)) {
            $dbman->add_field($tablelocalbatchinglogs, $tablelocalbatchinglogsaddbatchingfield, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023052402, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052403) {

        $tablelocalbatching = new xmldb_table('local_batching');
        $tablelocalbatchingrenamesectionnumberfield = new xmldb_field('section_number',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);

        if ($dbman->field_exists($tablelocalbatching, $tablelocalbatchingrenamesectionnumberfield)) {
            $dbman->rename_field($tablelocalbatching, $tablelocalbatchingrenamesectionnumberfield, 'sanction_number', $continue = true, $feedback = true);
        }

        $tablelocalbatchinglogs = new xmldb_table('local_batching_logs');
        $tablelocalbatchinglogsrenamesectionnumberfield = new xmldb_field('section_number',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);

        if ($dbman->field_exists($tablelocalbatchinglogs, $tablelocalbatchinglogsrenamesectionnumberfield)) {
            $dbman->rename_field($tablelocalbatchinglogs, $tablelocalbatchinglogsrenamesectionnumberfield, 'sanction_number', $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023052403, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052404) {

        $tablelocalbatchinglogs = new xmldb_table('local_batching_logs');

        $tablelocalbatchinglogsadddefaultvalueofcomment = new xmldb_field('comment', XMLDB_TYPE_CHAR, '', null, false, null, NULL);
        $tablelocalbatchinglogsadddefaultvalueofsanctionnumber = new xmldb_field('sanction_number', XMLDB_TYPE_CHAR, '255', null, false, null, NULL);

        if ($dbman->field_exists($tablelocalbatchinglogs, $tablelocalbatchinglogsadddefaultvalueofcomment)) {
            $dbman->change_field_default($tablelocalbatchinglogs, $tablelocalbatchinglogsadddefaultvalueofcomment);
        }

        if ($dbman->field_exists($tablelocalbatchinglogs, $tablelocalbatchinglogsadddefaultvalueofsanctionnumber)) {
            $dbman->change_field_default($tablelocalbatchinglogs, $tablelocalbatchinglogsadddefaultvalueofsanctionnumber);
        }

        upgrade_plugin_savepoint($result, 2023052404, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052500) {
        $tablelocalbatchingproposallogs = new xmldb_table('local_batching_proposal_logs');
        $tablelocalbatchingproposallogs->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $tablelocalbatchingproposallogs->add_field('batching', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingproposallogs->add_field('type', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingproposallogs->add_field('status', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingproposallogs->add_field('file', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $tablelocalbatchingproposallogs->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingproposallogs->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingproposallogs->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalbatchingproposallogs->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalbatchingproposallogs->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalbatchingproposallogs)) {
            $dbman->create_table($tablelocalbatchingproposallogs);
        }

        $tablelocalbatchingfinancials = new xmldb_table('local_batching_financials');
        $tablelocalbatchingfinancialsaddproposallogfield = new xmldb_field('proposallog', XMLDB_TYPE_INTEGER, '10', null, false, null, null, 'category');

        if (!$dbman->field_exists($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddproposallogfield)) {
            $dbman->add_field($tablelocalbatchingfinancials, $tablelocalbatchingfinancialsaddproposallogfield, $continue = true, $feedback = true);
        }
        upgrade_plugin_savepoint($result, 2023052500, 'local', 'batching');
    }


    if ($result && (int) $oldversion < 2023052700) {

        $tablelocalfinanciallunchtypes = new xmldb_table('local_financial_lunch_types');
        $tablelocalfinanciallunchtypes->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $tablelocalfinanciallunchtypes->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinanciallunchtypes->add_field('code', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $tablelocalfinanciallunchtypes->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinanciallunchtypes->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinanciallunchtypes->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocalfinanciallunchtypes->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocalfinanciallunchtypes->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocalfinanciallunchtypes)) {
            $dbman->create_table($tablelocalfinanciallunchtypes);
        }

        $tablelocalbatchingfinancials = new xmldb_table('local_batching_financials');

        $field = new xmldb_field('title', XMLDB_TYPE_TEXT, '', null, false, null, NULL, 'proposallog');
        if ($dbman->field_exists($tablelocalbatchingfinancials, $field)) {
            $dbman->change_field_default($tablelocalbatchingfinancials, $field);
        }

        upgrade_plugin_savepoint($result, 2023052700, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023052701) {

        $tablelocalfinancialdetails = new xmldb_table('local_financial_details');
        $tablelocalfinancialdetailsrenamenamefield = new xmldb_field('sub_category', XMLDB_TYPE_INTEGER, '10', null, false, null, null, '');

        if ($dbman->field_exists($tablelocalfinancialdetails, $tablelocalfinancialdetailsrenamenamefield)) {
            $dbman->rename_field($tablelocalfinancialdetails, $tablelocalfinancialdetailsrenamenamefield, 'grade', $continue = true, $feedback = true);
        }

        $tablelocalfinancialdetails = new xmldb_table('local_financial_details');
        $tablelocalfinancialdetailsaddlunchtypefield = new xmldb_field('lunchtype', XMLDB_TYPE_INTEGER, '10', null, false, null, null, 'grade');

        if (!$dbman->field_exists($tablelocalfinancialdetails, $tablelocalfinancialdetailsaddlunchtypefield)) {
            $dbman->add_field($tablelocalfinancialdetails, $tablelocalfinancialdetailsaddlunchtypefield, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023052701, 'local', 'batching');
    }

    if ($result && (int) $oldversion < 2023060201) {

        $tablelocalbatching = new xmldb_table('local_batching');
        $tablelocalbatchingrenamesanctionnumberfield = new xmldb_field('sanction_number',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);

        if ($dbman->field_exists($tablelocalbatching, $tablelocalbatchingrenamesanctionnumberfield)) {
            $dbman->rename_field($tablelocalbatching, $tablelocalbatchingrenamesanctionnumberfield, 'file_number', $continue = true, $feedback = true);
        }

        $tablelocalbatchinglogs = new xmldb_table('local_batching_logs');
        $tablelocalbatchinglogsrenamesanctionnumberfield = new xmldb_field('sanction_number',XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);

        if ($dbman->field_exists($tablelocalbatchinglogs, $tablelocalbatchinglogsrenamesanctionnumberfield)) {
            $dbman->rename_field($tablelocalbatchinglogs, $tablelocalbatchinglogsrenamesanctionnumberfield, 'file_number', $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023060201, 'local', 'batching');
    }

    return $result;
}
