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
 * Announcement
 * @package local_announcement
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/filelib.php');
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/announcement/locallib.php');
require_once($CFG->dirroot . '/local/announcement/classes/announcement.php');

class local_announcement_external extends \external_api {

    public static function announcement_get_parameters() {

        return new external_function_parameters([
            'data' => new external_single_structure([
                'announcementid' => new \external_value(PARAM_INT, 'announcement id', VALUE_OPTIONAL),
            ])
        ]);
    }

    public static function announcement_get() {

        $announcementsdata = \announcement\local_announcement::get_announcement_based_on_user();

        $context = context_system::instance();

        $formattedannouncementdata = array();

        if (!empty($announcementsdata)) {

            foreach ($announcementsdata as $announcement) {

                $imageurl = \announcement\local_announcement::getimageurl($announcement, $context->id);

                $announcementdata = array();
                $announcementdata['title'] = $announcement->title;
                $announcementdata['description'] = $announcement->description;
                $announcementdata['image'] = $imageurl;
                $formattedannouncementdata[] = $announcementdata;
            }
        }

        return [
            'data' => $formattedannouncementdata
        ];

    }

    public static function announcement_get_returns() {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'announcement data')
            )
        );
    }
}
