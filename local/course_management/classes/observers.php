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
 * Local stuff for category enrolment plugin.
 *
 * @package    local_course_management
 * @copyright  Lalit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_management;

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__DIR__).'/lib.php');
class observers {
    public static function delete_local_course_relation(\core\event\course_deleted $event) {
            if (empty($event)) {
                return;
            } else {
                $coursedetail = $event->get_data();
                delete_course_relation($coursedetail['objectid']);
            }
    }
}
