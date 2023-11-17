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
 * @package    block_training_overview
 * @copyright  2010 Remote-Learner.net
 * @author     Lalit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Displays the current user's profile information.
 *
 * @copyright  2010 Remote-Learner.net
 * @author     Lalit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_training_overview extends block_base {
    /**
     * block initializations
     */

    public function init() {
        $this->title = get_string('pluginname', 'block_training_overview');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        if (!isloggedin() || isguestuser()) {
            $this->content->text = '';
            return $this->content; // Never useful unless you are logged in as real users.
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $course = $this->page->course;

        $courseimgurl = \core_course\external\course_summary_exporter::get_course_image($course);
 
        $this->content->text = '<div class="media bg-white rounded-lg p-3 shadow-sm">';
        if ($courseimgurl) {
            $this->content->text .= '<img src="' . $courseimgurl . '" class="rounded" alt="course image" width="120" height="120">';
        }

        $this->content->text .= '<div class="media-body ml-3">';
        if ($course->fullname) {
            $this->content->text .= '<h4> ' . $course->fullname . '</h4>';
        }

        if ($course->summary) {
            $this->content->text .= '<p> ' . $course->summary . '</p>';
        }
        $this->content->text .= '</div></div>';
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
