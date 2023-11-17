<?php
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

class block_advnotifications_external extends external_api {
    public static function get_zone_parameters()
    {
        return new external_function_parameters(
            array(
                'district_ids' => new external_value(PARAM_TEXT, 'Zone all')
            )
        );
    }

    public static function get_zone($district_ids)
    {
        GLOBAL $DB;
        $sql = "SELECT *
                FROM {local_demo_zones}
                WHERE district_id in ({$district_ids})";
        //echo $sql;
        $zones = $DB->get_records_sql($sql , null);
        if ($zones) {
            $zoneoptionshtml = '';
            foreach ($zones as $zone) {
                $zoneoptionshtml .= '<option value="'.$zone->id.'">'.$zone->name.'</option>';
            }
        }
        return [
            'data' => $zoneoptionshtml
        ];
    }

    public static function get_zone_returns()
    {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'all')
            )
        );
    }


    //Get all diet based on zone ids
    public static function get_diet_parameters()
    {
        return new external_function_parameters(
            array(
                'zone_ids' => new external_value(PARAM_TEXT, 'Diet all')
            )
        );
    }

    public static function get_diet($zone_id)
    {
        GLOBAL $DB;
        $sql = "SELECT *
                FROM {local_demo_diet}
                WHERE zone_id in ({$zone_id})";
        //echo $sql;
        $diets = $DB->get_records_sql($sql , null);
        if ($diets) {
            $dietoptionshtml = '';
            foreach ($diets as $diet) {
                $dietoptionshtml .= '<option value="'.$diet->id.'">'.$diet->name.'</option>';
            }
        }
        return [
            'data' => $dietoptionshtml
        ];
    }

    public static function get_diet_returns()
    {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'all')
            )
        );
    }

    //Get all schools based on diet ids
    public static function get_school_parameters()
    {
        return new external_function_parameters(
            array(
                'diet_ids' => new external_value(PARAM_TEXT, 'School all')
            )
        );
    }

    public static function get_school($diet_ids)
    {
        GLOBAL $DB;
        $sql = "SELECT *
                FROM {local_demo_schools}
                WHERE diet_id in ({$diet_ids})";
        //echo $sql;
        $schools = $DB->get_records_sql($sql , null);
        if ($schools) {
            $schooloptionshtml = '';
            foreach ($schools as $school) {
                $schooloptionshtml .= '<option value="'.$school->id.'">'.$school->name.'</option>';
            }
        }
        return [
            'data' => $schooloptionshtml
        ];
    }

    public static function get_school_returns()
    {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'all')
            )
        );
    }

}
