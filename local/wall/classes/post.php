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

namespace post;
class local_wall {

    public static function listallposts() {
        global $DB, $USER;
        $systemcontext = \context_system::instance();
        if (has_capability('local/wall:manage', $systemcontext)) {
            $posts = $DB->get_records('local_wall_posts', array('deleted' => 0), 'id desc');
        } else {
            $posts = $DB->get_records('local_wall_posts', array('createdby' => $USER->id, 'deleted' => 0), 'id desc');
        }
        return $posts;
    }
    public static function approvedposts($courseid) {
        global $DB;

        $getapprovedpostsql = "
            select p.*, CONCAT(u.firstname, ' ', u.lastname) as name, c.fullname
            from {local_wall_posts} p
            INNER JOIN {user} u ON  p.createdby = u.id
            JOIN {course} c ON c.id = p.courseid
             WHERE p.approved = 1 AND p.deleted = 0";

        if ($courseid > 0) {
            $getapprovedpostsql .= " AND p.courseid = $courseid";
        }

        $getapprovedpostsql .= " order by p.id desc";

        $posts = $DB->get_records_sql($getapprovedpostsql);

        $formatedpostdata = array();

        foreach ($posts as $post) {
            $data = array();
            $data['id'] = $post->id;
            $data['user_id'] = $post->createdby;
            $data['course_id'] = $post->courseid;
            $data['course_name'] = $post->fullname;
            $data['post_content'] = $post->description;
            $data['post_added_by'] = $post->name;
            $data['created_date'] = $post->timecreated;
            $data['post_vote'] = self::getpostvotecount($post->id);
            $data['post_comment'] = self::getpostcomment($post->id);
            $data['post_share'] = self::getpostshare($post->id);
            $formatedpostdata[$post->courseid][] = $data;
        }
        return $formatedpostdata;

    }
    public static function listdata() {
        return self::approvedposts();
    }

    public static function getpostcomment($postid) {
        global $DB;
        $getpostcommentsql = <<<SQL_QUERY
            select pc.*, CONCAT(u.firstname, ' ', u.lastname) as commented_by from {local_wall_post_comments} pc
            INNER JOIN {user} u ON  pc.createdby = u.id
            WHERE pc.deleted = 0 AND pc.postid =:postid
            order by pc.id desc
            SQL_QUERY;
        $params = [
            'postid' => $postid,
        ];

        $postscomment = $DB->get_records_sql($getpostcommentsql, $params);
        return $postscomment;
    }

    public static function checkuserenrollementincourse($courseid) {
        global $DB, $USER;

        $getuserenrollmentincoursesql = <<<SQL_QUERY
                            SELECT ue.*
                            FROM {user_enrolments} ue
                            JOIN {enrol} e ON e.id = ue.enrolid
                            WHERE e.courseid = :courseid AND ue.userid = :userid;
                            SQL_QUERY;
        $params = [
            'courseid' => $courseid,
            'userid' => $USER->id
        ];

        $userenrolled = $DB->get_record_sql($getuserenrollmentincoursesql, $params);

        if ($userenrolled) {
            return true;
        }
        return false;
    }

    public static function getpostvotecount($postid) {
        global $DB;
        $postsvotes = $DB->get_records('local_wall_post_likes', array('postid' => $postid, 'postlike' => 1));
        return $postsvotes;
    }

    public static function getpostshare($postid) {
        global $DB;
        $postsshares = $DB->get_records('local_wall_post_shares', array('postid' => $postid));
        return $postsshares;
    }

    public static function listpostdetail($postid) {
        global $DB;
        $post = $DB->get_record('local_wall_posts', array('id' => $postid, 'deleted' => 0));

        $post->post_vote = self::getpostvotecount($post->id);
        $post->post_comment = self::getpostcomment($post->id);
        $post->post_share = self::getpostshare($post->id);
        return $post;
    }

    public static function getuserprofilepictureurl($userid, $usercontext) {

        global $CFG, $DB;
        $user = $DB->get_record('user', array('id' => $userid));

        if ($user->picture) {
            $profilepicture = $CFG->wwwroot . '/pluginfile.php/' . $usercontext->id . '/user/icon/edumy/f1?rev=' . $user->picture;
        } else {
            $profilepicture = $CFG->wwwroot . '/user/pix.php/'. $user->id . '/f1.jpg';
        }

        return $profilepicture;
    }

    public static function getuploadedfileurl($postid, $contextid) {
        global $CFG;

        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'local_wall', 'attachment', $postid, 'sortorder', false);
        $file = reset($files);
        $fileurl = '';
        if ($file) {
            $filename = $file->get_filename();
            if ($filename <> '.') {
                $filemimetype = explode('/', $file->get_mimetype());
                $fileurl = $CFG->wwwroot . "/pluginfile.php/" . $contextid . "/local_wall/attachment/" . $postid . '/' . $filename;
                if ($filemimetype[0] == 'image') {
                    $fileurl = '<img src="' . $fileurl . '" />';
                }

                if ($filemimetype[0] == 'video') {
                    $fileurl = '<video controls>
                    <source src="'. $fileurl. '" type="'. $file->get_mimetype() .'">
                    </video>';
                }

                if ($filemimetype[0] == 'audio') {
                    $fileurl = '<audio controls><source src="'. $fileurl .'" type="'. $file->get_mimetype() .'"></audio>';
                }

            }

        }

        return $fileurl;
    }
}
