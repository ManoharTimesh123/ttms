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
 * The blog Management
 * @package    local_blog
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/blog/renderer.php');
require_once($CFG->dirroot . '/local/blog/locallib.php');

require_login();

global $CFG;

$systemcontext = context_system::instance();
require_capability('local/blog:view', $systemcontext);
$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . '/local/blog/index.php');
$PAGE->set_title(get_string('pluginname', 'local_blog'));
$PAGE->set_heading(get_string('pluginname', 'local_blog'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

$filters = [
    'status' => 1,
    'manageown' => false,
];

if ($id > 0) {
    $filters['id'] = $id;
    echo $blogdetailgrid = render_blog_detail_grid($filters);
} else {
    $itemperpage = $CFG->itemperpage;
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    } else {
        $page = optional_param('page', 0, PARAM_INT);
    }

    $blogdata = \blog\local_blog::getblogpost($filters, $page);

    echo $bloggrid = render_blog_grid($blogdata['data']);

    echo $OUTPUT->paging_bar($blogdata['count'], $page, $itemperpage, new moodle_url('/local/blog'));
}

echo $OUTPUT->footer();
