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
class block_key_activity extends block_base {
    /**
     * block initializations
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_key_activity');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;
        
        $systemcontext = context_system::instance();
        if ($this->content !== null) {
            return $this->content;
        }

        if (!is_siteadmin() && !has_capability('block/key_activity:view', $systemcontext)) {
            return $this->content;
        }

        if (!isloggedin() || isguestuser()) {
            return '';  // Never useful unless you are logged in as real users.
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        $course = $this->page->course;
        $this->content->text .= '<div class="row">';

        if (is_siteadmin() || has_capability('block/key_activity:view', $systemcontext)) {
            $this->content->text .= '
            <div class="col-md-6 col mb-3">
                <a class="text-center p-2 key_activities_item rounded h-100 d-flex flex-column justify-content-center"
                    href="'.$CFG->wwwroot.'/admin/user.php">
                    <i class="flaticon-online display-4"> </i>
                    <h5 class="mb-0">' . get_string('manageusers', 'block_key_activity') . '</h5>
                    <div class="pull-right badge" id="WrForms"></div>
                </a>
            </div>';

            $performbatchingurl = new moodle_url($CFG->wwwroot . '/local/batching/index.php');

            $this->content->text .= '
            <div class="col-md-6 col mb-3">
                <a class="text-center p-2 key_activities_item rounded h-100 d-flex flex-column justify-content-center"
                href="' . $performbatchingurl . '">
                    <i class="flaticon-resume display-4"> </i>
                    <h5 class="mb-0">' . get_string('managebatches', 'block_key_activity') . '</h5>
                    <div class="pull-right badge" id="WrThemesIcons"></div>
                </a>
            </div>';

            $this->content->text .= '
                    <div class="col-md-6 col mb-3">
                    <a class="text-center p-2 key_activities_item rounded h-100 d-flex flex-column justify-content-center"
                    href="'.$CFG->wwwroot.'/local/modality/show_schools.php">
                        <i class="flaticon-student-2 display-4"> </i>
                        <h5 class="mb-0">' . get_string('manageschools', 'block_key_activity') . '</h5>
                        <div class="pull-right badge" id="WrThemesIcons"></div>
                    </a>
                    </div>';

            $this->content->text .= '
                <div class="col-md-6 col mb-3">
                <a class="text-center p-2 key_activities_item rounded h-100 d-flex flex-column justify-content-center"
                href="'.$CFG->wwwroot.'/admin/category.php?category=local_modality">
                    <i class="flaticon-appointment display-4"> </i>
                    <h5 class="mb-0">' . get_string('managedistzondiets', 'block_key_activity') . '</h5>
                    <div class="pull-right badge" id="WrThemesIcons"></div>
                </a>
                </div>';

            $this->content->text .= '
                <div class="col-md-6 col mb-3">
                    <a class="text-center p-2 key_activities_item rounded h-100 d-flex flex-column justify-content-center"
                    href="'.$CFG->wwwroot.'/course/management.php">
                        <i class="flaticon-graduation-cap display-4"> </i>
                        <h5 class="mb-0">' . get_string('courses', 'block_key_activity') . '</h5>
                        <div class="pull-right badge" id="WrThemesIcons"></div>
                </a>
                </div>';
        }
               $this->content->text .= '</div>';
        // Get current active role. if role is not equal to instructor then dont proceed. return empty string.
        return $this->content;
    }

    /**
     * allow the block to have a configuration page
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
