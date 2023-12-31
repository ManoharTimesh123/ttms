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
 * The batching and cycling Management
 * @Package local_batching
 * @author Tarun Upadhyay
 * @Plugin Batching Management
 * @Description The plugin involves the management of batching and cycling.
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023060600;
$plugin->requires = 2016120500; // Moodle 3.2 required.
$plugin->component = 'local_batching';
$plugin->release = '1.1';
$plugin->maturity = MATURITY_STABLE;

