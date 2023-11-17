<?php

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/blocks/course_catalog/locallib.php');

class block_course_catalog_external extends \external_api
{

    public static function get_courses_by_category_id_parameters()
    {
        return new external_function_parameters(
            array(
                'categoryid' => new external_value(PARAM_INT, 'category id')
            )
        );
    }

    public static function get_courses_by_category_id($categoryid)
    {
        $courses = get_course_by_category_id($categoryid);
        if (!empty($courses)) {
            return [
                'processed' => true,
                'record' => json_encode($courses)
            ];
        } else {
            return [
                'processed' => false,
                'record' => ''
            ];
        }
    }

    public static function get_courses_by_category_id_returns()
    {
        return new external_single_structure(
            array(
                'processed' => new external_value(PARAM_BOOL, 'true false'),
                'record' => new external_value(PARAM_RAW, 'courses')
            )
        );
    }
}
