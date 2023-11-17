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
 * The News Management
 * @package    local_news
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/news/renderer.php');
require_once($CFG->dirroot . '/local/news/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Modality id.
$agree = optional_param('agree', 0, PARAM_INT);
$changestatus = optional_param('status', 0, PARAM_INT);

require_login();

$systemcontext = context_system::instance();

if ( !has_capability('local/news:manage', $systemcontext) && !has_capability('local/news:manageown', $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermissions', 'local_news'));
}

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot.'/local/news/listing.php');
$PAGE->set_title(get_string('pluginname', 'local_news'));
$PAGE->set_heading(get_string('pluginname', 'local_news'));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/news/js/jquery.dataTables.min.js'), true);
$PAGE->requires->css(new moodle_url('/local/news/css/jquery.dataTables.min.css'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

if ($agree == 1) {
    delete_news($id);
}
if ($changestatus == 1) {
    change_news_status($id);
}

$baseurl = new moodle_url($CFG->wwwroot . '/local/news/listing.php');
$text = get_string('createnews', 'local_news');
$url = $CFG->wwwroot . '/local/news/edit.php';

if (has_capability('local/news:add', $systemcontext)) {
    echo news_add_button($text, $url);
}
echo render_news();

echo $OUTPUT->footer();

