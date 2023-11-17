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
 * The modality Management
 *
 * @Package local_modality
 * @Plugin Modality Management
 * @Description The plugin involves the management of the delivery mechanism types for the course.
 * It allows us to create, edit and delete a modality.
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023060801;
$plugin->requires = 2016120500; // Moodle 3.2 required.
$plugin->component = 'local_modality';
$plugin->release = '1.1';
$plugin->maturity = MATURITY_STABLE;
