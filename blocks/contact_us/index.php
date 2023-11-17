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
 * Venue Requests
 *
 * @package    block_contact_us
 */

use block_contact_us;

// Load in Moodle config.
/*
    INTG Customization Start : This file is added for showing this block in a page.
*/
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/blocks/moodleblock.class.php');
require_once($CFG->dirroot . '/blocks/contact_us/block_contact_us.php');

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/blocks/contact_us/');
$PAGE->set_title(get_string('pluginname', 'block_contact_us'));
$PAGE->set_heading(get_string('pluginname', 'block_contact_us'));
$PAGE->set_pagelayout('standard');
echo $OUTPUT->header();

echo $OUTPUT->footer();
/*
    INTG Customization End
*/
