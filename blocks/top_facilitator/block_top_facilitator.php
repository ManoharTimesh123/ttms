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

require_once($CFG->dirroot. '/theme/edumy/ccn/user_handler/ccn_user_handler.php');
require_once($CFG->dirroot. '/theme/edumy/ccn/block_handler/ccn_block_handler.php');

class block_top_facilitator extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_top_facilitator');
    }

    /**
     * Block Content
     *
     * @return object
     */
    public function hide_header() {
        return true;
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();

        if (!empty($this->config->title)) {
              $this->content->title = $this->config->title;
        } else {
                $this->content->title = '';
        }
        if (!empty($this->config->description)) {
            $this->content->description = $this->config->description;
        } else {
            $this->content->description = '';
        }

        // FIXME: This query is currently getting all the facilitators limit to 6.
        // Later we have to change this logic to get the top facilitators.
        $gettopfacilitatorsql = <<<SQL_QUERY
            select u.* from lms_user u
            INNER JOIN lms_role_assignments ra ON u.id = ra.userid
            INNER JOIN lms_role r ON r.id = ra.roleid
            WHERE r.shortname = 'facilitator'
            LIMIT 6
            SQL_QUERY;

        $users = $DB->get_records_sql($gettopfacilitatorsql);

        $this->content->text = '
          <section class="view-teacher">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-lg-6 mb-4">
                        <div class="white p-4 box-shadow h-100">
                            <h2 class="mb-3">' . $this->content->title . '</h2>
                            <p>' . $this->content->description . '.</p>
                            <!-- <a href="" class="btn-primary d-table ml-auto rounded-pill">View All</a> -->
                        </div>
                    </div>';
        if (!empty($users)) {
            foreach ($users as $key => $ccnuserid) {
                if ($ccnuserid->id) {
                    $ccnuserhandler = new ccnuserHandler();
                    $ccnuser = $ccnuserhandler->ccnGetUserDetails($ccnuserid->id);

                    $this->content->text .= '
                    <div class="col-md-3 col-lg-3 mb-4 flip-wrapper">
                        <div class="flip-container-box position-relative w-100 h-100">
                            <img src="' . $ccnuser->rawAvatar . '" alt="" class="image d-block w-100 h-auto">
                            <div class="overlay w-100 h-100 p-3 position-absolute">
                                <div class="d-flex">
                                    <img src="' . $ccnuser->rawAvatar . '" alt="" class="user-img mr-2 shadow rounded-pill">
                                    <div>
                                        <h4 class="mb-0 text-white">' . $ccnuser->fullname . '</h4>
                                        <span class="mb-0 text-white">' . $ccnuser->department . ' </span>
                                        <span class="d-block text-white"> ' . $ccnuser->lang . '</span>
                                    </div>
                                </div>
                                <div class="text text-white overflow-auto mt-1">
                                    <p>' . $ccnuser->description . '</p>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            }
        }
        $this->content->text .= '
               </div>
            </div>
        </section>';
        $this->page->requires->js('/blocks/top_facilitator/js/top_facilitator.js');
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
        $ccnblockhandler = new ccnBlockHandler();
        return $ccnblockhandler->ccnGetBlockApplicability(array('all'));
    }

    public function html_attributes() {
        global $CFG;
        $attributes = parent::html_attributes();
        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/attributes.php');
        return $attributes;
    }

}
