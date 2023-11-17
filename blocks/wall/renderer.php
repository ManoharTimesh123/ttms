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
 * User Wall
 * @package    block_wall
 */
namespace wall;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/wall/classes/post.php');
require_once($CFG->dirroot.'/lib/form/editor.php');
require_once($CFG->dirroot . '/lib/editorlib.php');

function add_button($text, $url) {
    global $CFG, $DB, $OUTPUT;
    $adduserbutton = \html_writer::start_tag('div');
    $adduserbutton .= \html_writer::link($url, $text, array('class' => 'btn btn-primary mb-3 w-100 createwall-post-btn'));
    $adduserbutton .= \html_writer::end_tag('div');
    return $adduserbutton;
}

function render_approve_wall_post_grid() {
    global $CFG, $USER;
    $loggedinuserid = $USER->id;
    $context = \context_system::instance();
    $postsdata = \post\local_wall::approvedposts($courseid = 0);
    $editor = \editors_get_preferred_editor();

    $outputhtml = '<div>';
    foreach ($postsdata as $courseid => $posts) {

        $course = get_course($courseid);

        $postdetailurl = new \moodle_url($CFG->wwwroot . '/local/wall/index.php', array('courseid' => $course->id));
        $dateformat = ($CFG->dateformat) ? $CFG->dateformat : 'F j, Y, g:i a';

        foreach ($posts as $post) {
            $postid = $post['id'];

            $attobuttons = 'style1 = bold, italic' .  PHP_EOL . 'files = emojipicker';
            $editor->use_editor("comment_content_$postid", [
                'autosave' => false,
                'atto:toolbar' => $attobuttons,
            ]);
            $commenteditor = \html_writer::tag('textarea', '',
                 array('id' => "comment_content_$postid", 'name' => 'comment_content', 'width' => '68%', 'height' => '68%'));

            $fileurl = \post\local_wall::getuploadedfileurl($postid, $context->id);

            if (isset($fileurl) && !empty($fileurl)) {
                $fileurl = $fileurl;
            } else {
                $fileurl = '';
            }

            // Check user is enrolled in course or not.
            $isuserenrolledincourse = \post\local_wall::checkuserenrollementincourse($post['course_id']);
            if ($isuserenrolledincourse) {
                $commentclass = 'btn-comment';
                $likeclass = 'btn-like';
                $shareclass = 'btn-share';
                $warningmessage = '';
            } else {
                $commentclass = '';
                $likeclass = '';
                $shareclass = '';
                $warningmessage = get_string('notenrolledincourse', 'block_wall');
            }
            $commenthtml = '';
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

                    if ($comment->createdby == $loggedinuserid) {
                        $deletecomment = '<a onclick="deletepostcomment(' . $comment->id . ')"
						class="delete_comment"><i class="fa fa-trash"></i></a>';
                    } else {
                        $deletecomment = '';
                    }

                    $commentdescription = $comment->description;
                    if (strlen($comment->description) > 100) {
                        $commentdescription = substr($comment->description, 0, 100);
                        $commentdescription .= '<div id="fulltext_' . $comment->id . '" class="fulltext">'
                        . $comment->description . '</div>';
                        $commentdescription .= ' <a id="' . $comment->id . '" class="readmore" href="#">' . get_string('readmore', 'block_wall') . '</a>';
                    }

                    $commentdate = customdateformat('DATE_WITH_TIME', $comment->timecreated);
                    $usercontext = \context_user::instance($comment->createdby);
                    $profilepicture = \post\local_wall::getuserprofilepictureurl($comment->createdby, $usercontext);
                    $commenthtml .= '
                        <div class="d-flex flex-start mt-2" id="comment_row_' . $comment->id . '">
                            <img class="rounded-circle shadow-1-strong mr-3"
                            src="' . $profilepicture . '" alt="avatar" width="40"
                            height="40" />
                            <div class="w-100 commnet-box-bg">
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                  <h6 class="text-primary fw-bold mb-0">
                                    ' . $comment->commented_by . '
                                  </h6>
                                  ' . $commentdescription . '
                                  <p class="mb-0">
                                  ' . $deletecomment . '
                                  ' . $commentdate . '
                                  </p>
                                </div>
                            </div>
                        </div>';
                }
            }

            $posteddate = customdateformat('DATE_WITH_TIME', $post['created_date']);
            $usercontext = \context_user::instance($post['user_id']);
            $profilepicture = \post\local_wall::getuserprofilepictureurl($post['user_id'], $usercontext);
            $outputhtml .= '
                    <div class="wall-posts card mb-3">
                            <div class="d-flex flex-start align-items-center">
                              <img class="rounded-circle shadow-1-strong mr-2"
                                src="' . $profilepicture . '" alt="avatar" width="60"
                                height="60" />
                              <div>
                                <h4 class="fw-bold text-dark mb-1">'. $post['post_added_by'] .'</h4>
                                <p class="text-muted small mb-0">
                                '. $posteddate .'
                                </p>
                              </div>
                            </div>
                          <div class="wall-post-image mt-3 mb-2">'. $fileurl .'</div>
                              <p class="mb-2">'. strip_html_tag_and_limit_character($post['post_content'], 100) .'</p>
                              <a class="shadow-none mb-2 mt-2" target="_blank" href="'. $postdetailurl .'">
							  <h4 class="h5 font-weight-bold">' . $course->fullname . '</h4>
							  </a>
                            <div class="small d-flex justify-content-start border-bottom mb-2 wallpost-social-icons">
                              <a href="javascript:void(0)" id="' . $postid . '"
							  class="flex-grow-1 user_like_' . $postid .' '. $likeclass. '  d-flex align-items-center mr-3">
                                <i class="fa '.$thumbclass.' mr-2" title="' . $warningmessage . '"></i>
								<p class="mb-0">  '.$postlikes.'</p>
                              </a>
                              <a  href="javascript:void(0)" id="' . $postid . '"
							  class="flex-grow-1 ' . $commentclass . ' d-flex align-items-center mr-3 justify-content-center">
                                <i class="fa fa-commenting mr-2" title="' . $warningmessage . '"></i>
                               <!-- <p class="mb-0" id="post_comment_' . $postid . '">Comment '.$commentcount.'</p> -->
                                <p class="mb-0" id="post_comment_' . $postid . '"> '.$commentcount.'</p>
                              </a>
                              <a href="javascript:void(0)"  id="' . $postid . '"
							  class="flex-grow-1 '. $shareclass .' d-flex align-items-center mr-3 justify-content-end">
                                <i class="flaticon-share mr-2" title="' . $warningmessage . '"></i>
                                <p class="mb-0" id="post_share_' . $postid . '"> '.$sharecount.'</p>
                              </a>
                            </div>
                            <div id="socialsharebox' . $postid . '"
							class="socialsharebox card-footer mt-2 py-3 border-0" style="display:none;">
                               <div class="d-flex flex-start w-100">
                                  <div class="form-outline w-100">
                                    <a id="facebook_' . $postid . '"
									href="https://www.facebook.com/sharer/sharer.php?u='.$CFG->wwwroot.'/local/wall/detail.php?id='.$post['id'].'"
									class="mr-2 shareurl rounded-circle text-center mx-auto facebook-icon">
                                  <i class="fa fa-facebook text-center text-white m-0" aria-hidden="true"></i>
                                    </a>
                                     <a id="twitter_' . $postid . '"
									 href="https://twitter.com/intent/tweet/?text='.$CFG->wwwroot.'/local/wall/detail.php?id='.$post['id'].'"
									 class="mr-2 shareurl rounded-circle text-center mx-auto twitter-icon">
                                   <i class="fa fa-twitter text-center text-white m-0" aria-hidden="true"></i>
                                     </a>
                                     <a id="instagram_' . $postid . '"
									 href="http://instagram.com/sharer.php?'.$CFG->wwwroot.'/local/wall/detail.php?id='.$post['id'].'"
									 class="mr-2 shareurl rounded-circle text-center mx-auto instagram-icon">
                                   <i class="fa fa-instagram text-center text-white m-0" aria-hidden="true"></i>
                                     </a>
                                     <a id="linkedin_' . $postid . '"
									 href="https://www.linkedin.com/sharing/share-offsite/?url='.$CFG->wwwroot.'/local/wall/detail.php?id='.$post['id'].'"
									 class="mr-2 shareurl rounded-circle text-center mx-auto linkedin-icon">
                                   <i class="fa fa-linkedin text-center text-white m-0" aria-hidden="true"></i>
                                     </a>
                                    <a id="whatsapp_' . $postid . '"
									class="shareurl rounded-circle text-center mx-auto whatsapp-icon"
									href="https://api.whatsapp.com/send?text='.$CFG->wwwroot.'/local/wall/detail.php?id='.$post['id'].'">
                                       <i class="fa fa-whatsapp text-center text-white m-0" aria-hidden="true"></i>
                                    </a>
                                  </div>
                               </div>
                            </div>
                            <div id="commentbox' . $postid . '"
							class="commentbox card-footer mt-2 py-1 border-0" style="display:none;">
                               <div class="d-flex w-100 align-items-start">
                                  <img class="rounded-circle shadow-1-strong mr-2"
								  src="' . $CFG->wwwroot  . '/user/pix.php/pix' . '/f1.jpg" alt="avatar" width="40">
                                  <div class="form-outline w-100">
                                     '. $commenteditor .'
                                  </div>
                                   <button type="button" id="comment_submit_' . $postid . '"
								   class="m-0 ml-2 btn btn-primary btn-sm btn-submit text-white border-0">
								   <i class="flaticon-paper-plane"></i></button>
                               </div>
                            </div>
                            <div class="mb-3 user-comments">
                              <div class=" user_comment_' . $postid . '">
                            '. $commenthtml .'
                              </div></div>
                    </div>';
        }
    }
    $outputhtml .= '</div>';

    return $outputhtml;
}
