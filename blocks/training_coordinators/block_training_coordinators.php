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
 * @package    block_training_coordinators
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

class block_training_coordinators extends block_base {
    /**
     * block initializations
     */

    public function init() {
        $this->title = get_string('pluginname', 'block_training_coordinators');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $CFG, $DB, $USER, $OUTPUT;

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
        $systemcontext = context_system::instance();

        $course = $this->page->course;
        $coordinatorroleid = $DB->get_field('role', 'id', ['shortname' => 'coordinator']);
        $viewall = false;

        if (is_siteadmin() || has_capability('moodle/category:manage', $systemcontext)) {
            $viewall = true;
            $groups = groups_get_all_groups($course->id); 
        } else {
            $groups = groups_get_all_groups($course->id, $USER->id);
        }

        $context = context_course::instance($course->id);
        $userexist = [];

        if (has_capability('block/training_coordinators:view', $context) || $viewall) {
            foreach ($groups as $group) {
                $allgroupusers = get_enrolled_users($context, '', $group->id);
                foreach ($allgroupusers as $allgroupuser) {
                    if ($DB->record_exists('role_assignments', ['userid' => $allgroupuser->id, 'roleid' => $coordinatorroleid, 'contextid' => $context->id])) {
                        if (!in_array($allgroupuser->id, $userexist)) {
                            $this->content->text .= '<div class="bg-white rounded-lg p-3 shadow-sm mb-3">';
                            $userexist[] = $allgroupuser->id;
                            $this->content->text .= '<div class="media">';
                            if ($allgroupuser->username) {
                                $this->content->text .= $OUTPUT->user_picture($allgroupuser, array('size' => 50)) . '</br>';
                            }

                            $this->content->text .= '<div class="media-body ml-1">';
                            if ($allgroupuser->firstname) {
                                $this->content->text .= '<h5 class="font-weight-bold"> ' . $allgroupuser->firstname . '</h5>';
                            }

                            if ($allgroupuser->email) {
                                $this->content->text .= '<p class="mb-0"> ' . $allgroupuser->email . '</p>';
                            }

                            $this->content->text .= '</div></div></div>';
                        }
                    }
                }
            }
        }
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
