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
 * Top Facilitator
 *
 * @package    block_top_facilitator
 * @author     Lalit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */
class block_top_facilitator_edit_form extends block_edit_form {

    /**
     * Extends the configuration form for block_top_facilitator.
     */
    protected function specific_definition($mform) {
        global $CFG;

        // Section header title.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block_top_facilitator'));

        // Please keep in mind that all elements defined here must start with 'config_'.
        $mform->addElement('text', 'config_title', get_string('facilitator_title', 'block_top_facilitator'));
        $mform->setDefault('config_title', get_string('topratinginstructors', 'block_top_facilitator'))
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('text', 'config_description', get_string('facilitator_description', 'block_top_facilitator'));
        $mform->setDefault('config_description', get_string('blockdescriptionhere', 'block_top_facilitator'));
        $mform->setType('config_description', PARAM_TEXT);

        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/edit.php');
    }
}
