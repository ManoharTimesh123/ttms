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
 * The Wall Management
 * @package    local_wall
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/wall/renderer.php');
require_once($CFG->dirroot . '/local/wall/locallib.php');
$id = optional_param('id', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();
require_capability('local/wall:view', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname', 'local_wall'));
$PAGE->set_heading(get_string('pluginname', 'local_wall'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

echo  render_post_detail_grid($id);

echo $OUTPUT->footer();
