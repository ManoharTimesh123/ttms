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
 * The Blog Management
 *
 * @package    local_blog
 */

namespace blog;

class local_blog {
    public static function getblogpost($filters, $page = null) {
        global $DB, $CFG, $USER;

        $systemcontext = \context_system::instance();

        $blogsql = <<<SQL_QUERY
            SELECT * FROM {local_blogs}
            WHERE deleted = 0
        SQL_QUERY;

        if (!has_capability('local/blog:manage', $systemcontext) &&
            !is_siteadmin() &&
            (isset($filters['manageown']) && $filters['manageown'])
        ) {
            $blogsql .= ' AND usercreated =  ' . $USER->id;
        }

        if (isset($filters['status'])) {
            $blogsql .= ' AND approved = ' . $filters['status'];
        }

        if (isset($filters['id'])) {
            $blogsql .= ' AND id =  ' . $filters['id'];
        }

        $blogsql .= ' ORDER BY  id DESC';

        if (isset($filters['limit']) && !empty($filters['limit'])) {
            $blogsql .= ' LIMIT ' . $filters['limit'];
        }

        $datacount = count($DB->get_records_sql($blogsql));

        if ($page > -1) {
            $offset = $CFG->itemperpage;
            $limit = ($page + 1 - 1) * $offset;
            $blogsql .= ' LIMIT ' . $limit . ', ' . $offset;
        }

        $data = $DB->get_records_sql($blogsql);

        $blogdata = [
            'data' => $data,
            'count' => $datacount
        ];

        return $blogdata;
    }

    public static function getimageurl($post, $contextid) {
        global $CFG;

        $postid = $post->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'local_blog', 'attachment', $postid, 'sortorder', false);
        $file = reset($files);

        if (!empty($file) && !empty($file->get_filename())) {
            $imagename = $file->get_filename();
            if ($imagename <> '.') {
                $attachmentpath = "/local_blog/attachment/" . $postid . '/' . $imagename;
                $imageurl = $CFG->wwwroot . "/pluginfile.php/" . $contextid . $attachmentpath;
            } else {
                $imageurl = '';
            }
        } else {
            $imageurl = $CFG->wwwroot . '/theme/edumy/pix/default-image.jpg';
        }

        return $imageurl;
    }

}
