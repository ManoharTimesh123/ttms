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
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/news/classes/news.php');

function news_add_button($text, $url) {
    $adduserbutton = html_writer::start_tag('div');
    $adduserbutton .= html_writer::link($url, $text, array('class' => 'btn btn-primary float-right m-0 mb-3'));
    $adduserbutton .= html_writer::end_tag('div');
    return $adduserbutton;
}

function render_news() {
    global $PAGE, $CFG, $DB, $OUTPUT;

    $context = context_system::instance();
    $newsdata = \news\local_news::getnews([]);
    $table = new html_table();

    $tableheader = array(get_string('name', 'local_news'),
        get_string('image', 'local_news'),
        get_string('description', 'local_news'),
        get_string('school', 'local_news'),
        get_string('createdby', 'local_news'),
        get_string('datefrom', 'local_news'),
        get_string('dateto', 'local_news'),
        get_string('newstype', 'local_news'),
        get_string('status', 'local_news')
    );

    if (has_capability('local/news:approve', $context) ||
        has_capability('local/news:edit', $context) ||
        has_capability('local/news:delete', $context)
    ) {
        $tableheader[] = get_string('action', 'local_news');
    }

    $table->head = $tableheader;
    $data = [];

    $output = '';

    if (!empty($newsdata['data'])) {

        $newsdata = $newsdata['data'];

        foreach ($newsdata as $key => $news) {
            $newsstatus = \news\local_news::get_news_status($news);
            $imageurl = \news\local_news::getimageurl($news, $context->id);

            $row = array();
            $id = $news->id;
            $row[] = substr($news->title, 0, 30);
            $createdby = $DB->get_record('user', array('id' => $news->createdby));
            $row[] = '<img src="' . $imageurl . '" alt="">';
            $row[] = strip_html_tag_and_limit_character($news->description, 100);
            $row[] = $news->schoolname;
            $row[] = fullname($createdby);
            $row[] = customdateformat('DATE_WITHOUT_TIME', $news->datefrom);
            $row[] = customdateformat('DATE_WITHOUT_TIME', $news->dateto);
            if ($news->approved == 0) {
                $status = '<i class="fa fa-thumbs-down me-2 text-danger"></i>';
                $changestatustext = get_string('approve', 'local_news');
            } else {
                $status = '<i class="fa fa-thumbs-up me-2 text-success"></i>';
                $changestatustext = get_string('unapprove', 'local_news');
            }
            $row[] = '<span class="table-status ' . $newsstatus['statusclass'] . '">' . $newsstatus['newsstatustype'] . '</span>';
            $row[] = $status;
            $actionicons = '';
            if (has_capability('local/news:approve', $context)) {

                $changestatusurl = new moodle_url($CFG->wwwroot.'/local/news/edit.php', array('id' => $id, 'status' => 1));
                $actionicons .= '<a href="' . $changestatusurl . '">' . $changestatustext . '</a>';
            }
            if (has_capability('local/news:edit', $context)) {
                $editurl = new moodle_url($CFG->wwwroot . '/local/news/edit.php', array('id' => $id));
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

            if (has_capability('local/news:delete', $context)) {
                $deleteurl = new moodle_url($CFG->wwwroot . '/local/news/edit.php', array('id' => $id, 'delete' => 1));
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
        $table->id = 'news-list';
        $allnews = html_writer::table($table);
        $output .= '<div class="table-responsive">'. $allnews .'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#news-list').dataTable({
                                    'bSort': false,
                                });
                            });
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('datamissingerrormsg', 'local_news') . '</div>';
    }
    return $output;
}

function render_news_grid($page, $newsdata) {
    global $CFG;
    $context = context_system::instance();

    $output = '';
    if (!empty($newsdata)) {
        foreach ($newsdata as $key => $news) {
            $imageurl = \news\local_news::getimageurl($news, $context->id);

            $newsurl = new moodle_url($CFG->wwwroot . '/local/news', array('id' => $news->id));
            $posteddate = customdateformat('DATE_WITHOUT_TIME', $news->timecreated);
            $output .=
                <<<DATA
                <div class="col-md-4 ccn-blog-list-entry pb-4 mb-1">
                    <div class="main-blog-page box-shadow bg-white h-100">
                        <a href="$newsurl"><img class="img-fluid w-100" src="$imageurl" alt=""></a>
                        <a href="$newsurl"><h4 class="px-3 pt-3">$news->title</h4></a>
                        <span class="d-block px-3 pb-4">$posteddate</span>
                    </div>
                </div>
            DATA;
        }
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('datamissingerrormsg', 'local_news') . '</div>';
    }

    return $output;
}

function render_news_detail_grid($filters) {
    global $DB;

    $context = context_system::instance();
    $newsdata = \news\local_news::getnews($filters);

    if (!empty($newsdata['data'])) {
        $news = $newsdata['data'][$filters['id']];
        $createdby = $DB->get_record('user', array('id' => $news->createdby));
        $author = fullname($createdby);
        $newscreateddate = customdateformat('DAY', $news->timecreated);
        $newscreatedmonth = customdateformat('MONTH', $news->timecreated);
        $imageurl = \news\local_news::getimageurl($news, $context->id);
        $output =
            <<<DATA
            <div class="main-post-content bg-white">
                <div class="thumb position-relative overflow-hidden">
                    <img class="img-fluid w-100" src="$imageurl" alt="$news->title">
                    <div class="tag text-white position-absolute"></div>
                    <div class="post-date position-absolute">
                        <h2 class="display-4 text-white font-weight-bold m-0">$newscreateddate</h2>
                        <span class="font-weight-normal h4 text-white">$newscreatedmonth</span>
                    </div>
                </div>
                <div class="details rounded-lg p-3 px-4 box-shadow">
                    <h3 class="mb-1">$news->title</h3>
                    <h6 class="mb-3"><i class="fa fa-user mr-1" aria-hidden="true"></i> $author</h6>
                    <p>$news->description</p>
                </div>
            </div>
        DATA;
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('datamissingerrormsg', 'local_news') . '</div>';
    }

    return $output;
}

