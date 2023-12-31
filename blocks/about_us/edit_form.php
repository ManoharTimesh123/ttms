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

class block_about_us_edit_form extends block_edit_form {
    
    protected function specific_definition($mform) {
        global $CFG;

        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // Title.
        $mform->addElement('text', 'config_title', get_string('config_title', 'theme_edumy'));
        $mform->setDefault('config_title', 'Our Values');
        $mform->setType('config_title', PARAM_RAW);

        // Body.
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $mform->addElement('editor', 'config_body', get_string('config_body', 'theme_edumy'), null, $editoroptions);
        $mform->addRule('config_body', null, 'required', null, 'client');
        $mform->setType('config_body', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

        // Image.
        $mform->addElement('filemanager', 'config_image', get_string('config_image', 'theme_edumy'), null,
                array('subdirs' => 0, 'maxbytes' => $maxbytes, 'areamaxbytes' => 10485760, 'maxfiles' => 1,
                'accepted_types' => array('.png', '.jpg', '.jpeg', '.gif') ));

        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'config_style', '', get_string('leftimage' , 'block_about_us'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'config_style', '', get_string('rightimage' , 'block_about_us'), 1, $attributes);
        $mform->addGroup($radioarray, 'config_style', 'Style', array(' '), false);

        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/edit.php');
    }

    public function set_data($defaults) {

        // Begin CCN Image Processing.
        if (empty($entry->id)) {
            $entry = new stdClass;
            $entry->id = null;
        }
        $draftitemid = file_get_submitted_draft_itemid('config_image');
        file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_about_us', 'content', 0, array('subdirs' => true));
        $entry->attachments = $draftitemid;
        parent::set_data($defaults);
        if ($data = parent::get_data()) {
            file_save_draft_area_files($data->config_image, $this->block->context->id, 'block_about_us', 'content', 0,
                array('subdirs' => true));
        }
        // END CCN Image Processing.

        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->body;
            $draftideditor = file_get_submitted_draft_itemid('config_body');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_body['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id, 'block_about_us',
            'content', 0, array('subdirs' => true), $currenttext);
            $defaults->config_body['itemid'] = $draftideditor;
            $defaults->config_body['format'] = $this->block->config->format;
        } else {
            $text = '';
        }
    }
}
