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
use mod_quiz_external;
require_once($CFG->dirroot . '/course/externallib.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/classes/external.php');

class quizhelper {

    public static function read_get_quiz_start_attempt($qidinfo) {

        $qid = $qidinfo['id'];

        $activityattemptsdata = \mod_quiz_external::start_attempt($qid);
        $activityattemptsdata = \external_api::clean_returnvalue(mod_quiz_external::get_attempt_review_returns(), $activityattemptsdata);
        return [
            'records' => $activityattemptsdata,
        ];
    }

    public static function read_quiz_attempt_data($quizattemptid) {
     
        $page = $quizattemptid['page'];
        $quizattemptid = $quizattemptid['attemptid'];
        $activityattemptdata = \mod_quiz_external::get_attempt_data($quizattemptid, $page);
        return [
            'records' => $activityattemptdata,
        ];
    }

    public static function read_quiz_save_attempt($quizattemptidanddata) {

        $quizattemptid = $quizattemptidanddata['attemptid'];
        $data = $quizattemptidanddata['data'];

        $activityattemptsavedata = \mod_quiz_external::save_attempt($quizattemptid, $data);
        
        return [
            'records' => $activityattemptsavedata,
        ];
    }


    public static function read_quiz_attempt_summary($quizattemptid) {

        $quizattemptid = $quizattemptid['attemptid'];
        

        $activityattemptsummarydata = \mod_quiz_external::get_attempt_summary($quizattemptid);
        
        return [
            'records' => $activityattemptsummarydata,
        ];
    }

    public static function read_get_quiz_attempt_review($quizattemptid) {

        $quizattemptid = $quizattemptid['attemptid'];
        

        $activityattemptreviwdata = \mod_quiz_external::get_attempt_review($quizattemptid);
        
        return [
            'records' => $activityattemptreviwdata,
        ];
    }

    
    public static function read_get_quiz_process_attempt($quizattemptinfo) {
        $quizattemptid = $quizattemptinfo['attemptid'];
        $data = $quizattemptinfo['data'];
        $finishattempt = $quizattemptinfo['finishattempt'];
        $timeup = $quizattemptinfo['timeup'];
        $activityattemptprocess = \mod_quiz_external::process_attempt($quizattemptid, $data, $finishattempt, $timeup);
        
        return [
            'records' => $activityattemptprocess,
        ];
    }
}
