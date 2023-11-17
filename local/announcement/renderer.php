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
 * @package    local_announcement
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/announcement/classes/announcement.php');


function announcement_create_button($text, $url) {
    $adduserbutton = html_writer::start_tag('div');
    $adduserbutton .= html_writer::link($url, $text, array('class' => 'btn btn-primary float-right m-0 mb-3'));
    $adduserbutton .= html_writer::end_tag('div');

    return $adduserbutton;
}

function render_announcement() {
    global $PAGE, $CFG, $DB, $OUTPUT;

    $systemcontext = context_system::instance();
    $announcementdata = \announcement\local_announcement::getannouncements([]);
    $table = new html_table();

    $tableheader = array(get_string('name', 'local_announcement'),
        get_string('image', 'local_announcement'),
        get_string('description', 'local_announcement'),
        get_string('createdby', 'local_announcement'),
        get_string('announcementvisiblefrom', 'local_announcement'),
        get_string('announcementvisibleto', 'local_announcement'),
        get_string('status', 'local_announcement'),
        get_string('type', 'local_announcement'),
        get_string('action', 'local_announcement')
    );

    $table->head = $tableheader;
    $data = [];
    $output = '';

    if (!empty($announcementdata['data'])) {
        foreach ($announcementdata['data'] as $key => $announcement) {
            $announcementstatus = \announcement\local_announcement::get_announcement_status($announcement);

            $row = array();
            $imageurl = \announcement\local_announcement::getimageurl($announcement, $systemcontext->id);
            $id = $announcement->id;

            $row[] = substr($announcement->title, 0, 30);
            $createdby = $DB->get_record('user', array('id' => $announcement->createdby));
            $row[] = '<img src="' . $imageurl . '" alt="">';
            $row[] = strip_html_tag_and_limit_character($announcement->description, 100);
            $row[] = fullname($createdby);
            $row[] = customdateformat('DATE_WITHOUT_TIME', $announcement->startdate);
            $row[] = customdateformat('DATE_WITHOUT_TIME', $announcement->enddate);
            $status = ($announcement->global == 1) ? 'Global' : 'Targeted';
            $row[] = '<span class="table-status ' . $announcementstatus['statusclass'] . '">' . $announcementstatus['announcementstatustype'] . '</span>';;
            $row[] = $status;

            $actionicons = '';
            $editurl = new moodle_url($CFG->wwwroot . '/local/announcement/edit.php', array('id' => $id));

            if (has_capability('local/announcement:add', $systemcontext) || is_siteadmin()) {
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

            if (has_capability('local/announcement:delete', $systemcontext) || is_siteadmin()) {
                $deleteurl = new moodle_url($CFG->wwwroot . '/local/announcement/edit.php', array('id' => $id, 'delete' => 1));
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
            $row[] = $actionicons;

            $data[] = $row;
        }

        $table->data = $data;
        $table->id = 'announcement-list';
        $allannouncements = html_writer::table($table);
        $output .= '<div class="table-responsive">'. $allannouncements .'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#announcement-list').dataTable({
                                    'bSort': false,
                                });
                            });
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' .
                    get_string('nodataavailable', 'local_announcement') .
                '</div>';
    }
    return $output;
}

function render_announcement_grid($announcementdata) {
    global $CFG;

    $context = context_system::instance();
    $announcements = $announcementdata;
    $output = '';

    if (!empty($announcementdata)) {
        foreach ($announcements as $key => $announcement) {
            $imageurl = \announcement\local_announcement::getimageurl($announcement, $context->id);

            $announcementurl = new moodle_url($CFG->wwwroot . '/local/announcement', array('id' => $announcement->id));
            $posteddate = customdateformat('DATE_WITHOUT_TIME', $announcement->timecreated);
            $output .=
                <<<DATA
                <div class="col-md-4 ccn-blog-list-entry pb-4 mb-1">
                    <div class="main-blog-page box-shadow bg-white h-100">
                        <a href="$announcementurl"><img class="img-fluid w-100" src="$imageurl" alt=""></a>
                        <a href="$announcementurl"><h4 class="px-3 pt-3">$announcement->title</h4></a>
                        <span class="d-block px-3 pb-4">$posteddate</span>
                    </div>
                </div>
            DATA;
        }
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' .
                    get_string('nodataavailable', 'local_announcement') .
                '</div>';
    }

    return $output;
}

function render_announcement_detail_grid($filters) {
    global $DB;

    $context = context_system::instance();
    $announcementdata = \announcement\local_announcement::getannouncements($filters);

    if (!empty($announcementdata['data'])) {
        $announcement = $announcementdata['data'][$filters['id']];
        $createdby = $DB->get_record('user', array('id' => $announcement->createdby));
        $author = fullname($createdby);
        $announcementcreateddate = customdateformat('DAY', $announcement->timecreated);
        $announcementcreatedmonth = customdateformat('MONTH', $announcement->timecreated);
        $imageurl = \announcement\local_announcement::getimageurl($announcement, $context->id);
        $output =
            <<<DATA
            <div class="main-post-content bg-white">
                <div class="thumb position-relative overflow-hidden">
                    <img class="img-fluid w-100" src="$imageurl" alt="$announcement->title">
                    <div class="tag text-white position-absolute"></div>
                    <div class="post-date position-absolute">
                        <h2 class="display-4 text-white font-weight-bold m-0">$announcementcreateddate</h2>
                        <span class="font-weight-normal h4 text-white">$announcementcreatedmonth</span>
                    </div>
                </div>
                <div class="details rounded-lg p-3 px-4 box-shadow">
                    <h3 class="mb-1">$announcement->title</h3>
                    <h6 class="mb-3"><i class="fa fa-user mr-1" aria-hidden="true"></i> $author</h6>
                    <p>$announcement->description</p>
                </div>
            </div>
        DATA;
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' .
                    get_string('nodataavailable', 'local_announcement') .
                '</div>';
    }

    return $output;
}

