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
  * Displays News information.
  *
  * @copyright  2010 Remote-Learner.net
  * @author     Olav Jordan <olav.jordan@remote-learner.ca>
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

require_once($CFG->dirroot . '/local/news/renderer.php');

global $CFG;

class block_news extends block_base {

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
        $this->title = get_string('news', 'block_news');
    }

    // Declare second.
    public function specialization() {
        global $CFG, $DB;
    }

    public function get_content() {
        global $CFG, $DB;

        require_once($CFG->libdir . '/filelib.php');
        if ($this->content !== null) {
            return $this->content;
        }

        $systemcontext = context_system::instance();
        if (!has_capability('local/news:view', $systemcontext)) {
            $this->content->text = '';
            return $this->content;
        }

        $filters = [
            'status' => 1,
            'current_news' => true
        ];
        $newsdata = \news\local_news::getnews($filters);

        $newsurl = new moodle_url($CFG->wwwroot . '/local/news');
        $this->content = new stdClass;
        $this->content->text = '';

        if (!empty($newsdata['data'])) {

            $newsdata = $newsdata['data'];

            $this->content->text .= '
            <div class="dashboard-news Popular-Online-Course p-0">
                <div class="d-flex justify-content-between header pb-3">
                     <h4>' . get_string('news', 'block_news') . '</h4>
                     <a href="' . $newsurl . '" class="font-weight-bold">' . get_string('seeall', 'block_news') . '</a>
                </div>
                <div class="latest_news_slider">';

                foreach ($newsdata as $id => $news) {
                    $imageurl = \news\local_news::getimageurl($news, $systemcontext->id);
                    $newsurl = new moodle_url($CFG->wwwroot . '/local/news', array('id' => $id));

                    $this->content->text .= '
                        <div class="content-item box-shadow bg-white h-100 pb-2 mx-auto">
                            <a href="' . $newsurl . '">
                                <img class="img-fluid" src="' . $imageurl . '" alt="">
                                <h5 class="px-3 pt-3 font-weight-bold">' . $news->title . '</h5>
                                <p>' . strip_html_tag_and_limit_character($news->description, 100) . '</p>
                            </a>
                        </div>';
                }

                $this->content->text .= '
                </div>
            </div>';
        }

        return $this->content;
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
