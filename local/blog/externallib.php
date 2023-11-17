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
 * External Blog API
 *
 * @package    local_blog
 * @category   external
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/filelib.php');
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/blog/locallib.php');
require_once($CFG->dirroot . '/local/blog/classes/blog.php');

class local_blog_external extends \external_api {

    public static function blog_create_parameters() {

        return new external_function_parameters([
            'data' => new external_single_structure([
                'title' => new external_value(PARAM_TEXT, 'title'),
                'description' => new external_value(PARAM_TEXT, 'description'),
                'file' => new external_value(PARAM_FILE, 'file', VALUE_OPTIONAL),
            ])
        ]);
    }

    public static function blog_create($data, $file) {
        global $USER;

        $params = self::validate_parameters(self::blog_create_parameters(), ['data' => $data]);

        $params = $params['data'];

        if (isset($file) && !empty($file)) {

            $maxfilesize = 2097152;
            $fileobject = $file;
            if ($fileobject['blog']['size'][0]['file'] > $maxfilesize) {
                throw new \file_exception(get_string('blogfilesizeerror', 'local_blog'));
            }

            $filename = $fileobject['blog']['name'][0]['file'];

            $fileextension = pathinfo($filename, PATHINFO_EXTENSION);

            $allowedextensions = array('jpg', 'jpeg', 'png');

            if (!in_array($fileextension, $allowedextensions)) {
                throw new \file_exception(get_string('blogfileextensionerror', 'local_blog'));
            }

        } else {
            $fileobject = null;
        }

        $author = $USER->firstname.' '.$USER->lastname;
        $loggedinuserid = $USER->id;

        $postdata = new stdClass();
        $postdata->description['text'] = $params['description'];
        $postdata->title = $params['title'];

        try {
            $blogid = add_post($postdata);
            if ($fileobject && $blogid) {
                self::upload_file($fileobject, $blogid, $loggedinuserid, $author, 1, 'local_blog', 'attachment', $maxfilesize);
            }
            return [
                'data' => [
                    [
                        'processed' => true,
                        'record' => get_string('recordcreated', 'local_blog')
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
        } catch (moodle_exception $e) {
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

    public static function upload_file($fileobject, $itemid, $userid, $uploadername, $contextid, $component, $filearea, $filesize) {
        global $DB;

        $filename = $fileobject['blog']['name'][0]['file'];
        $filetype = $fileobject['blog']['type'][0]['file'];
        $filepath = $fileobject['blog']['tmp_name'][0]['file'];

        $fs = get_file_storage();
        $filedata = $DB->get_record('files', array('contextid' => $contextid, 'component' => $component, 'itemid' => $itemid));

        if (!empty($filedata)) {
            $DB->delete_records('files', array('contextid' => $contextid, 'component' => $component, 'itemid' => $itemid));
        }

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

    public static function blog_create_returns() {

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

    public static function blog_get_parameters() {

        return new external_function_parameters([
            'data' => new external_single_structure([
                'blogid' => new \external_value(PARAM_INT, 'blog id', VALUE_OPTIONAL),
            ])
        ]);
    }

    public static function blog_get() {

        $posts = get_approve_post();

        $context = context_system::instance();

        $formattedpostdata = array();

        if (!empty($posts)) {

            foreach ($posts as $post) {

                $imageurl = \blog\local_blog::getimageurl($post, $context->id);

                $postdata = array();
                $postdata['title'] = $post->title;
                $postdata['description'] = $post->description;
                $postdata['image'] = $imageurl;
                $formattedpostdata[] = $postdata;
            }
        }

        return [
            'data' => $formattedpostdata
        ];

    }

    public static function blog_get_returns() {

        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'blog data')
            )
        );
    }

    public static function blog_update_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'blogid' => new external_value(PARAM_INT, 'id'),
                'title' => new external_value(PARAM_TEXT, 'title'),
                'description' => new external_value(PARAM_TEXT, 'description'),
                'file' => new external_value(PARAM_FILE, 'file', VALUE_OPTIONAL),
            ])
        ]);
    }

    public static function blog_update($data, $file) {
        global $USER;

        $params = self::validate_parameters(self::blog_update_parameters(), ['data' => $data]);

        $params = $params['data'];

        if (isset($file) && !empty($file)) {

            $maxfilesize = 2097152;
            $fileobject = $file;

            if ($fileobject['blog']['size'][0]['file'] > $maxfilesize) {
                throw new \file_exception(get_string('blogfilesizeerror', 'local_blog'));
            }

            $filename = $fileobject['blog']['name'][0]['file'];

            $fileextension = pathinfo($filename, PATHINFO_EXTENSION);

            $allowedextensions = array('jpg', 'jpeg', 'png');

            if (!in_array($fileextension, $allowedextensions)) {
                throw new \file_exception(get_string('blogfileextensionerror', 'local_blog'));
            }

        } else {
            $fileobject = null;
        }

        $author = $USER->firstname.' '.$USER->lastname;
        $loggedinuserid = $USER->id;

        $postdata = new stdClass();
        $postdata->id = $params['blogid'];
        $postdata->description['text'] = $params['description'];
        $postdata->title = $params['title'];

        try {
            $blogid = add_post($postdata);
            if ($fileobject && $blogid) {
                self::upload_file($fileobject, $blogid, $loggedinuserid, $author, 1, 'local_blog', 'attachment', $maxfilesize);
            }
            return [
                'data' => [
                    [
                        'processed' => true,
                        'record' => get_string('recordcreated', 'local_blog')
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
        } catch (moodle_exception $e) {
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

    public static function blog_update_returns() {

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

    public static function blog_delete_parameters() {

        return new external_function_parameters([
            'data' => new external_single_structure([
                'blogid' => new \external_value(PARAM_INT, 'blog id'),
            ])
        ]);
    }

    public static function blog_delete($data) {
        global $USER;

        $params = self::validate_parameters(self::blog_delete_parameters(), ['data' => $data]);

        $params = $params['data'];

        try {
            delete_post($params['blogid']);
            return [
                'data' => [
                    [
                        'processed' => true,
                        'record' => get_string('recorddeleted', 'local_blog')
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
        } catch (moodle_exception $e) {
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

    public static function blog_delete_returns() {

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

}
