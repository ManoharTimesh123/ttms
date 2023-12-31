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

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$settings = new admin_settingpage('local_self_registration', new lang_string('pluginname', 'local_self_registration'));

$name = get_string('settingname', 'local_self_registration');
$title = get_string('settingtitle', 'local_self_registration');
$description = get_string('settingdescription', 'local_self_registration');
$setting = new admin_setting_configcheckbox($name, $title, $description, 0);
$settings->add($setting);

$ADMIN->add('localplugins', $settings);