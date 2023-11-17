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
 *
 * @package      local
 * @subpackage   self_registration
 * @author Tarun Upadhyay
 *
 * @license      http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @Description  This Plugin is used to create,Update,Show and Delete n-level of Organisations and n-level of Departments.
 * This Plugin is capable to setup a environment to achive sigle site multiple organisation concept.
 * We can assign users any roles when we map them to department.We can assign courses and class-rooms etc..
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023062000;
$plugin->cron = 0;
$plugin->component = 'local_self_registration';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.1.2';
