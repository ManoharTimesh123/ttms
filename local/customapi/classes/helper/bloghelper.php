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
use stdClass;
use Throwable;

require_once($CFG->dirroot . '/local/blog/externallib.php');

class bloghelper {

    public static function create_blog($params) {

        try {

            $file = (isset($_FILES)) ? $_FILES : null;
            $blog = \local_blog_external::blog_create($params, $file);

            if (!$blog['data'][0]['processed']) {
                throw new customapiException('wserrorcreateblog', $params, $blog['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorcreateblog', $params, $err);
        }

        return [
            'entity' => 'blog',
            'crud' => 'c',
            'externalid' => count($params),
        ];
    }

    public static function read_blog() {

        $blogs = \local_blog_external::blog_get();

        return [
            'records' => $blogs['data'],
        ];
    }

    public static function update_blog($params) {

        try {

            $file = (isset($_FILES)) ? $_FILES : null;
            $blog = \local_blog_external::blog_update($params, $file);

            if (!$blog['data'][0]['processed']) {
                throw new customapiException('wserrorupdateblog', $params, $blog['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorupdateblog', $params, $err);
        }

        return [
            'entity' => 'blog',
            'crud' => 'u',
            'externalid' => $params['blogid'],
        ];
    }

    public static function delete_blog($params) {

        try {

            $blog = \local_blog_external::blog_delete($params);

            if (!$blog['data'][0]['processed']) {
                throw new customapiException('wserrornotfundblog', $params, $blog['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorcreateblog', $params, $err);
        }

        return [
            'entity' => 'blog',
            'crud' => 'd',
            'externalid' => $params['blogid'],
        ];
    }
}
