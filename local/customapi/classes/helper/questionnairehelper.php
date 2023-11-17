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
require_once($CFG->dirroot . '/mod/questionnaire/externallib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/course/externallib.php');
use local_customapi\exception\customapiException;
use stdClass;
use Throwable;
use mod_questionnaire_external;

class questionnairehelper {

    public static function add_user_questionnaire_responses($questionnaireinfo) {
        global $USER;

        $questionnaireid = $questionnaireinfo['questionnaireid'];
        $surveyid = $questionnaireinfo['surveyid'];
        $userid = $USER->id;
        $cmid = $questionnaireinfo['cmid'];
        $sec = $questionnaireinfo['sec'];
        $completed = $questionnaireinfo['completed'];
        $rid = $questionnaireinfo['rid'];
        $submit = $questionnaireinfo['submit'];
        $action = $questionnaireinfo['action'];
        $responses = $questionnaireinfo['responses'];

        $questionnairedata = \mod_questionnaire_external::submit_questionnaire_response($questionnaireid, $surveyid, $userid, $cmid, $sec, $completed, $rid, $submit, $action, $responses);

        return [
            'records' => $questionnairedata,
        ];
    }

    public static function read_view_questionnaire_data($questionnaireinfo) {

        $questionnaireid = $questionnaireinfo['questionnaireid'];
        $cmid = $questionnaireinfo['cmid'];
        $questionnairedata = self::get_questionnaire_view_details($questionnaireid, $cmid);
        return [
            'records' => $questionnairedata,
        ];
    }

    public function get_questionnaire_view_details($questionnaireid, $cmid) {
        global $DB, $COURSE, $CFG;

        require_once($CFG->dirroot.'/mod/questionnaire/questionnaire.class.php');
        $course = $COURSE;
        list($cmid, $course, $questionnaire) = questionnaire_get_standard_page_items($cmid, $questionnaireid);
        
        $questionnaire = new \questionnaire($course, $cmid, 0, $questionnaire);
      
        $cmdetail = $questionnaire->cm;
        $cminfo = array();
        $cminfo['id'] = $cmdetail->id;
        $cminfo['course'] = $cmdetail->course;
        $cminfo['module'] = $cmdetail->module;
        $cminfo['name'] = $cmdetail->name;
        $cminfo['modname'] = $cmdetail->modname;
        $cminfo['instance'] = $cmdetail->instance;
        $cminfo['section'] = $cmdetail->section;
        $cminfo['sectionnum'] = $cmdetail->sectionnum;
        $cminfo['groupmode'] = $cmdetail->groupmode;
        $cminfo['groupingid'] = $cmdetail->groupingid;
        $cminfo['completion'] = $cmdetail->completion;
       
        // Survey.
        $surveyinfo = $questionnaire->survey;
        $surveydetails = array();
        $surveydetails['id'] = $surveyinfo->id;
        $surveydetails['name'] = $surveyinfo->name;
        $surveydetails['courseid'] = $surveyinfo->course;

        // Course.
        $courseinformation = $questionnaire->course;
        $coursedetails = array();
        $coursedetails['id'] = $courseinformation->id;
        $coursedetails['categoryid'] = $courseinformation->category;
        $coursedetails['fullname'] = $courseinformation->fullname;
        $coursedetails['shortname'] = $courseinformation->shortname;
        $coursedetails['idnumber'] = $courseinformation->idnumber;
        $coursedetails['summary'] = $courseinformation->summary;
        $coursedetails['summaryformat'] = $courseinformation->summaryformat;
        $coursedetails['format'] = $courseinformation->format;
        $coursedetails['visible'] = $courseinformation->visible;
        $coursedetails['enablecompletion'] = $courseinformation->enablecompletion;
       
        // Question.
        $questioninforamtions = $questionnaire->questions;
        $allquestion = array();
        foreach ($questioninforamtions as $qid => $question) {
            $questiondetails = array();
            $questiondetails['id'] = $question->id;
            $questiondetails['surveyid'] = $question->surveyid;
            $questiondetails['name'] = $question->name;
            $questiondetails['type'] = $question->type;
            $questiondetails['choices'] = $question->choices;
            $questiondetails['type_id'] = $question->type_id;
            $allquestion[] = $questiondetails;
        }

        $returndata = [];
        $returndata['id'] = $questionnaire->id;
        $returndata['name'] = $questionnaire->name;
        $returndata['sid'] = $questionnaire->sid;
        $returndata['assigned_user_role_id'] = $questionnaire->assigned_user_role_id;
        $returndata['survey'] = $surveydetails;
        $returndata['cm'] = $cminfo;
        $returndata['course'] = $coursedetails;
        $returndata['questions'] = $allquestion;
     
        return $returndata;
    }
}
