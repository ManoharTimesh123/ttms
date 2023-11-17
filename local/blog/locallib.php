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
 *
 * @package    local_blog
 */

function add_post($data) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $timestamp = time();
    $systemcontext = context_system::instance();

    $postdata = new stdClass();
    $postdata->title = strip_tags($data->title);
    $postdata->description = $data->description['text'];

    if ($data->id > 0) {

        $post = $DB->get_record('local_blogs', array('id' => $data->id, 'deleted' => 0));

        if (!$post) {
            throw new dml_exception('recordnotfound', 'local_blog', 'blog does not exist.');
        }

        // When the required permission do not match while deleting the data.
        if (!is_siteadmin() &&
            !has_capability('local/blog:manage', $systemcontext) &&
            (has_capability('local/blog:manageown', $systemcontext) && $post->usercreated != $loggedinuserid)
        ) {
            throw new \moodle_exception(get_string('blogeditownerror', 'local_blog'), 'local_blog', '');
        }

        $postdata->id = $post->id;
        $postdata->approved = 0;
        $postdata->timemodified = $timestamp;
        $postdata->usermodified = $loggedinuserid;
        $dataid = $DB->update_record('local_blogs', $postdata);
        return $post->id;
    } else {
        $postdata->timecreated = $timestamp;
        $postdata->timemodified = $timestamp;
        $postdata->usercreated = $loggedinuserid;
        $postdata->usermodified = $loggedinuserid;
        $postid = $DB->insert_record('local_blogs', $postdata);
        return $postid;
    }

}

function get_approve_post() {
    global $DB;

    $posts = $DB->get_records('local_blogs', array('deleted' => 0, 'approved' => 1), 'id desc');
    return $posts;
}

function delete_post($id) {
    global $DB, $USER;

    $loggedinuserid = $USER->id;
    $systemcontext = context_system::instance();

    $post = $DB->get_record('local_blogs', array('id' => $id, 'deleted' => 0));

    if (!$post) {
        throw new dml_exception('recordnotfound', 'local_blog', get_string('blogmissingerrormsg', 'local_blog'));
    }

    // When the required permission do not match while deleting the data.
    if (!is_siteadmin() &&
        !has_capability('local/blog:manage', $systemcontext) &&
        (has_capability('local/blog:manageown', $systemcontext) && $post->usercreated != $loggedinuserid)
    ) {
        throw new \moodle_exception(get_string('blogeditownerror', 'local_blog'), 'local_blog', '');
    }

    $postdata = new stdClass();
    $postdata->id = $post->id;
    $postdata->deleted = 1;
    $postdata->timedeleted = time();
    $postdata->userdeleted = $loggedinuserid;

    return $DB->update_record('local_blogs', $postdata);
}

function change_post_status($id) {
    global $DB;

    $post = $DB->get_record('local_blogs', array('id' => $id));

    if ($post->approved == 0) {
        $status = 1;
    } else {
        $status = 0;
    }
    $postdata = new stdClass();
    $postdata->id = $post->id;
    $postdata->approved = $status;

    return $DB->update_record('local_blogs', $postdata);
}

