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
class block_choose_trainings_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG;

         // Title.
        $mform->addElement('text', 'config_title_inperson', get_string('config_title_inperson', 'block_choose_trainings'));
        $mform->setDefault('config_title_inperson', get_string('inpersontraining', 'block_choose_trainings'));
        $mform->setType('config_title_inperson', PARAM_RAW);

        // Body.
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $mform->addElement('editor', 'config_body_inperson', get_string('config_body_inperson', 'block_choose_trainings'), null, $editoroptions);
        $mform->addRule('config_body_inperson', null, 'required', null, 'client');
        $mform->setType('config_body_inperson', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

        // Title.
        $mform->addElement('text', 'config_title_online', get_string('config_title_online', 'block_choose_trainings'));
        $mform->setDefault('config_title_online',  get_string('onlinetraining', 'block_choose_trainings'));
        $mform->setType('config_title_online', PARAM_RAW);

        // Body.
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $mform->addElement('editor', 'config_body_online', get_string('config_body_online', 'block_choose_trainings'), null, $editoroptions);
        $mform->addRule('config_body_online', null, 'required', null, 'client');
        $mform->setType('config_body_online', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/edit.php');

    }
}

