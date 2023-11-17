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
 * @package    local_blog
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/news/renderer.php');
require_once($CFG->dirroot . '/local/news/locallib.php');

require_login();

global $CFG;

$systemcontext = context_system::instance();
require_capability('local/news:view', $systemcontext);

$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/local/news/index.php');
$PAGE->set_title(get_string('pluginname', 'local_news'));
$PAGE->set_heading(get_string('pluginname', 'local_news'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

$filters = [
    'status' => 1,
    'current_news' => true,
];

if ($id > 0) {
    $filters['id'] = $id;
    echo $newsgrid = render_news_detail_grid($filters);
} else {
    $itemperpage = $CFG->itemperpage;
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    } else {
        $page = optional_param('page', 0, PARAM_INT);
    }

    $newsdata = \news\local_news::getnews($filters, $page);

    echo $newsgrid = render_news_grid($page, $newsdata['data']);
    echo $OUTPUT->paging_bar($newsdata['count'], $page, $itemperpage, new moodle_url('/local/news'));
}

echo $OUTPUT->footer();
