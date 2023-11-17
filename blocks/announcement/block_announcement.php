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

 /**
  * Displays Announcement information.
  *
  * @copyright  2010 Remote-Learner.net
  * @author     Olav Jordan <olav.jordan@remote-learner.ca>
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

require_once($CFG->dirroot . '/local/announcement/renderer.php');

global $CFG;

class block_announcement extends block_base {

    /**
     * Block Content
     *
     * @return object
     */
    public function hide_header() {
        return true;
    }

    // Declare first.
    public function init() {
        $this->title = get_string('announcement', 'block_announcement');
    }

    // Declare second.
    public function specialization() {
        global $CFG, $DB;
    }

    public function get_content() {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        if ($this->content !== null) {
            return $this->content;
        }
        $systemcontext = context_system::instance();

        if (!has_capability('local/announcement:view', $systemcontext)) {
            $this->content->text = '';
            return $this->content;
        }

        $filters = [
            'current_announcement' => true,
        ];

        $announcementdata = \announcement\local_announcement::getannouncements($filters);

        $count = 0;
        $open = true;
        $announcementurl = new moodle_url($CFG->wwwroot . '/local/announcement');
        $this->content = new stdClass;
        $this->content->text = '';

        if (!empty($announcementdata['data'])) {
            $announcementdata = $announcementdata['data'];
            $this->content->text .= '
        <div class="dashboard-announcement features-course Popular-Online-Course p-0">
            <div class="d-flex justify-content-between header pb-3">
                <h3>' . get_string('announcements', 'block_announcement') . '</h3>
                <a href="' . $announcementurl . '" class="font-weight-bold">' . get_string('seeall', 'block_announcement') . '</a>
            </div>
            <div class="announcement_slider">';
            foreach ($announcementdata as $id => $announcement) {
                $count++;
                $imageurl = \announcement\local_announcement::getimageurl($announcement, $systemcontext->id);

                if ($open == true) {
                    $this->content->text .= '<div class="item hello">';
                    $open = false;
                }
                $announcementurl = new moodle_url($CFG->wwwroot . '/local/announcement', array('id' => $id));

                $this->content->text .= '
                    <a href="' . $announcementurl . '">
                        <div class="content-item media border-bottom mb-4">
                            <img class="img-fluid rounded" src="' . $imageurl . '">
                            <div class="media-body">
                                <h4 class="px-3 font-weight-bold">' . $announcement->title . '</h4>
                                <p class="m-0">' . strip_html_tag_and_limit_character($announcement->description, 100) . '</p>
                            </div>
                        </div>
                    </a>';

                if ($count % 3 == 0) {
                    $this->content->text .= '</div>';
                    $open = true;
                }

            }

            if ($count % 3 != 0) {
                $this->content->text .= '</div>';
            }

            $this->content->text .= '
            </div>
        </div>';
        }
    }

    /**
     * Allow multiple instances in a single course?
     *
     * @return bool True if multiple instances are allowed, false otherwise.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array('all' => true);
    }
}

