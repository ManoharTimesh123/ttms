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
require_once($CFG->dirroot . '/blocks/course_catalog/renderer.php');
class block_course_catalog extends block_base {

    public function init() {
        $this->title   = get_string('pluginname', 'block_course_catalog');
    }

    public function hide_header()
    {
        return true;
    }

    public function get_content() {

        if ($this->content !== NULL) {
            return $this->content;
        }
        $context = context_system::instance();
        if (!has_capability('block/course_catalog:viewcontent', $this->context)) {
            $this->content->text = '';
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        $this->page->requires->jquery();
        $this->page->requires->js(new moodle_url('/blocks/course_catalog/js/course_catalog_custom.js'));

        $this->content->text .= render_course_catalog_grid();

        return $this->content;
    }
}
