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
 * Need Based Trainings
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/need_based_trainings/locallib.php');

global $DB, $USER;

$trainingid = isset($_POST['trainingid']) ? $_POST['trainingid'] : null;
$topicid = isset($_POST['topicid']) ? $_POST['topicid'] : null;

if ($_REQUEST['task'] == 'recordinsert') {

    $trainingalreadyadded = check_training_already_requested($trainingid);
    if (!$trainingalreadyadded && $trainingid) {
        $reason = $_POST['reason'];
        $currenttimestamp = time();
        $userid = $USER->id;

        $trainingobject = (object)[
            'course' => $trainingid,
            'user' => $userid,
            'reason' => $reason,
            'status' => 'Pending',
            'usercreated' => $userid,
            'timecreated' => $currenttimestamp,
        ];

        $added = add_record_in_need_based_training($trainingobject);
    }

    $topicreadyadded = check_topic_already_requested($topicid);
    if (!$topicreadyadded  && $topicid) {
        $reason = $_POST['reason'];
        $currenttimestamp = time();
        $userid = $USER->id;

        $topicobject = (object)[
            'topic' => $topicid,
            'user' => $userid,
            'reason' => $reason,
            'status' => 'Pending',
            'usercreated' => $userid,
            'timecreated' => $currenttimestamp,
        ];

        $added = add_record_in_need_based_topic($topicobject);
    }

    if ($added) {
        echo true;
        exit();
    }
    echo false;
    exit();
}

if ($_REQUEST['task'] == 'alreadyadded') {
    $trainingalreadyadded = check_training_already_requested($trainingid);
    $topicreadyadded = check_topic_already_requested($topicid);
    if (!$trainingalreadyadded || !$topicreadyadded) {
        echo false;
        exit();
    }
    echo true;
    exit();
}

if ($_REQUEST['task'] == 'gettraining') {
    $training = get_training_by_id($trainingid);

    $course = (object)[
        'id' => $trainingid
    ];

    $imageurl = \core_course\external\course_summary_exporter::get_course_image($course);
    if (!$imageurl) {
        $imageurl = $CFG->wwwroot . '/theme/edumy/pix/default-image.jpg';
    }

    $training = (array)$training;
    $training['image'] = '<img src="'. $imageurl .'" >';
    $training['startdate'] = customdateformat('DATE_WITHOUT_TIME', $training['startdate']);
    $training['enddate'] = customdateformat('DATE_WITHOUT_TIME', $training['enddate']);
    echo json_encode($training);
}

exit();
