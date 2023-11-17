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
 * Block displaying information about current logged-in user.
 *
 * This block can be used as anti cheating measure, you
 * can easily check the logged-in user matches the person
 * operating the computer.
 *
 * @package    block_myprofile
 * @copyright  2010 Remote-Learner.net
 * @author     Olav Jordan <olav.jordan@remote-learner.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Displays the current user's profile information.
 *
 * @copyright  2010 Remote-Learner.net
 * @author     Olav Jordan <olav.jordan@remote-learner.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/local/annual_training_calendar/locallib.php');
require_once($CFG->dirroot . '/blocks/training_counts/locallib.php');

class block_training_counts extends block_base {
    /**
     * block initializations
     */

    public function init() {
        $this->title = get_string('pluginname', 'block_training_counts');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $CFG;

        $systemcontext = context_system::instance();

        if ($this->content !== null) {
            return $this->content;
        }

        if (!isloggedin() || isguestuser()) {
            $this->content->text = '';
            return $this->content;      // Never useful unless you are logged in as real users.
        }

        if (!has_capability('block/training_counts:viewcontent', $systemcontext)) {
            $this->content->text = '';
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $course = $this->page->course;

        $this->content->text .= '<div class="row training_block_section">';

        $upcomingtrainingurl = new moodle_url($CFG->wwwroot . '/local/annual_training_calendar', array(
            'sesskey' => sesskey(),
            '_qf__annual_training_calendar_filter_form' => 1,
            'trainingtype' => '_qf__force_multiselect_submission',
            'trainingtype[]' => 'upcoming'
        ));
        $upcomingtrainings = get_string('upcoming_trainings', 'block_training_counts');
        $upcomingtrainingscount = get_upcoming_training_count();

        $completedtrainingurl = new moodle_url($CFG->wwwroot . '/local/annual_training_calendar', array(
            'sesskey' => sesskey(),
            '_qf__annual_training_calendar_filter_form' => 1,
            'trainingtype' => '_qf__force_multiselect_submission',
            'trainingtype[]' => 'past'
        ));
        $completedtrainings = get_string('completed_trainings', 'block_training_counts');
        $completedtrainingscount = get_completed_training_count();

        $ongoingtrainingurl = new moodle_url($CFG->wwwroot . '/local/annual_training_calendar', array(
            'sesskey' => sesskey(),
            '_qf__annual_training_calendar_filter_form' => 1,
            'trainingtype' => '_qf__force_multiselect_submission',
            'trainingtype[]' => 'ongoing'
        ));
        $ongoingtrainings = get_string('ongoing_trainings', 'block_training_counts');
        $ongoingtrainingscount = get_ongoing_training_count();

        $courseurl = new moodle_url($CFG->wwwroot . '/blocks/course_catalog/listing.php/');
        $courses = get_string('courses', 'block_training_counts');
        $coursescount = get_total_courses_count();

        $trainingscreateurl = new moodle_url($CFG->wwwroot . '/local/annual_training_calendar');
        $trainingscreated = get_string('trainingscreated', 'block_training_counts');
        $trainingscreatedcount = get_created_training_count();

        if (has_capability('block/training_counts:viewtrainingscreated', $systemcontext)) {
            $this->content->text .=
                '<div class="col-md-6 col mb-3 ">
                <a href="' . $trainingscreateurl . '">
                    <div class="training_counts ccnDashBl text-center px-2 py-4">
                        <span class="count font-weight-bold display-4">' . $trainingscreatedcount . '</span>
                        <div class="label">' . $trainingscreated . '</div>
                    </div>
                </a>
            </div>
            ';
        }

        if (has_capability('block/training_counts:viewcompletedtrainings', $systemcontext)) {
            $this->content->text .=
                '<div class="col-md-6 col mb-3">
                <a href="' . $completedtrainingurl . '">
                    <div class="training_counts ccnDashBl text-center px-2 py-4">
                        <span class="count font-weight-bold display-4">' . $completedtrainingscount . '</span>
                        <div class="label">' . $completedtrainings . '</div>
                    </div>
                </a>
            </div>';
        }


        if (has_capability('block/training_counts:viewupcomingtrainings', $systemcontext)) {
            $this->content->text .= '
            <div class="col-md-6 col  mb-3">
                <a href="' . $upcomingtrainingurl . '">
                    <div class="training_counts ccnDashBl text-center px-2 py-4">
                        <span class="count font-weight-bold display-4">' . $upcomingtrainingscount . '</span>
                        <div class="label">' . $upcomingtrainings . '</div>
                    </div>
                </a>
            </div>';
        }

        if (has_capability('block/training_counts:viewongoingtrainings', $systemcontext)) {
            $this->content->text .=
            '<div class="col-md-6 col mb-3">
                <a href="' . $ongoingtrainingurl . '">
                    <div class="training_counts ccnDashBl text-center px-2 py-4">
                        <span class="count font-weight-bold display-4">' . $ongoingtrainingscount . '</span>
                        <div class="label">' . $ongoingtrainings . '</div>
                    </div>
                </a>
            </div>';
        }

        if (has_capability('block/training_counts:viewcoursescount', $systemcontext)) {
            $this->content->text .=
                '<div class="col-md-6 col mb-3 ">
                <a href="' . $courseurl . '">
                    <div class="training_counts ccnDashBl text-center px-2 py-4">
                        <span class="count font-weight-bold display-4">' . $coursescount . '</span>
                        <div class="label">' . $courses . '</div>
                    </div>
                </a>
            </div>
            ';
        }



        $this->content->text .= '</div>';

        /* Get current active role. if role is not equal to instructor then dont proceed. return empty string */

        return $this->content;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }

    /**
     * allow more than one instance of the block on a page
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        // Allow more than one instance on a page.
        return false;
    }

    /**
     * allow instances to have their own configuration
     *
     * @return boolean
     */
    public function instance_allow_config() {
        // Allow instances to have their own configuration.
        return false;
    }

    /**
     * instance specialisations (must have instance allow config true)
     *
     */
    public function specialization() {
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    /**
     * post install configurations
     *
     */
    public function after_install() {
    }

    /**
     * post delete configurations
     *
     */
    public function before_delete() {
    }

}
