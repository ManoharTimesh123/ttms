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
 * Course Catalog
 *
 * @package    block_course_catalog
 */

// Load in Moodle config.
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/blocks/course_catalog/renderer.php');

$systemcontext = context_system::instance();


$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/blocks/course_catalog/listing.php');
$PAGE->set_title(get_string('pluginname', 'block_course_catalog'));
$PAGE->set_heading(get_string('pluginname', 'block_course_catalog'));
$PAGE->set_pagelayout('standard');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/blocks/course_catalog/js/course_catalog_custom.js'));

echo $OUTPUT->header();
echo render_course_catalog_grid();
echo $OUTPUT->footer();
