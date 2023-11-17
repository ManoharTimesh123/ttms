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
 * @package    local_need_based_trainings
 */

function get_need_based_training() {
    global $DB;

    $trainingsql = '
            SELECT c.id, c.*, lm.name coursetype
            FROM {course} c
            JOIN {local_course_details} lcd ON lcd.course = c.id
            JOIN {local_modality} lm ON lm.id = lcd.modality
            JOIN {local_batching} lb ON lb.course = c.id
            WHERE c.visible = 1 AND lb.status = "proposed"
            ';

    $trainingsdata = $DB->get_records_sql($trainingsql);

    return $trainingsdata;
}

function get_training_by_id($trainingid) {
    global $DB, $CFG;

    $trainingsql = '
            SELECT c.id, c.*, lm.name coursetype
            FROM {course} c
            JOIN {local_course_details} lcd ON lcd.course = c.id
            JOIN {local_modality} lm ON lm.id = lcd.modality
            JOIN {local_batching} lb ON lb.course = c.id
            WHERE c.visible = 1 AND c.id = :trainingid
            ';

    $params = [
        'trainingid' => $trainingid
    ];
    $trainingsdata = $DB->get_record_sql($trainingsql, $params);

    if ($trainingsdata) {
        $course = (object)[
            'id' => $trainingsdata->id
        ];

        $imageurl = \core_course\external\course_summary_exporter::get_course_image($course);
        if (!$imageurl) {
            $imageurl = $CFG->wwwroot . '/theme/edumy/pix/default-image.jpg';
        }

        $trainingsdata = (array)$trainingsdata;
        $trainingsdata['image'] = '<img class="border rounded w-50" src="'. $imageurl .'" >';
        $trainingsdata['startdate'] = customdateformat('DATE_WITHOUT_TIME', $trainingsdata['startdate']);
        $trainingsdata['enddate'] = customdateformat('DATE_WITHOUT_TIME', $trainingsdata['enddate']);

        return (object)$trainingsdata;
    }

    return false;
}

function check_training_already_requested($trainingid) {
    global $USER, $DB;

    $trainingsql = '
            SELECT * FROM {local_nbt_training_interests}
            WHERE course = :trainingid AND user = :user
            ';

    $params = [
        'trainingid' => $trainingid,
        'user' => $USER->id
    ];

    $trainingsdata = $DB->get_record_sql($trainingsql, $params);

    if (!empty($trainingsdata)) {
        return true;
    }

    return false;
}

function add_record_in_need_based_training($trainingobject) {
    global $DB;

    $recordadded = $DB->insert_record('local_nbt_training_interests', $trainingobject);

    if ($recordadded) {
        return true;
    }

    return false;
}

function get_need_based_requested_training($filter) {
    global $USER, $DB, $CFG;

    $systemcontext = context_system::instance();

    $trainingsql = '
            SELECT c.id, c.*, lm.name coursetype, u.firstname, u.lastname
            FROM {course} c
            JOIN {local_course_details} lcd ON lcd.course = c.id
            JOIN {local_modality} lm ON lm.id = lcd.modality
            JOIN {local_nbt_training_interests} lnbt ON lnbt.course = c.id
            JOIN {user} u ON u.id = lnbt.user
            WHERE c.visible = 1
            ';
    if (!has_capability('local/need_based_trainings:viewall', $systemcontext)) {
        $trainingsql .= ' AND lnbt.user = ' . $USER->id . ' ';
    }

    if (isset($filter->course) && !empty($filter->course)) {
        $trainingsql .= ' AND c.id = '. $filter->course .' ';
    }

    $trainingsdata = $DB->get_records_sql($trainingsql);

    $needbasedtrainings = array();

    if (!empty($trainingsdata)) {
        foreach ($trainingsdata as $training) {
            $course = (object)[
                'id' => $training->id
            ];

            $imageurl = \core_course\external\course_summary_exporter::get_course_image($course);
            if (!$imageurl) {
                $imageurl = $CFG->wwwroot . '/theme/edumy/pix/default-image.jpg';
            }

            $traininglink =
                "<a href='" . new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $training->id) . "'
                            title='Go to Training' target='_blank'>" .
                $training->fullname .
                "</a>";

            $startdate = customdateformat('DATE_WITHOUT_TIME', $training->startdate);
            $enddate = customdateformat('DATE_WITHOUT_TIME', $training->enddate);

            $needbasedtraining = (object)[
                'trainingname' => $training->fullname,
                'user' => $training->firstname.' '.$training->lastname,
                'trainingimage' => $imageurl,
                'traininglink' => $traininglink,
                'trainingtype' => $training->coursetype,
                'trainingstartdate' => $startdate,
                'trainingenddate' => $enddate,
                'description' => $training->summary
            ];
            $needbasedtrainings[] = $needbasedtraining;
        }
    }

    return $needbasedtrainings;
}

function get_training_courses() {
    global $DB;

    $coursessql = '
        SELECT c.id, c.* FROM {course} c
        JOIN {local_course_details} lcd ON lcd.course = c.id
        JOIN {local_modality} lm ON lm.id = lcd.modality
        WHERE c.visible = 1  AND lm.shortname IN ("online", "offline")
        ';

    $coursessqldata = $DB->get_records_sql($coursessql);

    $courses = [];
    $courses[] = '';
    if (!empty($coursessqldata)) {
        foreach ($coursessqldata as $course) {
            $courses[$course->id] = $course->fullname;
        }
    }

    return $courses;
}

function get_topics() {
    global $DB;

    $topicsssql = '
        SELECT * FROM {local_nbt_topics}
        ORDER BY id DESC
        ';

    $topicsdata = $DB->get_records_sql($topicsssql);

    $topics = array();

    if (!empty($topicsdata)) {
        foreach ($topicsdata as $topic) {
            $startdate = '-';
            $enddate = '-';

            if ($topic->startdate) {
                $startdate = customdateformat('DATE_WITHOUT_TIME', $topic->startdate);
            }

            if ($topic->enddate) {
                $enddate = customdateformat('DATE_WITHOUT_TIME', $topic->enddate);
            }

            $topicobject = (object)[
                'id' => $topic->id,
                'name' => $topic->name,
                'shortname' => $topic->shortname,
                'startdate' => $startdate,
                'enddate' => $enddate,
                'description' => $topic->description,
                'status' => $topic->status
            ];
            $topics[] = $topicobject;
        }
    }

    return $topics;
}

function add_topic($data) {
    global $DB, $USER;

    $profdata = new stdClass();
    $profdata->name = strip_tags($data->name);
    $profdata->shortname = strip_tags($data->shortname);
    $profdata->description = $data->description['text'];
    if ($data->startdate_enabled == 1) {
        $profdata->startdate = $data->startdate;
    }

    if ($data->endate_enabled == 1) {
        $profdata->enddate = $data->enddate;
    }

    $profdata->status = $data->status;

    if ($data->id > 0) {
        $topic = $DB->get_record('local_nbt_topics', array('id' => $data->id));
        $profdata->id = $topic->id;
        $profdata->timemodified = time();
        $profdata->usermodified = $USER->id;

        $dataid = $DB->update_record('local_nbt_topics', $profdata);
    } else {
        $profdata->timecreated = time();
        $profdata->usercreated = $USER->id;
        $profdata->timemodified = $profdata->timecreated;
        $profdata->usermodified = $profdata->usercreated;
        $dataid = $DB->insert_record('local_nbt_topics', $profdata);
    }

    return $dataid;
}

function delete_topic($id) {
    global $DB;

    return $DB->delete_records('local_nbt_topics', array('id' => $id));
}

function get_topic_by_id($topicid) {
    global $DB;

    $topicsssql = '
        SELECT * FROM {local_nbt_topics}
        WHERE id = :topicid
        ';

    $params = [
        'topicid' => $topicid
    ];

    $trainingsdata = $DB->get_record_sql($topicsssql, $params);

    if ($trainingsdata) {
        $startdate = '-';
        $enddate = '-';

        if ($trainingsdata->startdate) {
            $startdate = customdateformat('DATE_WITHOUT_TIME', $trainingsdata->startdate);
        }

        if ($trainingsdata->enddate) {
            $enddate = customdateformat('DATE_WITHOUT_TIME', $trainingsdata->enddate);
        }

        $trainingsdata->startdate = $startdate;
        $trainingsdata->enddate = $enddate;

        return $trainingsdata;
    }

    return false;
}

function check_topic_already_requested($topicid) {
    global $USER, $DB;

    $topicsql = '
            SELECT * FROM {local_nbt_topic_interests}
            WHERE topic = :topicid AND user = :user
            ';

    $params = [
        'topicid' => $topicid,
        'user' => $USER->id
    ];

    $topicdata = $DB->get_record_sql($topicsql, $params);

    if (!empty($topicdata)) {
        return true;
    }

    return false;
}

function add_record_in_need_based_topic($topicobject) {
    global $DB;

    $recordadded = $DB->insert_record('local_nbt_topic_interests', $topicobject);

    if ($recordadded) {
        return true;
    }

    return false;
}

function get_need_based_requested_topics($filter) {
    global $USER, $DB, $CFG;

    $systemcontext = context_system::instance();

    $topicssql = '
            SELECT lnbtt.id, lnbtt.*, u.firstname, u.lastname
            FROM {local_nbt_topics} lnbtt
            JOIN {local_nbt_topic_interests} lnbtti ON lnbtti.topic = lnbtt.id
            JOIN {user} u ON u.id = lnbtti.user
            ';
    if (!has_capability('local/need_based_trainings:viewall', $systemcontext)) {
        $topicssql .= ' WHERE lnbtti.user = ' . $USER->id . ' ';
    }

    if (isset($filter->topic) && !empty($filter->topic)) {
        $topicssql .= ' AND lnbtt.id = '. $filter->topic .' ';
    }

    $topicsdata = $DB->get_records_sql($topicssql);

    $needbasedtopics = array();

    if (!empty($topicsdata)) {
        foreach ($topicsdata as $topic) {
            $startdate = '-';
            $enddate = '-';

            if ($topic->startdate) {
                $startdate = customdateformat('DATE_WITHOUT_TIME', $topic->startdate);
            }

            if ($topic->enddate) {
                $enddate = customdateformat('DATE_WITHOUT_TIME', $topic->enddate);
            }

            $needbasedtopic = (object)[
                'name' => $topic->name,
                'user' => $topic->firstname.' '.$topic->lastname,
                'startdate' => $startdate,
                'enddate' => $enddate,
                'description' => $topic->description,
                'status' => $topic->status,
            ];
            $needbasedtopics[] = $needbasedtopic;
        }
    }

    return $needbasedtopics;
}
