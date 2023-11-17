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
 * Announcement
 * @package local_announcement
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/announcement/renderer.php');
require_once($CFG->dirroot . '/local/announcement/locallib.php');

$id = optional_param('id', 0, PARAM_INT);
$agree = optional_param('agree', 0, PARAM_INT);

$systemcontext = context_system::instance();
require_login();

if ( !has_capability('local/announcement:manage', $systemcontext) && !is_siteadmin()) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermission', 'local_announcement'));
}
$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/local/announcement/listing.php');
$PAGE->set_title(get_string('pluginname', 'local_announcement'));
$PAGE->set_heading(get_string('pluginname', 'local_announcement'));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/announcement/js/jquery.dataTables.min.js'), true);
$PAGE->requires->css(new moodle_url('/local/announcement/css/jquery.dataTables.min.css'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

if ($agree == 1) {
    delete_announcement($id);
}


$baseurl = new moodle_url($CFG->wwwroot . '/local/announcement/listing.php');
$text = get_string('createannouncement', 'local_announcement');
$url = $CFG->wwwroot . '/local/announcement/edit.php';

if (has_capability('local/announcement:add', $systemcontext) || is_siteadmin()) {
    echo announcement_create_button($text, $url);
}

echo  render_announcement();

echo $OUTPUT->footer();

