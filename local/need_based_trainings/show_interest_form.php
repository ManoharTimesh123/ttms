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
 *
 * @package    local_need_based_trainings
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class show_interest_filter_form extends moodleform {
    /**
     * Form definition
     */
    public function definition () {

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $mform->addElement('html', '<div class="customized-field">');
        $mform->addElement('header', 'need_based_trainings', get_string('filter', 'local_need_based_trainings'), '');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-xl-6">');

        $options = array('multiple' => false, 'noselectionstring' => get_string('select'), 'class' => 'nbttraining');
        $coursesdata = get_need_based_training();
        $courses = [];
        $courses[] = '';
        foreach ($coursesdata as $course) {
            $courses[$course->id] = $course->fullname;
        }
        $mform->addElement('autocomplete', 'course', get_string('course', 'local_need_based_trainings'), $courses, $options);

        // Training detail html start.
        if ($data->course) {
            $training = get_training_by_id($data->course);

            $mform->addElement('html', '<div class="row">');

            $mform->addElement('html', '<div class="col-sm-6"><label class="bold">Training Type</label></div>');
            $mform->addElement('html', '<div class="col-sm-6">' . $training->coursetype . '</div>');

            $mform->addElement('html', '<div class="col-sm-6"><label class="bold">Start Date</label></div>');
            $mform->addElement('html', '<div class="col-sm-6">' . $training->startdate . '</div>');

            $mform->addElement('html', '<div class="col-sm-6"><label class="bold">End Date</label></div>');
            $mform->addElement('html', '<div class="col-sm-6">' . $training->enddate . '</div>');

            $mform->addElement('html', '<div class="col-sm-6"><label class="bold">Description</label></div>');
            $mform->addElement('html', '<div class="col-sm-6">' . $training->summary . '</div>');

            $mform->addElement('html', '<div class="col-sm-6"><label class="bold">Image</label></div>');
            $mform->addElement('html', '<div class="col-sm-6">' . $training->image . '</div>');

            $mform->addElement('html', '</div>');

            // Hidden optional params.
            $mform->addElement('html', '<div id="trainingid" style="display:none">' . $training->id . '</div>');

        }
        // Training detail html end.

        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-xl-6">');
        $options = array('multiple' => false, 'noselectionstring' => get_string('select'));
        $topicsdata = get_topics();
        $topics = [];
        $topics[] = '';
        foreach ($topicsdata as $topic) {
            $topics[$topic->id] = $topic->name;
        }

        $mform->addElement('autocomplete', 'topic', get_string('selecttopic', 'local_need_based_trainings'), $topics, $options);

        // Topic detail html start.
        if ($data->topic) {
            $topic = get_topic_by_id($data->topic);

            $mform->addElement('html', '<div class="row">');

            $mform->addElement('html', '<div class="col-sm-6"><label class="bold">Shortname</label></div>');
            $mform->addElement('html', '<div class="col-sm-6">' . $topic->shortname . '</div>');

            $mform->addElement('html', '<div class="col-sm-6"><label class="bold">Start Date</label></div>');
            $mform->addElement('html', '<div class="col-sm-6">' . $topic->startdate . '</div>');

            $mform->addElement('html', '<div class="col-sm-6"><label class="bold">End Date</label></div>');
            $mform->addElement('html', '<div class="col-sm-6">' . $topic->enddate . '</div>');

            $mform->addElement('html', '<div class="col-sm-6"><label class="bold">Description</label></div>');
            $mform->addElement('html', '<div class="col-sm-6">' . $topic->description . '</div>');

            $mform->addElement('html', '</div>');

            // Hidden optional params.
            $mform->addElement('html', '<div id="topicid" style="display:none">' . $topic->id . '</div>');

        }
        // Topic detail html end.

        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>');

        $this->add_action_buttons(true, get_string('searchsubmitbutton', 'local_need_based_trainings'));
        $mform->addElement('html', '</div>');
    }
}

