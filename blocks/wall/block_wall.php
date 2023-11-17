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
require_once($CFG->dirroot . '/blocks/wall/renderer.php');
class block_wall extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_wall');
    }

    public function get_content() {
        global $CFG;
        $systemcontext = context_system::instance();
        if (!has_capability('local/wall:view', $systemcontext)) {
            $this->content->text = '';
            return $this->content;
        }

        if ($this->content !== null) {
            return $this->content;
        }
        $text = get_string('createpost', 'block_wall');
        $url = new moodle_url($CFG->wwwroot . '/local/wall/create.php');
        $wallurl = new moodle_url($CFG->wwwroot . '/local/wall');
        $seeallwall = '<a href="' . $wallurl . '" class="font-weight-bold float-right text-danger mr-4">' . get_string('seeall', 'block_wall') . '</a>';

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->text .= $seeallwall;
        if (has_capability('local/wall:add', $systemcontext)) {
            $this->content->text .= wall\add_button($text, $url);
        }
        $this->content->footer = '';

        $this->page->requires->jquery();
        $this->page->requires->js(new moodle_url('/blocks/wall/js/custom.js'));

        $this->content->text .= wall\render_approve_wall_post_grid(); ?>

        <?php
        return $this->content;
    }
}
