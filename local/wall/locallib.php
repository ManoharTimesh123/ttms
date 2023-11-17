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
 * The Wall Post Management
 *
 * @package local_wall
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/wall/classes/post.php');

function get_posts($courseid) {
    global $DB;

    $systemcontext = \context_system::instance();
    if (!has_capability('local/wall:view', $systemcontext)) {
        throw new moodle_exception('nopermissions');
    }

    if ($courseid && (int) $courseid) {
        $getapprovedpostsql = <<<SQL_QUERY
            select p.*, CONCAT(u.firstname, ' ', u.lastname) as name, c.fullname
             from {local_wall_posts} p
            INNER JOIN {user} u ON  p.createdby = u.id
            JOIN {course} c ON  c.id = p.courseid
            WHERE p.approved = 1 AND p.deleted = 0
            AND p.courseid = :courseid
            SQL_QUERY;
        $params = [
            'courseid' => $courseid,
        ];
        $posts = $DB->get_records_sql($getapprovedpostsql, $params);
    } else {
        $getapprovedpostsql = <<<SQL_QUERY
            select p.*, CONCAT(u.firstname, ' ', u.lastname) as name, c.fullname
            from {local_wall_posts} p
            INNER JOIN {user} u ON  p.createdby = u.id
            LEFT JOIN {course} c ON  c.id = p.courseid
            WHERE p.approved = 1 AND p.deleted = 0
            order by p.id desc
            SQL_QUERY;
        $posts = $DB->get_records_sql($getapprovedpostsql);
    }
    return $posts;
}

function getpostcomment($postid) {
    global $DB;
    $getpostcommentsql = <<<SQL_QUERY
            select pc.*, CONCAT(u.firstname, ' ', u.lastname) as commented_by from {local_wall_post_comments} pc
            INNER JOIN {user} u ON  pc.createdby = u.id
            WHERE pc.deleted = 0 AND pc.postid = :postid
            order by pc.id desc
            SQL_QUERY;
    $params = [
        'postid' => $postid,
    ];

    $postscomment = $DB->get_records_sql($getpostcommentsql, $params);
    return $postscomment;
}

function getpostvotecount($postid) {
    global $DB;
    $postsvotes = $DB->get_records('local_wall_post_likes', array('postid' => $postid, 'postlike' => 1));
    return $postsvotes;
}

function getpostshare($postid) {
    global $DB;
    $postsshares = $DB->get_records('local_wall_post_shares', array('postid' => $postid));
    return $postsshares;
}
function add_post($data) {
    global $DB;

    $systemcontext = \context_system::instance();
    if (!has_capability('local/wall:add', $systemcontext)) {
        throw new moodle_exception('nopermissions');
    }

    $postdata = new stdClass();
    $postdata->description = $data->description['text'];
    if (isset($data->courseid) && !empty($data->courseid)) {
        $postdata->courseid = $data->courseid;
    }
    $timestamp = time();
    $userid = $data->userid;
    if ($data->id > 0) {

        $post = $DB->get_record('local_wall_posts', array('id' => $data->id));
        $postdata->id = $post->id;
        $postdata->approved = 0;
        $postdata->timemodified = $timestamp;
        $postdata->updatedby = $userid;
        $DB->update_record('local_wall_posts', $postdata);
        return $post->id;
    } else {
        $postdata->timecreated = $timestamp;
        $postdata->timemodified = $timestamp;
        $postdata->createdby = $userid;
        $postdata->updatedby = $userid;
        $postid = $DB->insert_record('local_wall_posts', $postdata);
        return $postid;
    }
}

function update_post($data) {
    global $DB, $USER;

    $systemcontext = \context_system::instance();
    if (!has_capability('local/wall:edit', $systemcontext)) {
        throw new moodle_exception('nopermissions');
    }

    $postid = $data->id;

    $post = $DB->get_record('local_wall_posts', array('id' => $postid, 'deleted' => 0));

    if (!$post) {
        throw new dml_exception('recordnotfound', 'local_wall', get_string('postmissingerrormessage', 'local_wall'));
    }

    // When the required permission do not match while editing the data.
    if (!is_siteadmin() &&
        !has_capability('local/wall:manage', $systemcontext) &&
        (has_capability('local/wall:manageown', $systemcontext) && $post->createdby !== $USER->id)
    ) {
        throw new dml_exception('recordnotfound', 'local_wall', get_string('notautorised', 'local_wall'));
    }

    $DB->update_record('local_wall_posts', $data);

    return true;
}
function delete_post($data) {
    global $DB;

    $systemcontext = \context_system::instance();
    if (!has_capability('local/wall:delete', $systemcontext)) {
        throw new moodle_exception('nopermissions');
    }

    $loggedinuserid = $data->userid;
    $postid = $data->id;

    $post = $DB->get_record('local_wall_posts', array('id' => $postid, 'deleted' => 0));

    if (!$post) {
        throw new dml_exception('recordnotfound', 'local_wall', get_string('postmissingerrormessage', 'local_wall'));
    }

    // When the required permission do not match while deleting the data.
    if (!is_siteadmin() &&
        !has_capability('local/wall:manage', $systemcontext) &&
        (has_capability('local/wall:manageown', $systemcontext) && $post->createdby !== $loggedinuserid)
    ) {
        throw new dml_exception('nopermission', 'local_wall', get_string('notautorised', 'local_wall'));
    }

    $postdata = new stdClass();
    $postdata->id = $post->id;
    $postdata->deleted = 1;
    $postdata->timedeleted = time();
    $postdata->deletedby = $loggedinuserid;
    if ($DB->update_record('local_wall_posts', $postdata)) {
        $params = [
            'deleted' => 1,
            'timedeleted' => time(),
            'deletedby' => $loggedinuserid,
            'postid' => $postid
        ];
        if ($DB->count_records('local_wall_post_comments') > 0) {
            $deletecommentsql = <<<SQL_QUERY
                    UPDATE {local_wall_post_comments}
                     SET deleted = :deleted, timedeleted = :timedeleted, deletedby = :deletedby
                     WHERE postid = :postid
                SQL_QUERY;

            $DB->execute($deletecommentsql, $params);
        }
        if ($DB->count_records('local_wall_post_likes') > 0) {
            $deletepostlikesql = <<<SQL_QUERY
                    UPDATE {local_wall_post_likes}
                     SET deleted = :deleted, timedeleted = :timedeleted, deletedby = :deletedby
                     WHERE postid = :postid
                SQL_QUERY;

            $DB->execute($deletepostlikesql, $params);
        }
        if ($DB->count_records('local_wall_post_shares') > 0) {
            $deletepostsharesql = <<<SQL_QUERY
                    UPDATE {local_wall_post_shares}
                     SET deleted = :deleted, timedeleted = :timedeleted, deletedby = :deletedby
                     WHERE postid = :postid
                SQL_QUERY;

            $DB->execute($deletepostsharesql, $params);
        }
        return true;
    }
    return false;
}

function change_post_status($id) {
    global $DB, $USER;

    $systemcontext = \context_system::instance();
    if (!has_capability('local/wall:approve', $systemcontext)) {
        throw new moodle_exception('nopermissions');
    }

    $loggedinuserid = $USER->id;
    $post = $DB->get_record('local_wall_posts', array('id' => $id));

    if ($post->approved == 0) {
        $status = 1;
    } else {
        $status = 0;
    }

    $postdata = new stdClass();
    $postdata->id = $post->id;
    $postdata->approved = $status;
    $postdata->updatedby = $loggedinuserid;

    return $DB->update_record('local_wall_posts', $postdata);
}

function add_post_comment($data) {
    global $DB, $CFG;

    $loggedinuserid = $data->userid;
    $time = time();
    $postid = $data->postid;
    $dateformat = ($CFG->dateformat) ? $CFG->dateformat : 'F j, Y, g:i a';

    $commentdata = new stdClass();
    $commentdata->description = $data->description;
    $commentdata->postid = $postid;
    $commentdata->createdby = $loggedinuserid;
    $commentdata->updatedby = $loggedinuserid;
    $commentdata->timecreated = $time;
    $commentdata->timemodified = $time;

    $post = $DB->get_record('local_wall_posts', array('id' => $postid, 'deleted' => 0));

    if (!$post) {
        throw new dml_exception('recordnotfound', 'local_wall', get_string('postmissingerrormessage', 'local_wall'));
    }

    $isenrolled = \post\local_wall::checkuserenrollementincourse($post->courseid);

    if (!$isenrolled) {
        throw new moodle_exception('noenrolementincourse', 'local_wall', '', '', get_string('notaddpostcommentmessage', 'local_wall'));
    }

    $commetid = $DB->insert_record('local_wall_post_comments', $commentdata);

    if ($commetid) {

        $commetcreatedby = $DB->get_record('user', array('id' => $loggedinuserid));
        $totalcomment = $DB->count_records('local_wall_post_comments', array('postid' => $postid, 'deleted' => 0));
        $commentdescription = $data->description;

        if (strlen($data->description) > 100) {
            $commentdescription = substr($data->description, 0, 100);
            $commentdescription .= '<div style="display:none;" id="fulltext_' . $commetid . '" class="fulltext_' . $commetid . '">' . $data->description . '</div>';
            $commentdescription .= ' <a id="' . $commetid . '"class="readmore" href="#">Read more...</a>';
        }

        return [
            'id' => $commetid,
            'user_profile_picture' => $CFG->wwwroot . '/user/pix.php/' . $loggedinuserid. '/f1.jpg',
            'comment_content' => $commentdescription,
            'comment_added_by' => fullname($commetcreatedby),
            'created_at' => customdateformat('DATE_WITH_TIME', $commentdata->timecreated),
            'comment_count' => $totalcomment
        ];
    } else {
        return false;
    }
}

function delete_post_comment($commentdata) {
    global $DB;

    $postcomment = new stdClass();
    $postcomment->userid = $commentdata->userid;
    $postcomment->id = $commentdata->commentid;
    $postcomment->deleted = 1;
    $postcomment->deletedby = $commentdata->userid;
    $postcomment->timedeleted = time();

    $commentdetail = $DB->get_record('local_wall_post_comments', array('deleted' => 0, 'id' => $commentdata->commentid));

    if (!$commentdetail) {
        throw new dml_exception('recordnotfound', 'local_wall', get_string('commentmissingerrormsg', 'local_wall'));
    }

    if ($commentdetail->createdby !== $commentdata->userid) {
        throw new dml_exception('recordnotfound', 'local_wall', get_string('notautorised', 'local_wall'));
    }

    if ($commentdetail) {
        $DB->update_record('local_wall_post_comments', $postcomment);
        $totalcomment = $DB->count_records('local_wall_post_comments', array('postid' => $commentdetail->postid, 'deleted' => 0));
        return [
            'postid' => $commentdetail->postid,
            'comment_count' => $totalcomment
        ];
    } else {
        return false;
    }
}

function add_or_update_post_like($postlikedata) {
    global $DB;

    $loggedinuserid = $postlikedata->userid;
    $postid = $postlikedata->postid;

    $post = $DB->get_record('local_wall_posts', array('id' => $postid, 'deleted' => 0));

    if (!$post) {
        throw new dml_exception('recordnotfound', 'local_wall', get_string('postmissingerrormessage', 'local_wall'));
    }

    $isenrolled = \post\local_wall::checkuserenrollementincourse($post->courseid);

    if (!$isenrolled) {
        throw new moodle_exception('noenrolementincourse', 'local_wall', '', '', get_string('notlikepostmessage', 'local_wall'));
    }

    $checkrecordexist = $DB->get_record('local_wall_post_likes', array('postid' => $postid, 'createdby' => $loggedinuserid));
    if ($checkrecordexist) {

        $votedata = new stdClass();
        $votedata->id = $checkrecordexist->id;
        if ($checkrecordexist->postlike == 1) {
            $like = false;
            $votedata->postlike = 0;
            $votedata->postunlike = 1;
        } else {
            $like = true;
            $votedata->postlike = 1;
            $votedata->postunlike = 0;
        }
        $DB->update_record('local_wall_post_likes', $votedata);
        $totallikes = $DB->count_records('local_wall_post_likes', array('postid' => $postid, 'postlike' => 1));
        return [
            'like' => $like,
            'total_like' => $totallikes
        ];
    }
    $time = time();
    $postdata = new stdClass();
    $postdata->postid = $postid;
    $postdata->createdby = $loggedinuserid;
    $postdata->updatedby = $loggedinuserid;
    $postdata->postlike = 1;
    $postdata->postunlike = 0;
    $postdata->timecreated = $time;
    $postdata->timemodified = $time;

    if ($DB->insert_record('local_wall_post_likes', $postdata)) {
        $totallikes = $DB->count_records('local_wall_post_likes', array('postid' => $postid, 'postlike' => 1));
        return [
            'like' => true,
            'total_like' => $totallikes
        ];
    } else {
        return false;
    }
}

function post_share($postdata) {
    global $DB;

    $loggedinuserid = $postdata->userid;
    $shareto = $postdata->socialprovider;
    $postid = $postdata->postid;

    $post = $DB->get_record('local_wall_posts', array('id' => $postid, 'deleted' => 0));

    if (!$post) {
        throw new dml_exception('recordnotfound', 'local_wall', get_string('postmissingerrormessage', 'local_wall'));
    }

    $isenrolled = \post\local_wall::checkuserenrollementincourse($post->courseid);

    if (!$isenrolled) {
        throw new moodle_exception('noenrolementincourse', 'local_wall', '', '', get_string('notsharepostmessage', 'local_wall'));
    }

    $time = time();
    $sharedata = new stdClass();
    $sharedata->socialprovider = $shareto;
    $sharedata->postid = $postid;
    $sharedata->createdby = $loggedinuserid;
    $sharedata->updatedby = $loggedinuserid;
    $sharedata->timecreated = $time;
    $sharedata->timemodified = $time;

    if ($DB->insert_record('local_wall_post_shares', $sharedata)) {
        $totalshare = $DB->count_records('local_wall_post_shares', array('postid' => $postid, 'deleted' => 0));
        return [
            'shareto' => $shareto,
            'sharedcount' => $totalshare
        ];
    } else {
        return false;
    }

}

function current_user_like_post($postid) {
    global $DB, $USER;

    $loggeinuserid = $USER->id;
    $postlike = $DB->count_records('local_wall_post_likes', array('postid' => $postid, 'postlike' => 1, 'createdby' => $loggeinuserid));

    return $postlike;
}

function get_course_detail_by_post_id($postid) {
    global $DB;

    $getcoursedetailsql = <<<SQL_QUERY
            select c.id, c.fullname
            from {local_wall_posts} p
            LEFT JOIN {course} c ON  c.id = p.courseid
            WHERE  p.deleted = 0
            SQL_QUERY;

    $course = $DB->get_record_sql($getcoursedetailsql);

    return $course;
}

