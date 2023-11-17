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
 * User Badges
 *
 * @package    block_user_badges
 */

function get_badges() {
    global $DB, $USER;

    $encryptedvalue = $DB->get_field('block_instances', 'configdata', array('blockname' => 'user_badges'));

    $caculationcriteriavalue = unserialize(base64_decode($encryptedvalue));

    if ($caculationcriteriavalue) {
        $caculationcriteriavalue = $caculationcriteriavalue->calculation_criteria;
    } else {
        $caculationcriteriavalue = 10;
    }

    $loggedinuserid = $USER->id;

    $userposts = $DB->get_records('local_wall_posts', array('createdby' => $loggedinuserid, 'deleted' => 0));
    $userbadges = 0;

    if (!empty($userposts)) {
        $commentcount = 0;
        $likecount = 0;
        $sharecount = 0;

        foreach ($userposts as $post) {
            $postid = $post->id;

            $commentcount += get_post_comment_count($postid);
            $likecount += get_post_like_count($postid);
            $sharecount += get_post_share_count($postid);
        }

        $totalcount = $commentcount + $likecount + $sharecount;

        if ($totalcount > 0) {
            $userbadges = $totalcount / $caculationcriteriavalue;
            $userbadges = (int)$userbadges;
        }
    }

    return $userbadges;
}

function get_post_comment_count($postid) {
    global $DB;

    return $DB->count_records('local_wall_post_comments', array('postid' => $postid, 'deleted' => 0));
}

function get_post_like_count($postid) {
    global $DB;

    return $DB->count_records('local_wall_post_likes', array('postid' => $postid, 'deleted' => 0));
}

function get_post_share_count($postid) {
    global $DB;

    return $DB->count_records('local_wall_post_shares', array('postid' => $postid, 'deleted' => 0));
}
