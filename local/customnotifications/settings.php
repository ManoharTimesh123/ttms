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

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('root', new admin_category('local_customnotifications', get_string('pluginname', 'local_customnotifications', null,
            true)));

// Create new external pagelist page.
$page = new admin_externalpage('local_customnotifications_index',
        get_string('templates', 'local_customnotifications', null, true),
        new moodle_url('/local/customnotifications/index.php', array('id' => 0)),
        'moodle/site:configview');

// Add pagelist page to navigation category.
$ADMIN->add('local_customnotifications', $page);

// Create new external pagelist page.
$page = new admin_externalpage('local_customnotifications_add',
        get_string('addtemplate', 'local_customnotifications', null, true),
        new moodle_url('/local/customnotifications/template.php', array('id' => 0)),
        'moodle/site:configview');

// Add pagelist page to navigation category.
$ADMIN->add('local_customnotifications', $page);

 // Create new settings page.
$page = new admin_settingpage('local_customnotifications_smsconfig',
            get_string('smsconfigure', 'local_customnotifications'));

if ($ADMIN->fulltree) {
    $page->add(new admin_setting_configtext('local_customnotifications/sms_endpoint',
                get_string('sms_endpoint', 'local_customnotifications'), 'https://sms.gov.in/failsafe/MLink?',
                    'https://sms.gov.in/failsafe/MLink?', PARAM_RAW));
    $page->add(new admin_setting_configtext('local_customnotifications/sms_username',
        get_string('sms_username', 'local_customnotifications'), '', 'sms', PARAM_RAW));
     $page->add(new admin_setting_configtext('local_customnotifications/sms_signature',
        get_string('sms_signature', 'local_customnotifications'), '', 'SIGNATURE', PARAM_RAW));
    $page->add(new admin_setting_configpasswordunmask('local_customnotifications/sms_pin',
        get_string('sms_pin', 'local_customnotifications'), 'PIN', 'PIN', PARAM_RAW));
    $page->add(new admin_setting_configtext('local_customnotifications/sms_dlt_entity_id',
        get_string('sms_dlt_entity_id', 'local_customnotifications'), '', '1234567890', PARAM_RAW));
    $page->add(new admin_setting_configcheckbox('local_customnotifications/enablesms',
                get_string('enablesms', 'local_customnotifications'), '', 1));

}

// Add pagelist page to navigation category.
$ADMIN->add('local_customnotifications', $page);
