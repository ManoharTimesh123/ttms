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
 * About Us
 *
 * @package    block_about_us
 * @author     Sangita Kumari
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

use block_about_us;
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

global $CFG;
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/blocks/moodleblock.class.php');
require_once($CFG->dirroot . '/blocks/about_us/block_about_us.php');

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/blocks/about_us/');
$PAGE->set_title(get_string('pluginname', 'block_about_us'));
$PAGE->set_heading(get_string('pluginname', 'block_about_us'));
$PAGE->set_pagelayout('standard');
echo $OUTPUT->header();

echo $OUTPUT->footer();

