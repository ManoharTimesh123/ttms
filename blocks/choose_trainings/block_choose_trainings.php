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
 * Choose Trainings
 * @package    block_choose_trainings
 */
defined('MOODLE_INTERNAL') || die();
 
global $CFG;
require_once($CFG->dirroot. '/theme/edumy/ccn/block_handler/ccn_block_handler.php');
require_once($CFG->dirroot . '/blocks/choose_trainings/renderer.php');

class block_choose_trainings extends block_base {
    // Declare first.
    public function init() {
        $this->title = get_string('choose_trainings', 'block_choose_trainings');

    }

    // Declare second.
    public function specialization() {
        global $CFG, $DB;
        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/specialization.php');

    }

    public function get_content() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/filelib.php');
        if ($this->content !== null) {
             return $this->content;
        }

        $this->content = new stdClass();

        $traininginfo = new stdClass(); 

        if (!empty($this->config->title_inperson)) {
            $traininginfo->titleinperson = $this->config->title_inperson;
        } else {
            $traininginfo->titleinperson = '';
        }  

        if (!empty($this->config->body_inperson)) {
            $traininginfo->inperson = $this->config->body_inperson['text'];
        } else {
            $traininginfo->inperson = '';
        }

        if (!empty($this->config->title_online)) {
            $traininginfo->titleonline = $this->config->title_online;
        } else {
            $traininginfo->titleonline = '';
        }

        if (!empty($this->config->body_online)) {
            $traininginfo->online = $this->config->body_online['text'];
        } else {
            $traininginfo->online = '';
        }


        $this->content->text = render_block_choose_trainings($traininginfo);

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
