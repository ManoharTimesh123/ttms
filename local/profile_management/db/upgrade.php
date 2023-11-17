<?php
// This file is part of the Contact Form plugin for Moodle - http://moodle.org/
//
// Contact Form is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Contact Form is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Contact Form.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This plugin for Moodle is used to send emails through a web form.
 *
 * @package    local_profile_management
 * @copyright  2016-2019 TNG Consulting Inc. - www.tngconsulting.ca
 * @author     Michael Milette
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * Upgrade code for the Contact Form local plugin.
  *
  * @param int $oldversion - the version we are upgrading from.
  * @return bool result
  */

function xmldb_local_profile_management_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    $result = true;

    if ($result && (int) $oldversion < 2023050102) {

        $tablelocaluserprofiledetails = new xmldb_table('local_user_profile_details');
        $tablelocaluserprofiledetails->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,  null, null);
        $tablelocaluserprofiledetails->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocaluserprofiledetails->add_field('changeditem', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null);
        $tablelocaluserprofiledetails->add_field('itemvalue', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null);
        $tablelocaluserprofiledetails->add_field('fromdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocaluserprofiledetails->add_field('todate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocaluserprofiledetails->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocaluserprofiledetails->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $tablelocaluserprofiledetails->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablelocaluserprofiledetails->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($tablelocaluserprofiledetails)) {
            $dbman->create_table($tablelocaluserprofiledetails);
        }

        upgrade_plugin_savepoint($result, 2023050102, 'local', 'profile_management');
    }

    if ($result && (int) $oldversion < 2023062000) {
        $tablelocaluserprofiledetails = new xmldb_table('local_user_profile_details');
        $tablelocaluserprofiledetailsaddfielddeleted = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NULL, null, 0, 'todate');
        $tablelocaluserprofiledetailsaddfielddusereleted = new xmldb_field('userdeleted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NULL, null, 0, 'deleted');

        if (!$dbman->field_exists($tablelocaluserprofiledetails, $tablelocaluserprofiledetailsaddfielddeleted)) {
            $dbman->add_field($tablelocaluserprofiledetails, $tablelocaluserprofiledetailsaddfielddeleted, $continue = true, $feedback = true);
        }

        if (!$dbman->field_exists($tablelocaluserprofiledetails, $tablelocaluserprofiledetailsaddfielddusereleted)) {
            $dbman->add_field($tablelocaluserprofiledetails, $tablelocaluserprofiledetailsaddfielddusereleted, $continue = true, $feedback = true);
        }

        upgrade_plugin_savepoint($result, 2023062000, 'local', 'profile_management');
    }

    return $result;
}
