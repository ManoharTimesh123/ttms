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
 * @package    training_transcript
 * @category   external
 * @copyright  20XX by lalit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/training_transcript/locallib.php');

class local_training_transcript_external extends \external_api {

    public static function get_training_transcript($filters) {
        $params = $filters['trainingtranscript'];

        $filterparams = new stdClass();

        foreach ($params as $key => $value) {

            if ($key == 'startdate' || $key == 'enddate') {
                $filterparams->$key = strtotime($value);
            } else if ($key == 'trainingmode' && !empty($value)) {
                $filterparams->{$key}[] = $value;
            } else {
                $filterparams->$key = $value;
            }
        }

        $trainingtranscripts = get_training_transcript($filterparams, $params['trainingid']);

        return [
            'data' => $trainingtranscripts
        ];
    }
}
