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
 * @package    local_user_management
 */

namespace local_user_management;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__DIR__) . '/locallib.php');

class observers {
    public static function delete_local_user_relation(\core\event\user_deleted $event) {

        if (empty($event)) {
            return;
        } else {
            $userdetail = $event->get_data();

            soft_delete_user_relation($userdetail['objectid']);
        }
    }
}
