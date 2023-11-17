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
 * Custom API Service API - version details.
 *
 * @package    local_customapi
 *
 * @global stdClass $plugin
 */

defined('MOODLE_INTERNAL') || die();


$plugin->version = 2023061301;
$plugin->requires = 2010112400;
$plugin->release = '0.1';
$plugin->maturity = MATURITY_STABLE;
$plugin->component = 'local_customapi';
$plugin->dependencies = [
];

