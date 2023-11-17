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
 * External Annual Training Calender API
 *
 * @package    local_annual_training_calender
 * @category   external
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/filelib.php');
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/news/locallib.php');
require_once($CFG->dirroot . '/local/news/classes/news.php');

class local_news_external extends \external_api {

    public static function news_create_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'title' => new external_value(PARAM_TEXT, 'title'),
                'description' => new external_value(PARAM_TEXT, 'description'),
                'startdate' => new external_value(PARAM_TEXT, 'start date'),
                'enddate' => new external_value(PARAM_TEXT, 'end date'),
                'file' => new external_value(PARAM_FILE, 'file', VALUE_OPTIONAL),
            ])
        ]);
    }

    public static function news_create($data, $file) {
        global $USER;

        $params = self::validate_parameters(self::news_create_parameters(), ['data' => $data]);

        $params = $params['data'];

        if (isset($file) && !empty($file)) {

            $maxfilesize = 2097152;
            $fileobject = $file;
            if ($fileobject['news']['size'][0]['file'] > $maxfilesize) {
                throw new \file_exception(get_string('newsfilesizeerror', 'local_news'));
            }

            $filename = $fileobject['news']['name'][0]['file'];

            $fileextension = pathinfo($filename, PATHINFO_EXTENSION);

            $allowedextensions = array('jpg', 'jpeg', 'png');

            if (!in_array($fileextension, $allowedextensions)) {
                throw new \file_exception(get_string('newsfileextensionerror', 'local_news'));
            }

        } else {
            $fileobject = null;
        }

        $author = $USER->firstname.' '.$USER->lastname;
        $loggedinuserid = $USER->id;

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $params['startdate'])) {
            throw new \moodle_exception(get_string('newsincaliddateformaterror', 'local_news'), 'local_news', '');
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $params['enddate'])) {
            throw new \moodle_exception(get_string('newsincaliddateformaterror', 'local_news'), 'local_news', '');
        }

        $postdata = new stdClass();
        $postdata->description['text'] = $params['description'];
        $postdata->title = $params['title'];
        $postdata->datefrom = strtotime($params['startdate']);
        $postdata->dateto = strtotime($params['enddate']);

        try {
            $newsid = add_news($postdata);
            if ($fileobject && $newsid) {
                self::upload_file($fileobject, $newsid, $loggedinuserid, $author, 1, 'local_news', 'attachment', $maxfilesize);
            }
            return [
                'data' => [
                    [
                        'processed' => true,
                        'record' => 'Record created.'
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

        $filename = $fileobject['news']['name'][0]['file'];
        $filetype = $fileobject['news']['type'][0]['file'];
        $filepath = $fileobject['news']['tmp_name'][0]['file'];

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

    public static function news_create_returns() {
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

    public static function news_get_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'schoolid' => new \external_value(PARAM_INT, 'school id', VALUE_OPTIONAL),
            ])
        ]);
    }

    public static function news_get() {

        $newsdata = \news\local_news::getnews();

        $context = context_system::instance();

        $formattednewsdata = array();

        if (!empty($newsdata)) {
            foreach ($newsdata as $news) {
                $newsdata = array();
                $imageurl = \news\local_news::getimageurl($news, $context->id);

                $newsdata['title'] = $news->title;
                $newsdata['description'] = $news->description;
                $newsdata['image'] = $imageurl;
                $formattednewsdata[] = $newsdata;
            }
        }

        return [
            'data' => $formattednewsdata
        ];

    }

    public static function news_get_returns() {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'news data')
            )
        );
    }

    public static function news_update_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'newsid' => new external_value(PARAM_INT, 'id'),
                'title' => new external_value(PARAM_TEXT, 'title'),
                'description' => new external_value(PARAM_TEXT, 'description'),
                'startdate' => new external_value(PARAM_TEXT, 'start date'),
                'enddate' => new external_value(PARAM_TEXT, 'end date'),
                'file' => new external_value(PARAM_FILE, 'file', VALUE_OPTIONAL),
            ])
        ]);
    }

    public static function news_update($data, $file) {
        global $USER;

        $params = self::validate_parameters(self::news_update_parameters(), ['data' => $data]);

        $params = $params['data'];

        if (isset($file) && !empty($file)) {

            $maxfilesize = 2097152;
            $fileobject = $file;
            if ($fileobject['news']['size'][0]['file'] > $maxfilesize) {
                throw new \file_exception(get_string('newsfilesizeerror', 'local_news'));
            }

            $filename = $fileobject['news']['name'][0]['file'];

            $fileextension = pathinfo($filename, PATHINFO_EXTENSION);

            $allowedextensions = array('jpg', 'jpeg', 'png');

            if (!in_array($fileextension, $allowedextensions)) {
                throw new \file_exception(get_string('newsfileextensionerror', 'local_news'));
            }

        } else {
            $fileobject = null;
        }

        $author = $USER->firstname.' '.$USER->lastname;
        $loggedinuserid = $USER->id;

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $params['startdate'])) {
            throw new \moodle_exception(get_string('newsincaliddateformaterror', 'local_news'), 'local_news', '');
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $params['enddate'])) {
            throw new \moodle_exception(get_string('newsincaliddateformaterror', 'local_news'), 'local_news', '');
        }

        $postdata = new stdClass();
        $postdata->id = $params['newsid'];
        $postdata->description['text'] = $params['description'];
        $postdata->title = $params['title'];
        $postdata->datefrom = strtotime($params['startdate']);
        $postdata->dateto = strtotime($params['enddate']);

        try {
            $newsid = add_news($postdata);
            if ($fileobject && $newsid) {
                self::upload_file($fileobject, $newsid, $loggedinuserid, $author, 1, 'local_news', 'attachment', $maxfilesize);
            }
            return [
                'data' => [
                    [
                        'processed' => true,
                        'record' => 'Record created.'
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

    public static function news_update_returns() {
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

    public static function news_delete_parameters() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'newsid' => new \external_value(PARAM_INT, 'post id'),
            ])
        ]);
    }

    public static function news_delete($data) {
        global $USER;

        $params = self::validate_parameters(self::news_delete_parameters(), ['data' => $data]);
        $params = $params['data'];
        try {
            delete_news($params['newsid']);
            return [
                'data' => [
                    [
                        'processed' => true,
                        'record' => 'Record deleted.'
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

    public static function news_delete_returns() {
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
