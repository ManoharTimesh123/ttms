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
 * User Overall Course Rating
 *
 * @package block_user_overall_ratings
 */

// Find course rating.
function get_course_overall_rating ($userenrolledsingcourse, $userroleid) {
    $courseid = $userenrolledsingcourse->id;
    $userquestionnaireid = get_user_feedback_info($courseid, $userroleid);
    // Find all responses for respective questinnaire id and responses for given user id.
    $allquestionnaireresponses = get_user_feedback_responses($userquestionnaireid, $userroleid);
    // Calculate Overall Course rating.
    $courserating = get_course_rating($allquestionnaireresponses, $userroleid);
    return $courserating;
}
// Find user assigned feedback details.
function get_user_feedback_info ($courseid, $userroleid ) {
    global $DB;
    $sql = <<<SQL
        SELECT id FROM {questionnaire}
         WHERE course = :course
        AND assigned_user_role_id = :assigned_user_role_id
        ORDER by timemodified desc
         LIMIT 1;
    SQL;
    $params = array('course' => $courseid, 'assigned_user_role_id' => $userroleid);
    $questionnaireinfo = $DB->get_records_sql($sql, $params);
    $questionnaireid = null;
    if (!empty($questionnaireinfo)) {
        $qiddetails = array_column($questionnaireinfo, 'id');
        $questionnaireid = $qiddetails[0];
    }
    return $questionnaireid;
}
// Find users reponses.
function get_user_feedback_responses($userquestionnaireid) {
    global $DB;
    $sql = <<<SQL
            SELECT qr.id as reponseid, userid,provided_by_user_roleid, rankvalue as userrate
             FROM {questionnaire_response} qr
             JOIN {questionnaire_response_rank} qrr
            ON qr.id = qrr.response_id
            JOIN {questionnaire_quest_choice} qqc
            ON qrr.question_id = qqc.question_id
            where questionnaireid = :questionnaireid
            SQL;
    $params = ['questionnaireid' => $userquestionnaireid];
    $allquestionnairereponses = $DB->get_records_sql($sql, $params);
    $userreponsedifferntroleinfo = array();
    foreach ($allquestionnairereponses as $userresponse) {
        $userreponsedifferntroleinfo[$userresponse->provided_by_user_roleid][] = $userresponse->userrate;
    }
    return $userreponsedifferntroleinfo;
}
// Find course rating.
function get_course_rating ($allquestionnaireresponses, $userroleid) {
    global $DB;
    $allusercount = 0;
    $roleaveragerating = array();
    $finalcourserating = 0;
    foreach ($allquestionnaireresponses as $providedbyroleid => $questionnaireresponses) {
        $actualtolatrating = array_sum($questionnaireresponses);
        $tolatusercount = count($questionnaireresponses);
        $averageactualrating = $actualtolatrating / $tolatusercount;
        $params = ['provided_by_user_roleid' => $providedbyroleid, 'received_by_user_roleid' => $userroleid];
        $weightagedetails = $DB->get_record('user_ratings_weightage', $params, 'rate_weightage');
        $weightage = 0;
        if (!empty($weightagedetails)) {
            $weightage = $weightagedetails->rate_weightage;
        }
        $averageweightagerating = ($averageactualrating * $weightage) / 100;
        $roleaveragerating[$providedbyroleid] = number_format($averageweightagerating, 2);
        $allusercount = $allusercount + $tolatusercount;
    }
    if (!empty($roleaveragerating)) {
        $tolalcourserating = array_sum($roleaveragerating);
        $totalcourseuserrolecount = count($roleaveragerating);
        $averagecouserating = $tolalcourserating / $totalcourseuserrolecount;
        $finalcourserating = number_format($averagecouserating, 2);
    }
    $courseratinginfo = array('courserating' => $finalcourserating, 'totalusercount' => $allusercount);
    return $courseratinginfo;
}
// Find user roleid.
function get_user_role_id ($context, $userid) {
    $userroledetails = get_user_roles($context, $userid, true);
    $userroleidinfo = array_column($userroledetails, 'roleid');
    $userroleid = null;
    if (!empty($userroleidinfo)) {
        $userroleid = $userroleidinfo[0];
    }
    return $userroleid;
}

function update_user_overall_rating() {
    global $DB;

    $usersql = <<<SQL
            SELECT u.id FROM {user} u
           SQL;

    $users = $DB->get_records_sql($usersql);
    foreach ($users as $user) {
        // FIXME: Here we will call a function to get user overall rating.
        $useroverallrating = 1;

        $updateuseroverallratingsql = <<<SQL_QUERY
                    UPDATE {local_user_details}
                     SET overallrating = :overallrating
                    WHERE userid = :userid
                SQL_QUERY;

        $params = [
            'userid' => $user->id,
            'overallrating' => $useroverallrating
        ];

        $DB->execute($updateuseroverallratingsql, $params);
    }
}
