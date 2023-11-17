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
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/blog/classes/blog.php');


function blog_add_button($text, $url) {
    global $CFG, $DB, $OUTPUT;
    $adduserbutton = html_writer::start_tag('div');
    $adduserbutton .= html_writer::link($url, $text, array('class' => 'btn btn-primary float-right m-0 mb-3'));
    $adduserbutton .= html_writer::end_tag('div');
    return $adduserbutton;
}

function render_blog() {
    global $PAGE, $CFG, $DB, $OUTPUT;
    $posts = '';

    $systemcontext = context_system::instance();

    $filters = [
        'manageown' => true
    ];

    $blogdata = \blog\local_blog::getblogpost($filters);
    $table = new html_table();

    $tableheader = array(get_string('name', 'local_blog'),
        get_string('image', 'local_blog'),
        get_string('description', 'local_blog'),
        get_string('createdby', 'local_blog'),
        get_string('status', 'local_blog')
    );

    if (has_capability('local/blog:delete', $systemcontext) ||
        has_capability('local/blog:approve', $systemcontext) ||
        has_capability('local/blog:edit', $systemcontext)
    ) {
        $tableheader[] = get_string('action', 'local_blog');
    }

    $table->head = $tableheader;
    $data = [];
    $output = '';

    if (!empty($blogdata['data'])) {
        $blogdata = $blogdata['data'];

        foreach ($blogdata as $key => $post) {
            $row = array();
            $id = $post->id;

            $row[] = substr($post->title, 0, 30);
            $imageurl = \blog\local_blog::getimageurl($post, $systemcontext->id);
            $row[] = '<img src="' . $imageurl . '" alt="">';
            $row[] = strip_html_tag_and_limit_character($post->description, 100);
            $createdby = $DB->get_record('user', array('id' => $post->usercreated));
            $row[] = fullname($createdby);

            if ($post->approved == 0) {
                $status = '<i class="fa fa-thumbs-down me-2 text-danger"></i>';
                $changestatustext = get_string('approve', 'local_blog');
            } else {
                $status = '<i class="fa fa-thumbs-up me-2 text-success"></i>';
                $changestatustext = get_string('uapprove', 'local_blog');
            }

            $row[] = $status;
            $actionicons = '';

            if (has_capability('local/blog:approve', $systemcontext)) {

                $changestatusurl = new moodle_url($CFG->wwwroot.'/local/blog/edit.php', array('id' => $id, 'status' => 1));
                $actionicons .= '<a href="' . $changestatusurl . '">' . $changestatustext . '</a>';
            }

            if (has_capability('local/blog:edit', $systemcontext)) {
                $editurl = new moodle_url($CFG->wwwroot . '/local/blog/edit.php', array('id' => $id));
                $actionicons .= html_writer::link(
                    $editurl,
                    html_writer::empty_tag('img', array(
                        'src' => $OUTPUT->image_url('i/edit'),
                        'title' => 'Edit',
                        'class' => 'iconsmall',
                        'width' => '14',
                        'height' => '14'
                    ))
                );
            }

            if (has_capability('local/blog:delete', $systemcontext)) {
                $deleteurl = new moodle_url($CFG->wwwroot . '/local/blog/edit.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link(
                    $deleteurl,
                    html_writer::empty_tag('img', array(
                        'src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete',
                        'class' => 'iconsmall',
                        'width' => '14',
                        'height' => '14'
                    ))
                );
            }

            if ($actionicons) {
                $row[] = $actionicons;
            }

            $data[] = $row;
        }
        $table->data = $data;
        $table->id = 'blog-list';
        $allblogs = html_writer::table($table);
        $output .= '<div class="table-responsive blog-table">'. $allblogs .'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#blog-list').dataTable({
                                    'bSort': false,
                                });
                            });
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('datamissingerrormsg', 'local_blog') . '</div>';
    }
    return $output;
}

function render_blog_grid($blogdata) {
    global $PAGE, $CFG, $DB, $OUTPUT;

    $context = context_system::instance();
    $output = '';

    if (!empty($blogdata)) {

        foreach ($blogdata as $key => $post) {
            $imageurl = \blog\local_blog::getimageurl($post, $context->id);

            $posturl = new moodle_url($CFG->wwwroot.'/local/blog', array('id' => $post->id));
            $posteddate = customdateformat('DATE_WITHOUT_TIME', $post->timecreated);
            $output .=
                <<<DATA
                <div class="col-md-4 ccn-blog-list-entry pb-4 mb-1">
                    <div class="main-blog-page box-shadow bg-white h-100">
                        <a href="$posturl"><img class="img-fluid w-100" src="$imageurl" alt=""></a>
                        <a href="$posturl"><h4 class="px-3 pt-3">$post->title</h4></a>
                        <span class="d-block px-3 pb-4">$posteddate</span>
                    </div>
                </div>
            DATA;
        }
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('datamissingerrormsg', 'local_blog') . '</div>';
    }

    return $output;
}

function render_blog_detail_grid($filters) {
    global $PAGE, $DB;
    $context = context_system::instance();

    $blogdata = \blog\local_blog::getblogpost($filters);
    if (!empty($blogdata['data'])) {
        $post = $blogdata['data'][$filters['id']];
        $createdby = $DB->get_record('user', array('id' => $post->usercreated));
        $postauthor = fullname($createdby);
        $postcreateddate = customdateformat('DAY', $post->timecreated);
        $postcreatedmonth = customdateformat('MONTH', $post->timecreated);
        $imageurl = \blog\local_blog::getimageurl($post, $context->id);

        $output =
            <<<DATA
            <div class="main-post-content bg-white">
                <div class="thumb position-relative overflow-hidden d-flex align-items-center">
                    <img class="img-fluid w-100" src="$imageurl" alt="$post->title">
                    <div class="tag text-white position-absolute"></div>
                    <div class="post-date position-absolute">
                        <h2 class="display-4 text-white font-weight-bold m-0">$postcreateddate</h2>
                        <span class="font-weight-normal h4 text-white">$postcreatedmonth</span>
                    </div>
                </div>
                <div class="details rounded-lg p-3 px-4 box-shadow">
                    <h3 class="mb-1">$post->title</h3>
                    <h6 class="mb-3"><i class="fa fa-user mr-1" aria-hidden="true"></i> $postauthor</h6>
                    <p>$post->description</p>
                </div>
            </div>
        DATA;
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('datamissingerrormsg', 'local_blog') . '</div>';
    }

    return $output;
}
