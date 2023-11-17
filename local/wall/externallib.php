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
 * Wall Post
 *
 * @package local_wall
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/filelib.php');
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/wall/classes/post.php');
require_once($CFG->dirroot . '/local/wall/locallib.php');

class local_wall_external extends \external_api {

    public static function get_posts_parameters() {
        return new external_function_parameters(
            array(
                'post' => new external_value(PARAM_TEXT, 'post all', VALUE_OPTIONAL)
            )
        );
    }

    public static function get_posts($courseid) {
        global $CFG;

        $dateformat = ($CFG->dateformat) ? $CFG->dateformat : 'F j, Y, g:i a';
        $posts = get_posts($courseid);
        $formatedpostdata = array();
        foreach ($posts as $post) {
            $context = \context_system::instance();
            $fileurl = \post\local_wall::getuploadedfileurl($post->id, $context->id);

            if (isset($fileurl) && !empty($fileurl)) {
                $fileurlhtml = $fileurl;
                preg_match( '@src="([^"]+)"@' , $fileurlhtml, $match );
                $filesrc = array_pop($match);
                $fileurl = $filesrc;
            } else {
                $fileurl = '';
            }

            $currentuserlikepost = current_user_like_post($post->id);

            $postdetialurl = $CFG->wwwroot.'/local/wall/detail.php?id='.$post->id;

            $data = array();
            $data['id'] = $post->id;
            $data['user_id'] = $post->createdby;
            $data['post_content'] = $post->description;
            $data['course_id'] = $post->courseid;
            $data['user_like_post'] = $currentuserlikepost;
            $data['share_url'] = $postdetialurl;
            $data['course_name'] = $post->fullname;
            $data['post_file'] = $fileurl;
            $data['post_added_by'] = $post->name;
            $data['created_date'] = customdateformat('DATE_WITH_TIME', $post->timecreated);
            $data['post_like_count'] = count(getpostvotecount($post->id));
            $data['post_comment_count'] = count(getpostcomment($post->id));
            $data['post_comment'] = getpostcomment($post->id);
            $data['post_share_count'] = count(getpostshare($post->id));
            $formatedpostdata[] = $data;
        }

        return [
            'data' => $formatedpostdata
        ];

    }

    public static function get_posts_returns() {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'post data')
            )
        );
    }
    public static function post_create_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'description' => new external_value(PARAM_TEXT, 'description'),
                'file' => new external_value(PARAM_FILE, 'file', VALUE_OPTIONAL),
                'courseid' => new external_value(PARAM_INT, 'course id'),
            ])
        ]);
    }

    public static function post_create($data, $file) {
        global $USER;

        $loggedinuserid = $USER->id;
        $author = $USER->firstname.' '.$USER->lastname;
        $params = self::validate_parameters(self::post_create_parameters(), ['data' => $data]);
        $params = $params['data'];

        $courseid = $params['courseid'];

        $isenrolled = \post\local_wall::checkuserenrollementincourse($courseid);

        if (!$isenrolled) {
            throw new moodle_exception('noenrolementincourse', '', '', '', get_string('postaddederrormsg', 'local_wall'));
        }

        if (isset($file) && !empty($file)) {
            $maxfilesize = 2097152;
            $fileobject = $file;
            if ($fileobject['walls']['size'][0]['file'] > $maxfilesize) {
                throw new \file_exception(get_string('uploadfileissuemsg', 'local_wall'));
            }
        } else {
            $fileobject = null;
        }

        $postdata = new stdClass();
        $postdata->description['text'] = $params['description'];
        $postdata->courseid = $courseid;
        $postdata->userid = $loggedinuserid;

        try {
            $postid = add_post($postdata);
            if ($fileobject) {
                self::upload_file($fileobject, $postid, $loggedinuserid, $author, 1, 'local_wall', 'attachment', $maxfilesize);
            }
            return [
                'data' => [
                    [
                        'processed' => true,
                        'record' => get_string('recordcreated', 'local_wall')
                    ]
                ]
            ];
        } catch (dml_exception $e) {
            return [
                'data' => [
                    [
                        'processed' => false,
                        'record' => $e
                    ]
                ]
            ];
        }
    }
    public static function post_create_returns() {
        $params = [
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'processed' => new external_value(PARAM_BOOL, 'status'),
                    'record' => new external_value(PARAM_TEXT, 'description')
                ])
            ),
        ];
        return new external_single_structure($params);
    }

    public static function upload_file($fileobject, $itemid, $userid, $uploadername, $contextid, $component, $filearea, $filesize) {
        $filename = $fileobject['walls']['name'][0]['file'];
        $filetype = $fileobject['walls']['type'][0]['file'];
        $filepath = $fileobject['walls']['tmp_name'][0]['file'];
        $fs = get_file_storage();
        $fileinfo = array(
            'contextid' => $contextid,
            'component' => $component,
            'filearea' => $filearea,
            'itemid' => $itemid,
            'userid' => $userid,
            'source' => $filename,
            'author' => $uploadername,
            'license' => 'public',
            'filepath' => '/',
            'filename' => $filename,
            'filetype' => $filetype,
        );
        $filestatus = $fs->create_file_from_pathname($fileinfo, $filepath);
        if ($filestatus) {
            return true;
        } else {
            return false;
        }
    }

    public static function post_update_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'postid' => new \external_value(PARAM_INT, 'post id'),
                'description' => new \external_value(PARAM_TEXT, 'description'),
                'courseid' => new external_value(PARAM_INT, 'course id', VALUE_OPTIONAL),
            ])
        ]);
    }

    public static function post_update($data) {
        global $USER;

        $params = self::validate_parameters(self::post_update_parameters(), ['data' => $data]);

        $postdata = new stdClass();

        $params = $params['data'];
        $courseid = $params['courseid'];

        if ($courseid) {
            $postdata->courseid = $courseid;

            $isenrolled = \post\local_wall::checkuserenrollementincourse($courseid);

            if (!$isenrolled) {
                throw new moodle_exception('noenrolementincourse', '', '', '', get_string('postupdatederrormsg', 'local_wall'));
            }
        }

        $time = time();
        $postdata->id = $params['postid'];
        $postdata->description = $params['description'];
        $postdata->timemodified = $time;
        $postdata->updatedby = $USER->id;

        try {
            $updated = update_post($postdata);
            if ($updated) {
                return [
                    'data' => [
                        [
                            'processed' => true,
                            'record' => get_string('recordupdated', 'local_wall')
                        ]
                    ]
                ];
            }
        } catch (dml_exception $e) {
            return [
                'data' => [
                    [
                        'processed' => false,
                        'record' => $e
                    ]
                ]
            ];
        }
    }

    public static function post_update_returns() {
        $params = [
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'processed' => new external_value(PARAM_BOOL, 'true false'),
                    'record' => new external_value(PARAM_TEXT, 'description')
                ])
            ),
        ];
        return new external_single_structure($params);
    }

    public static function post_delete_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'postid' => new \external_value(PARAM_INT, 'post id'),
            ])
        ]);
    }

    public static function post_delete($data) {
        global $USER;

        $params = self::validate_parameters(self::post_delete_parameters(), ['data' => $data]);
        $params = $params['data'];
        $postdata = new stdClass();
        $postdata->id = $params['postid'];
        $postdata->userid = $USER->id;
        try {
            delete_post($postdata);
            return [
                'data' => [
                    [
                        'processed' => true,
                        'record' => get_string('recorddeleted', 'local_wall')
                    ]
                ]
            ];
        } catch (dml_exception $e) {
            return [
                'data' => [
                    [
                        'processed' => false,
                        'record' => $e
                    ]
                ]
            ];
        }
    }

    public static function post_delete_returns() {
        $params = [
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'processed' => new external_value(PARAM_BOOL, 'true false'),
                    'record' => new external_value(PARAM_TEXT, 'description')
                ])
            ),
        ];
        return new external_single_structure($params);
    }
    public static function post_comment_create_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'postid' => new external_value(PARAM_INT, 'Post id'),
                'description' => new external_value(PARAM_RAW, 'Post Comment')
            ])
        ]);
    }

    public static function post_comment_create($data) {
        global $USER;
        $params = self::validate_parameters(self::post_comment_create_parameters(), ['data' => $data]);
        $params = $params['data'];

        $commentdata = new stdClass();
        $commentdata->description = $params['description'];
        $commentdata->postid = $params['postid'];
        $commentdata->userid = $USER->id;

        try {
            $commentcreateddata = add_post_comment($commentdata);

            if ($commentcreateddata) {
                return [
                    'data' => [
                        [
                            'processed' => true,
                            'id' => $commentcreateddata['id'],
                            'comment_content' => $commentcreateddata['comment_content'],
                            'user_profile_picture' => $commentcreateddata['user_profile_picture'],
                            'comment_added_by' => $commentcreateddata['comment_added_by'],
                            'created_at' => $commentcreateddata['created_at'],
                            'comment_count' => $commentcreateddata['comment_count']
                        ]
                    ]
                ];
            }

        } catch (dml_exception $e) {
            return [
                'data' => [
                    [
                        'processed' => false,
                        'record' => $e
                    ]
                ]
            ];
        }

    }

    public static function post_comment_create_returns() {
        $params = [
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'processed' => new external_value(PARAM_BOOL, 'created /or not created '),
                    'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                    'comment_content' => new external_value(PARAM_RAW, 'comment_content', VALUE_OPTIONAL),
                    'user_profile_picture' => new external_value(PARAM_TEXT, 'user_profile_picture', VALUE_OPTIONAL),
                    'comment_added_by' => new external_value(PARAM_TEXT, 'comment_added_by', VALUE_OPTIONAL),
                    'created_at' => new external_value(PARAM_TEXT, 'created_at', VALUE_OPTIONAL),
                    'comment_count' => new external_value(PARAM_INT, 'comment_count', VALUE_OPTIONAL)
                ])
            ),
        ];

        return new external_single_structure($params);
    }

    public static function post_comment_delete_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'commentid' => new external_value(PARAM_INT, 'comment id'),
            ])
        ]);
    }


    public static function post_comment_delete($commentdata) {
        global $USER;
        $params = self::validate_parameters(self::post_comment_delete_parameters(), ['data' => $commentdata]);
        $params = $params['data'];
        $loggedinuserid = $USER->id;

        $postcomment = new stdClass();
        $postcomment->userid = $loggedinuserid;
        $postcomment->commentid = $params['commentid'];
        $deletedcommentdata = delete_post_comment($postcomment);
        if ($deletedcommentdata) {
            return [
                'data' => [
                    [
                        'processed' => true,
                        'record' => $deletedcommentdata['postid'],
                        'comment_count' => $deletedcommentdata['comment_count'],
                    ]
                ]
            ];
        } else {
            return [
                'data' => [
                    [
                        'processed' => false,
                        'record' => get_string('recordnotdeleted', 'local_wall'),
                    ]
                ]
            ];
        }
    }

    public static function post_comment_delete_returns() {
        $params = [
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'processed' => new external_value(PARAM_BOOL, 'true false'),
                    'record' => new external_value(PARAM_INT, 'Post Id', VALUE_OPTIONAL),
                    'comment_count' => new external_value(PARAM_INT, 'Comment count', VALUE_OPTIONAL)
                ])
            ),
        ];
        return new external_single_structure($params);
    }

    public static function post_like_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'postid' => new external_value(PARAM_INT, 'post id'),
            ])
        ]);
    }

    public static function post_like($postdata) {
        global $USER;

        $params = self::validate_parameters(self::post_like_parameters(), ['data' => $postdata]);
        $params = $params['data'];

        $postdatalike = new stdClass();
        $postdatalike->userid = $USER->id;
        $postdatalike->postid = $params['postid'];
        $data = add_or_update_post_like($postdatalike);
        if ($data) {
            return [
                'data' => [
                    [
                        'processed' => true,
                        'like' => $data['like'],
                        'total_like' => $data['total_like'],
                    ]
                ]
            ];
        } else {
            return [
                'data' => [
                    [
                        'processed' => false,
                        'record' => 'Record not found.',
                    ]
                ]
            ];
        }

    }

    public static function post_like_returns() {
        $params = [
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'processed' => new external_value(PARAM_BOOL, 'updated or added'),
                    'like' => new external_value(PARAM_BOOL, 'true false', VALUE_OPTIONAL),
                    'total_like' => new external_value(PARAM_INT, 'Total post like', VALUE_OPTIONAL)
                ])
            ),
        ];
        return new external_single_structure($params);
    }

    public static function post_share_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'postid' => new external_value(PARAM_INT, 'Post Id'),
                'shareto' => new external_value(PARAM_TEXT, 'Social Provider'),
            ])
        ]);
    }

    public static function post_share($postdata) {
        global $USER;

        $params = self::validate_parameters(self::post_share_parameters(), ['data' => $postdata]);
        $params = $params['data'];

        $sharedata = new stdClass();
        $sharedata->socialprovider = $params['shareto'];
        $sharedata->postid = $params['postid'];
        $sharedata->userid = $USER->id;
        $data = post_share($sharedata);
        if ($data) {
            return [
                'data' => [
                    [
                        'processed' => true,
                        'shareto' => $data['shareto'],
                        'sharedcount' => $data['sharedcount'],
                    ]
                ]
            ];
        } else {
            return [
                'data' => [
                    [
                        'processed' => false,
                    ]
                ]
            ];
        }
    }

    public static function post_share_returns() {
        $params = [
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'processed' => new external_value(PARAM_BOOL, 'True False'),
                    'shareto' => new external_value(PARAM_TEXT, 'Share to', VALUE_OPTIONAL),
                    'sharedcount' => new external_value(PARAM_INT, 'Total post share', VALUE_OPTIONAL)
                ])
            ),
        ];
        return new external_single_structure($params);
    }

}
