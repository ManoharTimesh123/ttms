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
 * The Course Package
 *
 * @package    local_user_management
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Nadia Farheen Limited
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

 'local/user_management:manage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    'local/user_management:line_viewaccess' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM
    ),
    'local/user_management:asset_viewaccess' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM
    ),
    'local/user_management:hr_viewaccess' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM
    ),
    'local/user_management:hq_view' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM
    )
);

