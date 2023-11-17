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
  * Displays blog information.
  *
  * @copyright  2010 Remote-Learner.net
  * @author     Olav Jordan <olav.jordan@remote-learner.ca>
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

require_once($CFG->dirroot .'/local/blog/renderer.php');

global $CFG;

class block_blogs extends block_base {

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
        $this->title = get_string('blogs', 'block_blogs');
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
        if (!has_capability('local/blog:view', $systemcontext) ) {
            $this->content->text = '';
            return $this->content;
        }

        $filters = [
            'status' => 1,
            'limit' => 5,
        ];

        $blogdata = \blog\local_blog::getblogpost($filters);
        $blogurl = new moodle_url($CFG->wwwroot . '/local/blog');

        $this->content = new stdClass;
        $this->content->text = '';
        if ($blogdata['data']) {
            $posts = $blogdata['data'];

            $this->content->text .= '
            <div class="dashboard-blog p-0">
                <div class="d-flex justify-content-between header pb-3">
                    <h4>' . get_string('blogs', 'block_blogs') . '</h4>
                    <a href="' . $blogurl . '" class="font-weight-bold">' . get_string('seeall', 'block_blogs') . '</a>
                </div>';
                foreach ($posts as $key => $post) {
                    $imageurl = \blog\local_blog::getimageurl($post, $systemcontext->id);
                    $posturl = new moodle_url($CFG->wwwroot . '/local/blog/index.php', array('id' => $post->id));
                    $createposturl = new moodle_url($CFG->wwwroot . '/local/blog/edit.php');
                    $postdescription = strip_tags($post->description, '<p>');

                    $this->content->text .= '
                    <div class="container content-item box-shadow bg-white mb-3 py-3 pl-3">
                        <a href="' . $posturl . '">
                            <div class="row">
                                <div class="col-sm-4">
                                    <img class="img-fluid img-rounded w-100" src="' . $imageurl . '" alt="">
                                </div>
                                <div class="col-sm-8 pl-md-0">
                                    <h5 class="font-weight-bold">'. $post->title .'</h5>
                                    <p>'. substr($postdescription, 0, 100) .'</p>
                                </div>
                            </div>
                        </a>
                    </div>';
                }
                $this->content->text .= '
                <a target="_blank" href="' . $createposturl . '" class="btn btn-primary d-table ml-auto">' . get_string('addblog', 'block_blogs') . '</a>
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
