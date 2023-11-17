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
 * The Wall Management
 * @package local_wall
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/wall/classes/post.php');


function add_button($text, $url) {
    $adduserbutton = html_writer::start_tag('div');
    $adduserbutton .= html_writer::link($url, $text, array('class' => 'btn btn-primary float-right m-0 mb-3'));
    $adduserbutton .= html_writer::end_tag('div');
    return $adduserbutton;
}

function render_all_post() {
    global $CFG, $DB, $OUTPUT;

    $context = context_system::instance();
    $posts = \post\local_wall::listallposts();
    $table = new html_table();

    $tableheader = array(get_string('post', 'local_wall'),
        get_string('uploadedfile', 'local_wall'),
        get_string('postedby', 'local_wall'),
        get_string('status', 'local_wall')
    );

    if (has_capability('local/wall:approve', $context) ||
        has_capability('local/wall:edit', $context) ||
        has_capability('local/wall:delete', $context)
    ) {
        $tableheader[] = get_string('action', 'local_wall');
    }

    $table->head = $tableheader;
    $data = [];
    $output = '';
    if (!empty($posts)) {
        foreach ($posts as $key => $post) {
            $id = $post->id;
            $fileurl = \post\local_wall::getuploadedfileurl($id, $context->id);
            if (isset($fileurl) && !empty($fileurl)) {
                $fileurl = $fileurl;
            } else {
                $fileurl = '';
            }
            $row = array();
            $row[] = strip_html_tag_and_limit_character($post->description, 100);
            $createdby = $DB->get_record('user', array('id' => $post->createdby));
            $row[] = '<div class="wall_post_file">' . $fileurl . '</div>';
            $row[] = fullname($createdby);
            if ($post->approved == 0) {
                $status = '<i class="fa fa-thumbs-down me-2 text-danger"></i>';
                $changestatustext = 'Approve';
            } else {
                $status = '<i class="fa fa-thumbs-up me-2 text-success"></i>';
                $changestatustext = get_string('unapprove', 'local_wall');
            }
            $row[] = $status;
            $actionicons = '';
            if (has_capability('local/wall:approve', $context)) {

                $changestatusurl = new moodle_url($CFG->wwwroot . '/local/wall/action.php', array('id' => $id, 'status' => 1));
                $actionicons .= html_writer::link($changestatusurl, $changestatustext);
            }

            if (has_capability('local/wall:edit', $context)) {

                $editurl = new moodle_url($CFG->wwwroot . '/local/wall/edit.php', array('id' => $id));
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

            if (has_capability('local/wall:delete', $context)) {
                $deleteurl = new moodle_url($CFG->wwwroot. '/local/wall/action.php', array('id' => $id, 'delete' => 1));
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
        $table->id = 'post-list';
        $allpost = html_writer::table($table);
        $output .= '<div class="table-responsive">'. $allpost .'</div>';
        $output .= html_writer::script("$(document).ready(function() {
                                $('#post-list').dataTable({
                                    'bSort': false,
                                });
                            });
                        ");
    } else {
        $output = '<div class="alert alert-info w-100 float-left">' . get_string('datamissingerrormsg', 'local_wall'). '</div>';
    }
    return $output;
}

function render_approve_post_grid($courseid) {
    global $CFG, $USER;

    $loggedinuserid = $USER->id;
    $context = \context_system::instance();
    $postsdata = \post\local_wall::approvedposts($courseid);
    $editor = \editors_get_preferred_editor();
    $dateformat = ($CFG->dateformat) ? $CFG->dateformat : 'F j, Y, g:i a';

    $outputhtml = '<div class="container category-wall-page">
                   <div class="bg-white box-shadow pt-4 px-4 rounded">
                   <div class="row">';
    foreach ($postsdata as $courseid => $posts) {

        $course = get_course($courseid);

        foreach ($posts as $post) {

            $postid = $post['id'];

            $postdetailurl = new moodle_url($CFG->wwwroot . '/local/wall/detail.php', array('id' => $postid));

            $fileurl = \post\local_wall::getuploadedfileurl($postid, $context->id);

            if (isset($fileurl) && !empty($fileurl)) {
                $fileurl = $fileurl;
            } else {
                $fileurl = '';
            }

            $thumbclass = 'fa-heart-o unlike';
            $postlikes = '';
            if (isset($post['post_vote']) &&  !empty($post['post_vote'])) {
                $postlikes = count($post['post_vote']);
                foreach ($post['post_vote'] as $vote) {
                    if ($vote->createdby == $USER->id && $vote->postlike == 1) {
                        $thumbclass = 'fa-heart';
                    }
                }
            }
            $sharecount = '';

            if (isset($post['post_share']) &&  !empty($post['post_share'])) {
                $sharecount = count($post['post_share']);
            }

            $commentcount = '';
            $deletecomment = '';

            if (isset($post['post_comment']) &&  !empty($post['post_comment'])) {

                $commentcount = count($post['post_comment']);

                foreach ($post['post_comment'] as $comment) {
                    $commentdate = customdateformat('DATE_WITH_TIME', $comment->timecreated);
                    $usercontext = \context_user::instance($comment->createdby);
                    $profilepicture = \post\local_wall::getuserprofilepictureurl($comment->createdby, $usercontext);
                }
            }

            $posteddate = customdateformat('DATE_WITH_TIME', $post['created_date']);
            $usercontext = \context_user::instance($post['user_id']);
            $profilepicture = \post\local_wall::getuserprofilepictureurl($post['user_id'], $usercontext);
            $outputhtml .= '
                    <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm h-100">
                      <div class="post-image">'.$fileurl.'</div>
                      <div class="card-body d-flex flex-column justify-content-between">
                       <div>
                        <h5 class="card-title"> <img class="rounded-circle shadow-1-strong mr-2"
                                src="' . $profilepicture . '" alt="avatar" width="40"
                                height="40" />' . $post['post_added_by'] . '</h5>
                               <p class="text-muted small mb-0">'. $posteddate .'</p>
                        <p class="card-text">'. $post['post_content'] .'</p>
                        <h4 class="card-title">'. $course->fullname .'</h4>
                        </div>
                        <div>
                       <a href="' . $postdetailurl . '" class="btn btn-outline-success btn-sm">Read Comments</a>
                        <a class="btn border-danger btn-sm cursor-auto" title=""><i class="fa fa-heart-o unlike mr-1"></i>  ' . $postlikes . '</a>
                        <a class="btn border-danger btn-sm cursor-auto"><i class="fa fa-commenting mr-1"></i>  ' . $commentcount . '</a>
                        <a class="btn border-danger btn-sm cursor-auto"><i class="flaticon-share mr-1"></i>  ' . $sharecount . '</a>
                        </div>
                      </div>
                     </div>
                    </div>';
        }
    }

    $outputhtml .= '</div></div></div>';

    return $outputhtml;
}

function render_post_detail_grid($postid) {
    global $PAGE, $DB, $CFG;
    $context = context_system::instance();
    $post = \post\local_wall::listpostdetail($postid);
    if (!$post) {
        return false;
    }
    $dateformat = ($CFG->dateformat) ? $CFG->dateformat : 'F j, Y, g:i a';

    $fileurl = \post\local_wall::getuploadedfileurl($postid, $context->id);
    if (isset($fileurl) && !empty($fileurl)) {
        $fileurl = $fileurl;
    } else {
        $fileurl = '';
    }
    $createdby = $DB->get_record('user', array('id' => $post->createdby));
    $postauthor = fullname($createdby);
    $commenthtml = '';
    $commentcount = 0;

    $postlikescount = 0;

    if (isset($post->post_vote) &&  !empty($post->post_vote)) {
        $postlikescount = count($post->post_vote);
    }

    $sharecount = 0;

    if (isset($post->post_share) &&  !empty($post->post_share)) {
        $sharecount = count($post->post_share);
    }
    $posteddate = customdateformat('DATE_WITH_TIME', $post->timecreated);
    if (isset($post->post_comment) &&  !empty($post->post_comment)) {

        foreach ($post->post_comment as $comment) {

            $commentdate = customdateformat('DATE_WITH_TIME', $comment->timecreated);
            $usercontext = \context_user::instance($comment->createdby);
            $commentcount = count($post->post_comment);
            $profilepicture = \post\local_wall::getuserprofilepictureurl($comment->createdby, $usercontext);

            $commenthtml .= '<div class="mb-3 user-comments">
                              <div>
                                 <div class="d-flex flex-start mt-2">
                                    <img class="rounded-circle shadow-1-strong mr-3" src="' . $profilepicture . '" alt="avatar" width="40" height="40">
                                    <div class="w-100 commnet-box-bg">
                                       <div class="d-flex justify-content-between align-items-center mb-0">
                                          <h6 class="text-primary fw-bold mb-0">
                                            ' . $comment->commented_by . '
                                          </h6>
                                          ' . $comment->description . '
                                          <p class="mb-0">
                                             ' . $commentdate . '
                                          </p>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                            </div>';
        }
    }
    $output =
        <<<DATA
        <div class="bg-white box-shadow pt-4 px-4 rounded wall-full-details"><h3 class="border-bottom mb-4 pb-2">Art Education Health</h3>
            <div class="row d-flex justify-content-center wall-posts card p-2 mb-3">
               <div class="d-flex flex-start align-items-center">
                  <img class="rounded-circle shadow-1-strong mr-2" src="http://moodle.test:5080/user/pix.php/270/f1.jpg" alt="avatar" width="60" height="60">
                  <div>
                     <h4 class="fw-bold text-dark mb-1">$postauthor</h4>
                     <p class="text-muted small mb-0">
                        $posteddate
                     </p>
                  </div>
               </div>
               <div class="wall-post-image mt-3 mb-2">$fileurl</div>
               $post->description
               <div class="float-right my-2">
                    <a class="btn border-danger btn-sm cursor-auto" title=""><i class="fa fa-heart-o unlike mr-1"></i> $postlikescount</a>
                    <a class="btn border-danger btn-sm cursor-auto"><i class="fa fa-commenting mr-1"></i> $commentcount</a>
                    <a class="btn border-danger btn-sm cursor-auto"><i class="flaticon-share mr-1"></i> $sharecount</a>
               </div>
              $commenthtml
            </div>
            </div>
        DATA;
    return $output;
}
