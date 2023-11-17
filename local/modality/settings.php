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
 * @package local_modality
 * @author Nadia Farheen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2023 Moodle
 */
defined('MOODLE_INTERNAL') || die();

$ADMIN->add('root', new admin_category('local_modality', get_string('pluginname', 'local_modality', null, true)));

$page = new admin_externalpage('local_modality_index',
        'Modality',
        new moodle_url('/local/modality/index.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

// Create new external pagelist page.
$page = new admin_externalpage('local_modality_coursetype',
        get_string('coursetype', 'local_modality', null, true),
        new moodle_url('/local/modality/show_coursetype.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

$page = new admin_externalpage('local_modality_departments',
        get_string('departments', 'local_modality', null, true),
        new moodle_url('/local/modality/show_departments.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

$page = new admin_externalpage('local_modality_subjects',
        get_string('subjects', 'local_modality', null, true),
        new moodle_url('/local/modality/show_subjects.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

$page = new admin_externalpage('local_modality_districts',
        get_string('districts', 'local_modality', null, true),
        new moodle_url('/local/modality/show_districts.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

$page = new admin_externalpage('local_modality_diets',
        get_string('diets', 'local_modality', null, true),
        new moodle_url('/local/modality/show_diets.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

$page = new admin_externalpage('local_modality_zones',
        get_string('zones', 'local_modality', null, true),
        new moodle_url('/local/modality/show_zones.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

$page = new admin_externalpage('local_modality_schools',
        get_string('schools', 'local_modality', null, true),
        new moodle_url('/local/modality/show_schools.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

$page = new admin_externalpage('local_modality_schoolposition',
        get_string('schoolposition', 'local_modality', null, true),
        new moodle_url('/local/modality/show_school_positions.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

/* INTG Customization Start : adding User Overall Rating link in user management section*/
$page = new admin_externalpage('local_modality_course_ratings',
        get_string('user_overall_ratings', 'local_modality', null, true),
        new moodle_url('/local/modality/user_overall_ratings.php'),
        'moodle/site:configview');
$ADMIN->add('local_modality', $page);

/* INTG Customization End */
