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

namespace local_customapi\helper;

use local_customapi\exception\customapiException;
use stdClass;
use Throwable;

require_once($CFG->dirroot . '/course/externallib.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/classes/external.php');

class traininghelper {

    public static function read_training($course) {
        global $USER;

        $courseid = $course['courseid'];

        $trainingdata = \core_course_external::get_course_contents($courseid);

        return [
            'records' => $trainingdata,
        ];
    }

    public static function read_training_activity($cmidinfo) {
    	
        $cmid = $cmidinfo['cmid'];
        $trainingactivitydata = \core_course_external::get_course_module($cmid);

        return [
            'records' => $trainingactivitydata['cm'],
        ];
    }
}
