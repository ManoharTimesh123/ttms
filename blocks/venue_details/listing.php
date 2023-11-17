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
 * Venue Details
 *
 * @package    block_venue_details
 */

// Load in Moodle config.

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login();

global $PAGE, $CFG, $OUTPUT;

require_once($CFG->dirroot . '/blocks/venue_details/renderer.php');

$systemcontext = context_system::instance();

$venuedetailfilter = optional_param('venue_detail', 'all', PARAM_TEXT);

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/blocks/venue_details/listing.php');
$PAGE->set_title(get_string('pluginname', 'block_venue_details'));
$PAGE->set_heading(get_string('pluginname', 'block_venue_details'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/blocks/venue_details/js/jquery.dataTables.min.js'), true);
$PAGE->requires->css(new moodle_url('/blocks/venue_details/css/jquery.dataTables.min.css'));
$PAGE->requires->js(new moodle_url('/blocks/venue_details/js/venue_details_custom.js'));


echo $OUTPUT->header();

echo render_venue_details_grid($venuedetailfilter);

echo $OUTPUT->footer();
