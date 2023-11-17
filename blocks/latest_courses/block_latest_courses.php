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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/latest_courses/renderer.php');


class block_latest_courses extends block_base {

    /**
     * block initializations
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_latest_courses');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function hide_header() {
        return true;
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        // Never useful unless you are logged in as real users.
        if (!isloggedin() || isguestuser()) {
            return '';
        }
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->text .= render_latest_course();
        $this->content->footer = '';
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

    /**
     * for block external use
     *
     */
    public function external_latest_courses() {
        $latestcourse = render_latest_course();
        return $latestcourse;

    }
}
