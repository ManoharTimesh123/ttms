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
defined('MOODLE_INTERNAL') || die();

function topic_add_button($text, $url) {
    $adduserbutton = html_writer::start_tag('div');
    $adduserbutton .= html_writer::link($url, $text, array('class' => 'btn btn-primary float-right m-0 mb-3'));
    $adduserbutton .= html_writer::end_tag('div');
    return $adduserbutton;
}

function render_topics() {
    global $CFG, $OUTPUT;
    $systemcontext = context_system::instance();

    $table = new html_table();

    $tableheader = array(
        get_string('serialnumber', 'local_need_based_trainings'),
        get_string('topicname', 'local_need_based_trainings'),
        get_string('topicshortname', 'local_need_based_trainings'),
        get_string('topicdescription', 'local_need_based_trainings'),
        get_string('startdate', 'local_need_based_trainings'),
        get_string('enddate', 'local_need_based_trainings'),
        get_string('status', 'local_need_based_trainings'),
    );

    if (has_capability('local/need_based_trainings:topicadd', $systemcontext) ||
        has_capability('local/need_based_trainings:topicdelete', $systemcontext)
    ) {
        $tableheader[] = get_string('action', 'local_need_based_trainings');
    }

    $table->head = $tableheader;
    $data = [];
    $output = '';

    $topics = get_topics();

    if (!empty($topics)) {
        $i = 1;
        foreach ($topics as $topic) {
            $id = $topic->id;
            $row = array();
            $row[] = $i;
            $row[] = $topic->name;
            $row[] = $topic->shortname;
            $row[] = strip_html_tag_and_limit_character($topic->description, 200);
            $row[] = $topic->startdate;
            $row[] = $topic->enddate;
            $row[] = $topic->status;
            $actionicons = '';
            $editurl = new moodle_url($CFG->wwwroot . '/local/need_based_trainings/topics/edit.php', array('id' => $id));
            if (has_capability('local/need_based_trainings:topicadd', $systemcontext) || is_siteadmin()) {
                $actionicons .= html_writer::link(
                    $editurl,
                    html_writer::empty_tag('img', array(
                        'src' => $OUTPUT->image_url('i/edit'),
                        'title' => 'Edit',
                        'class' => 'iconsmall',
                        'width' => '14',
                        'height' => '14'
                    ))
                );
            }

            if (has_capability('local/need_based_trainings:topicdelete', $systemcontext) || is_siteadmin()) {
                $deleteurl = new moodle_url($CFG->wwwroot . '/local/need_based_trainings/topics/edit.php', array('id' => $id, 'delete' => 1));
                $actionicons .= html_writer::link(
                    $deleteurl,
                    html_writer::empty_tag('img', array(
                        'src' => $OUTPUT->image_url('i/delete'),
                        'title' => 'Delete',
                        'class' => 'iconsmall',
                        'width' => '14',
                        'height' => '14'
                    ))
                );
            }
            if ($actionicons) {
                $row[] = $actionicons;
            }
            $data[] = $row;
            $i++;
        }

        $table->data = $data;
        $table->id = 'need-based-training-topics-list';
        $needbasedtrainingdata = html_writer::table($table);
        $output .= '<div class="table-responsive">'. $needbasedtrainingdata .'</div>';
    } else {
        $output = '<div class="alert alert-info w-100 float-left">No data available</div>';
    }

    return $output;
}

function render_need_based_topics($filter) {
    $systemcontext = context_system::instance();

    $table = new html_table();

    $tableheader = array(
        get_string('serialnumber', 'local_need_based_trainings'),
        get_string('topic', 'local_need_based_trainings'),
        get_string('startdate', 'local_need_based_trainings'),
        get_string('enddate', 'local_need_based_trainings'),
        get_string('description', 'local_need_based_trainings'),
    );
    if (has_capability('local/need_based_trainings:viewall', $systemcontext)) {
        $newheaderelement = get_string('user', 'local_need_based_trainings');
        $index = 2;
        array_splice($tableheader, $index, 0, $newheaderelement);
    }
    $table->head = $tableheader;
    $data = [];
    $output = '';

    $needbasedtopics = get_need_based_requested_topics($filter);

    if (!empty($needbasedtopics)) {
        $i = 1;
        foreach ($needbasedtopics as $needbasedtopic) {

            $row = array();
            $row[] = $i;
            $row[] = $needbasedtopic->name;
            if (has_capability('local/need_based_trainings:viewall', $systemcontext)) {
                $row[] = $needbasedtopic->user;
            }
            $row[] = $needbasedtopic->startdate;
            $row[] = $needbasedtopic->enddate;
            $row[] = $needbasedtopic->description;
            $data[] = $row;
            $i++;
        }

        $table->data = $data;
        $table->id = 'need-based-topics-list';
        $needbasedtopicdata = html_writer::table($table);
        $output .= '<div class="table-responsive">'. $needbasedtopicdata .'</div>';
    } else {
        $output = '<div class="alert alert-info w-100 float-left">No data available</div>';
    }

    return $output;
}
