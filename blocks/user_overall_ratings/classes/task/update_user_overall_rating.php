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

namespace block_user_overall_ratings\task;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/user_overall_ratings/locallib.php');

class update_user_overall_rating extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('updateuseroverallrating', 'block_user_overall_ratings');
    }

    /**
     * Run task for updating user overall ratings.
     */
    public function execute() {
        update_user_overall_rating();
    }

}
