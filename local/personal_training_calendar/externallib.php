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
 * PLUGIN external file
 *
 * @package    component
 * @category   external
 * @copyright  20XX by lalit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/local/personal_training_calendar/locallib.php');
class local_personal_training_calendar_external extends external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_personal_training_calendar_parameters() {
        return new external_function_parameters
        ([
            'trainingfilter' => new external_single_structure([
                'freetextsearch' => new external_value(PARAM_RAW, 'For free text search', VALUE_OPTIONAL, ''),
                'trainingdate' => new external_value(PARAM_RAW, 'For training date', VALUE_OPTIONAL, ''),
                'coursemode' => new external_value(PARAM_RAW, 'For course mode', VALUE_OPTIONAL, ''),
                'startdate' => new external_value(PARAM_RAW, 'Startdate', VALUE_OPTIONAL, ''),
                'enddate' => new external_value(PARAM_RAW, 'enddate', VALUE_OPTIONAL, ''),
                'trainingid' => new external_value(PARAM_RAW, 'trainingid', VALUE_OPTIONAL, 0),
            ])
        ]);
    }
    /**
     * Return information about a course module.
     *
     * @param int $id the course module id
     * @return array of warnings and the course module
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function get_personal_training_calendar($trainingfilter) {
        global $CFG, $USER;
        // Validate parameter.

        $params = self::validate_parameters(self::get_personal_training_calendar_parameters(), ['trainingfilter' => $trainingfilter['trainingfilter']]);
        $params = $params['trainingfilter'];
        $filterprams = new stdClass();
        foreach ($params as $key => $value) {
            $filterprams->$key = $value;
        }
        $tablefilterdata = get_personal_training_calendar($filterprams, $USER->id, $params['trainingid']);
        $personaltrainingcalenderdata = [];

        $sno = 1;
        foreach ($tablefilterdata as $personaltrainingcalendertablerows) {
            $data = [];
            $data['sno'] = $sno;
            $data['trainingid'] = $personaltrainingcalendertablerows->trainingid;
            $data['trainingname'] = $personaltrainingcalendertablerows->trainingname;
            $data['trainingtype'] = $personaltrainingcalendertablerows->trainingtype;
            $data['startdate'] = $personaltrainingcalendertablerows->startdate;
            $data['enddate'] = $personaltrainingcalendertablerows->enddate;
            $data['noofdays'] = $personaltrainingcalendertablerows->days;
            $data['traningdays'] = $personaltrainingcalendertablerows->trainingdays;
            $data['abouttraining'] = $personaltrainingcalendertablerows->summary;
            $data['trainingvenue'] = $personaltrainingcalendertablerows->venue;
            $data['cyclebatchid'] = $personaltrainingcalendertablerows->cycle;
            $data['status'] = $personaltrainingcalendertablerows->status;
            $data['facilitator'] = $personaltrainingcalendertablerows->facilitator;
            $data['coordinator'] = $personaltrainingcalendertablerows->coordinator;

            $personaltrainingcalenderdata[] = $data;
            $sno++;
        }
        return [
            'personaltrainingcalendertabledata' => $personaltrainingcalenderdata
        ];
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_personal_training_calendar_returns() {
        $params = [
            'personaltrainingcalendertabledata' => new external_multiple_structure(
                new external_single_structure([
                    'sno' => new external_value(PARAM_RAW, get_string('sno', 'local_personal_training_calendar')),
                    'trainingname' => new external_value(PARAM_TEXT, get_string('coursename', 'local_personal_training_calendar')),
                    'trainingtype' => new external_value(PARAM_RAW, get_string('type', 'local_personal_training_calendar')),
                    'startdate' => new external_value(PARAM_RAW, get_string('startdate', 'local_personal_training_calendar')),
                    'enddate' => new external_value(PARAM_RAW, get_string('enddate', 'local_personal_training_calendar')),
                    'noofdays' => new external_value(PARAM_RAW, get_string('noofdays', 'local_personal_training_calendar')),
                    'traningdays' => new external_value(PARAM_RAW, get_string('traningdays', 'local_personal_training_calendar')),
                    'abouttraining' => new external_value(PARAM_RAW, get_string('coursesummary', 'local_personal_training_calendar')),
                    'trainingvenue' => new external_value(PARAM_RAW, get_string('venue', 'local_personal_training_calendar')),
                    'cyclebatchid' => new external_value(PARAM_RAW, get_string('cycle/batchid', 'local_personal_training_calendar')),
                    'status' => new external_value(PARAM_RAW, get_string('status', 'local_personal_training_calendar')),
                    'facilitator' => new external_value(PARAM_RAW, get_string('facilitator', 'local_personal_training_calendar')),
                    'coordinator' => new external_value(PARAM_RAW, get_string('coordinator', 'local_personal_training_calendar'))
                ])
            ),
        ];
        return new external_single_structure($params);
    }
}
