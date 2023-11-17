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
 * @package    block_training_schedule
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
require_once($CFG->dirroot . '/blocks/training_schedule/renderer.php');

class block_training_schedule extends block_base {
    /**
     * block initializations
     */

    public function init() {
        $this->title = get_string('pluginname', 'block_training_schedule');
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
        $facilitatorroleid = $DB->get_field('role', 'id', ['shortname' => 'facilitator']);
        $viewall = false;
        if (is_siteadmin() || has_capability('moodle/category:manage', $systemcontext)) {
            $viewall = true;
            $groups = groups_get_all_groups($course->id);
        } else {
            $groups = groups_get_all_groups($course->id, $USER->id);
        }
        $context = context_course::instance($course->id);
        if ($viewall || has_capability('block/training_schedule:view', $context)) {
            foreach ($groups as $group) {
                $this->content->text .= '<div class="bg-white rounded-lg p-3 shadow-sm mb-3">';
    
                $venuesql = <<<SQL
                SELECT ls.name, ls.address, lgd.startdate, lgd.enddate
                FROM {local_group_details} lgd
                JOIN {local_schools} ls ON ls.id = lgd.venue
                WHERE lgd.groupid = :groupid
                SQL;
    
                $venueparams = [
                    'groupid' => $group->id,
                ];
    
                $venue = $DB->get_record_sql($venuesql, $venueparams);
    
                $cyclesql = <<<SQL
                SELECT g.name
                FROM {groupings} g
                JOIN {groupings_groups} gg ON g.id = gg.groupingid
                WHERE gg.groupid = :groupid
                SQL;
    
                $cycleparams = [
                    'groupid' => $group->id,
                ];
    
                $cyclename = $DB->get_record_sql($cyclesql, $cycleparams);
                if ($cyclename) {
                    $this->content->text .= '<p> <b> Cycle ID :- </b> ' . $cyclename->name . '</p>';
                }
    
                if ($group->name) {
                    $this->content->text .= '<p> <b> Batch ID : </b> ' . $group->name . '</p>';
                }
    
                if ($venue->name) {
                    $this->content->text .= '<p> <b> Venue name : </b>' . $venue->name . '</p> ';
                }
    
                if ($venue->address) {
                    $this->content->text .= '<p> <b> Venue address : </b> ' . $venue->address . '</p>';
                }
    
                if ($venue->startdate) {
                    $this->content->text .= '<p> <b> Training Start Date : </b> ' . customdateformat('DATE_WITH_TIME', $venue->startdate) . '</p>';
                }
    
                if ($venue->enddate) {
                    $this->content->text .= '<p class="mb-0"> <b> Training End Date : </b> ' . customdateformat('DATE_WITH_TIME', $venue->enddate) . '</p>';
                }
    
                $this->content->text .= '<p> <b> Training Coordinators : </b> ' . renderer_role_block($course->id, $group->id, $coordinatorroleid) . '</p>';
    
                $this->content->text .= '<p> <b> Training Facilitators : </b> ' . renderer_role_block($course->id, $group->id, $facilitatorroleid) . '</p>';
    
                $this->content->text .= '</div>';
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
