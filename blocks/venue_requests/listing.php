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
 * @package    block_venue_requests
 */

// Load in Moodle config.
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/blocks/venue_requests/renderer.php');

require_login();

$systemcontext = context_system::instance();
require_capability('local/modality:venueapprove', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/blocks/venue_requests/listing.php');
$PAGE->set_title(get_string('pluginname', 'block_venue_requests'));
$PAGE->set_heading(get_string('pluginname', 'block_venue_requests'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/blocks/venue_requests/js/jquery.dataTables.min.js'), true);
$PAGE->requires->css(new moodle_url('/blocks/venue_requests/css/jquery.dataTables.min.css'));
$PAGE->requires->js(new moodle_url('/blocks/venue_requests/js/venue_request_custom.js'));

echo $OUTPUT->header();
echo render_venue_approval_requests_grid();
echo $OUTPUT->footer();
