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
 * External Venue Request API
 *
 * @package    blocks_venue_request
 * @category   external
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/blocks/venue_requests/locallib.php');

class block_venue_requests_external extends \external_api {
    public static function update_venue_request_parameters() {
        return new external_function_parameters(
            array(
                'venuerequestid' => new external_value(PARAM_INT, 'venue request id')
            )
        );
    }

    public static function update_venue_request($venuerequestid) {
        $update = update_venue_request_status($venuerequestid);
        if ($update) {
            return [
                'processed' => true
            ];
        } else {
            return [
                'processed' => false
            ];
        }
    }

    public static function update_venue_request_returns() {
        return new external_single_structure(
            array(
                'processed' => new external_value(PARAM_BOOL, 'true false')
            )
        );
    }
}
