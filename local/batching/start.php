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
 * The batching Management
 *
 * @package local_batching
 * @author  Tarun Upadhyay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/blocks/choose_trainings/renderer.php');

$systemcontext = context_system::instance();
require_login();

require_capability('local/batching:propose', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/local/batching/start.php');
$PAGE->set_title(get_string('pluginname', 'block_choose_trainings'));
$PAGE->set_heading(get_string('pluginname', 'block_choose_trainings'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
//echo render_block_choose_trainings();
echo $OUTPUT->footer();

