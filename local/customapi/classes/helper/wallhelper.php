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

namespace local_customapi\helper;

use local_customapi\exception\customapiException;
use local_srsapi\exception\srsapiException;
use stdClass;
use Throwable;

require_once($CFG->dirroot . '/local/wall/externallib.php');

/**
 * Helper functions for Custom API related to wall posts
 *
 */
class wallhelper {

    public static function create_wall($params) {

        try {

            $file = (isset($_FILES)) ? $_FILES : null;
            $wall = \local_wall_external::post_create($params, $file);

            if (!$wall['data'][0]['processed']) {
                throw new customapiException('wserrorcreatewall', $params, $wall['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorcreatewall', $params, $err);
        }

        return [
            'entity' => 'wall',
            'crud' => 'c',
            'externalid' => count($params),
        ];
    }

    public static function read_wall($params) {

        $courseid = ($params['courseid'] > 0) ? $params['courseid'] : null;

        try {
            $posts = \local_wall_external::get_posts($courseid);
        } catch (Exception $err) {
            throw new customapiException('wserrorcreatewall', $params, $err);
        }

        return [
            'records' => $posts['data'],
        ];
    }

    public static function update_wall($params) {

        try {

            $wall = \local_wall_external::post_update($params);

            if (!$wall['data'][0]['processed']) {
                throw new customapiException('wserrorupdatewall', $params, $wall['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorupdatewall', $params, $err);
        }

        return [
            'entity' => 'wall',
            'crud' => 'u',
            'externalid' => $params['postid'],
        ];
    }

    public static function delete_wall($params) {

        try {

            $wall = \local_wall_external::post_delete($params);

            if (!$wall['data'][0]['processed']) {
                throw new customapiException('wserrordeletewall', $params, $wall['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrordeletewall', $params, $err);
        }

        return [
            'entity' => 'wall',
            'crud' => 'd',
            'externalid' => $params['postid'],
        ];
    }

    public static function create_wall_comment($params) {

        try {

            $comment = \local_wall_external::post_comment_create($params);

            if (!$comment['data'][0]['processed']) {
                throw new customapiException('wserrorcreatewall', $params, $comment['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorcreatewall', $params, $err);
        }

        return [
            'entity' => 'wall',
            'crud' => 'c',
            'externalid' => $comment['data'][0]['id'],
        ];
    }

    public static function delete_wall_comment($params) {

        try {

            $comment = \local_wall_external::post_comment_delete($params);

            if (!$comment['data'][0]['processed']) {
                throw new customapiException('wserrorcreatewall', $params, $comment['data'][0]['record']);
            }
        } catch (Exception $err) {
            throw new srsapiException('wserrorcreatewall', $params, $err);
        }

        return [
            'entity' => 'wall',
            'crud' => 'd',
            'externalid' => $params['commentid'],
        ];
    }

    public static function like_wall($params) {

        try {

            $wallpostlike = \local_wall_external::post_like($params);

            if (!$wallpostlike['data'][0]['processed']) {
                throw new customapiException('wserrorcreatewall', $params, $wallpostlike['data'][0]['record']);
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorcreatewall', $params, $err);
        }

        return [
            'entity' => 'wall',
            'crud' => 'c',
            'externalid' => $params['postid'],
        ];
    }

    public static function share_wall($params) {

        try {

            $wallshare = \local_wall_external::post_share($params);

            if (!$wallshare['data'][0]['processed']) {
                throw new customapiException('wserrorcreatewall', $params, $wallshare['data'][0]['record']);
            }
        } catch (Exception $err) {
            throw new srsapiException('wserrorcreatewall', $params, $err);
        }

        return [
            'entity' => 'wall',
            'crud' => 'c',
            'externalid' => $params['postid'],
        ];
    }
}
