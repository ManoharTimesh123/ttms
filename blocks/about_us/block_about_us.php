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
 * About Us
 *
 * @package    block_about_us
 * @author     Sangita Kumari
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot. '/theme/edumy/ccn/block_handler/ccn_block_handler.php');

class block_about_us extends block_base {
    // Declare first.
    public function init() {
        $this->title = get_string('about_us', 'block_about_us');
    }

    // Declare second.
    public function specialization() {
        global $CFG, $DB;
        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/specialization.php');

        if (empty($this->config->title) || empty($this->config->body) ) {
            $this->config = new \stdClass();
            // If about us title is not set in config, get the default tiel from language string.
            if (empty($this->config->title)) {
                $this->config->title = get_string('title' , 'block_about_us');
            }

            // If about us body text is not set in config, get the default body text from language string.
            if (empty($this->config->body)) {
                $this->config->body['text'] = get_string('description' , 'block_about_us');
            }

            $this->config->style = 0;
        }
    }
    public function get_content() {
        global $CFG, $DB;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return '';
        }

        $this->content = new stdClass;

        if (!empty($this->config->title)) {
            $this->content->title = $this->config->title;
        } else {
            $this->content->title = '';
        }

        if (!empty($this->config->body)) {
            $this->content->body = $this->config->body['text'];
        } else {
            $this->content->body = '';
        }

        if (!empty($this->config->style)) {
            $this->content->style = $this->config->style;
        } else {
            $this->content->style = 0;
        }

        if ($this->content->style == 1) {
            $class = '';
        } else {
            $class = 'ccn-row-reverse';
        }
        
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'block_about_us', 'content');
        $this->config->image = $CFG->wwwroot . '/theme/edumy/images/about/8.jpg';
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if ($filename <> '.') {
                $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                $file->get_filearea(), null, $file->get_filepath(), $filename);
                $this->config->image = $url;
            }
        }

        $this->content->text = '
            <div class="container mt80">
              <div class="row '.$class.'">';

                $this->content->text .= '<div class="col-lg-6">';

                $this->content->text .= '
        					<div class="about_content">
        						<h3 data-ccn="title">'.format_text($this->content->title, FORMAT_HTML, array('filter' => true)).'</h3>
        						<div data-ccn="body">'.format_text($this->content->body, FORMAT_HTML, array('filter' => true, 'noclean' => true))
                    .'</div>
        					</div>
        				</div>';
        if ($this->config->image) {
                  $this->content->text .= '
        				      <div class="col-lg-6">
        					       <div class="about_thumb">
        						        <img data-ccn="image" data-ccn-img="src" class="img-fluid" src="' . $this->config->image . '" alt="">
        					       </div>
        				      </div>';
        }
              $this->content->text .= '
        			</div>
            </div>';
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
