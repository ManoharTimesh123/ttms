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
 * Questionnaire version information.
 *
 * @package mod_questionnaire
 * @author  Mike Churchward
 * @copyright  2016 Mike Churchward (mike.churchward@poetopensource.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
/* INTG Customization Start : Increase version for creating fields and table using upgrade.php file*/
$plugin->version = 2023050900;  // The current module version (Date: YYYYMMDDXX).
/* INTG Customization End */
$plugin->requires = 2020061500; // Moodle version (3.9).

$plugin->component = 'mod_questionnaire';

$plugin->release = '3.11.2 (Build - 2023030700)';
$plugin->maturity = MATURITY_STABLE;