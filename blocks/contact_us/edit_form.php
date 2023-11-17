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

class block_contact_us_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB;
        $ccnfontlist = include($CFG->dirroot . '/theme/edumy/ccn/font_handler/ccn_font_select.php');
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $ccnstorage = $this->block->config;
        } else {
            $ccnstorage = new stdClass();
            $ccnstorage->items = 3;
        }

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $options = array(
            '0' => get_string('contactformandmap', 'block_contact_us'),
            '1' => get_string('contactformandimage', 'block_contact_us'),
            '2' => get_string('contactformonly', 'block_contact_us'),
        );
        $select = $mform->addElement('select', 'config_style', get_string('config_style', 'theme_edumy'),
        $options, array('class' => 'ccnCommLcRef_change'));
        $select->setSelected('0');

        $ccnitemsrange = array(
          0 => '0',
          1 => '1',
          2 => '2',
          3 => '3',
          4 => '4',
          5 => '5',
          6 => '6',
          7 => '7',
          8 => '8',
          9 => '9',
          10 => '10',
          11 => '11',
          12 => '12',
        );

        $ccnitemsmax = 12;

        $mform->addElement('select', 'config_items', get_string('config_items', 'theme_edumy'), $ccnitemsrange,
        array('class' => 'ccnCommLcRef_change'));
        $mform->setDefault('config_items', 3);

        for ($i = 1; $i <= $ccnitemsmax; $i++) {
            $mform->addElement('header', 'config_ccn_item'.$i , get_string('config_item', 'theme_edumy') . $i);

            $mform->addElement('text', 'config_title_'.$i, get_string('config_title', 'theme_edumy', $i));
            $mform->setDefault('config_title_'.$i, 'Our Email');
            $mform->setType('config_title_'.$i, PARAM_TEXT);

            $mform->addElement('textarea', 'config_subtitle_'.$i, get_string('config_body', 'theme_edumy', $i));
            $mform->setDefault('config_subtitle_'.$i , 'info@edumy.com');
            $mform->setType('config_subtitle_'.$i, PARAM_RAW);

            $select = $mform->addElement('select', 'config_icon_'.$i, get_string('config_icon_class', 'theme_edumy'),
            $ccnfontlist, array('class' => 'ccn_icon_class'));
            $select->setSelected('flaticon-email');

        }

        // Contact Form.
        $mform->addElement('header', 'config_header_4', 'Contact form');

        $mform->addElement('text', 'config_title', get_string('config_title', 'block_contact_us'));
        $mform->setDefault('config_title', 'Send a Message');
        $mform->setType('config_title', PARAM_RAW);

        $mform->addElement('text', 'config_subtitle', get_string('config_subtitle', 'block_contact_us'));
        $mform->setDefault('config_subtitle', 'Ex quem dicta delicata usu, zril vocibus maiestatis in qui.');
        $mform->setType('config_subtitle', PARAM_RAW);

        $options = array(
            '0' => get_string('displayrecaptchatoguest', 'block_contact_us'),
            '1' => get_string('displayrecaptchatoallusers', 'block_contact_us'),
            '2' => get_string('donotdisplayrecaptcha', 'block_contact_us'),
        );
        $select = $mform->addElement('select', 'config_recaptcha', get_string('config_recaptcha', 'theme_edumy'), $options);
        $select->setSelected('0');
        // Google Map.
        $mform->addElement('header', 'config_header_5', 'Google map');

        $mform->addElement('text', 'config_map_lat', get_string('config_map_lat', 'block_contact_us'));
        $mform->setDefault('config_map_lat', '40.6946703');
        $mform->setType('config_map_lat', PARAM_RAW);

        $mform->addElement('text', 'config_map_lng', get_string('config_map_lng', 'block_contact_us'));
        $mform->setDefault('config_map_lng', '-73.9280182');
        $mform->setType('config_map_lng', PARAM_RAW);

        $mform->addElement('text', 'config_map_address', get_string('address_line_1', 'theme_edumy'));
        $mform->setDefault('config_map_address', 'Trafalgar Square, London');
        $mform->setType('config_map_address', PARAM_RAW);

        $range = range(1, 20);
        $mform->addElement('select', 'config_zoom', get_string('config_zoom', 'theme_edumy'), $range);
        $mform->setDefault('config_zoom', '11');

        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'config_map_style', '', get_string('edumydefault', 'block_contact_us'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'config_map_style', '', get_string('roadmap', 'block_contact_us'), 1, $attributes);
        $radioarray[] = $mform->createElement('radio', 'config_map_style', '', get_string('satellite', 'block_contact_us'), 2, $attributes);
        $radioarray[] = $mform->createElement('radio', 'config_map_style', '', get_string('hybrid', 'block_contact_us'), 3, $attributes);
        $radioarray[] = $mform->createElement('radio', 'config_map_style', '', get_string('terrain', 'block_contact_us'), 4, $attributes);
        $mform->addGroup($radioarray, 'config_map_style', 'Style', array(' '), false);

        for ($i = 1; $i <= 2; $i++) {
            if ($i == 1) {
                $title = get_string('contentimage', 'block_contact_us');
            } else if ($i == 2) {
                $title = get_string('mapmarkerimage', 'block_contact_us');
            }
                $mform->addElement('header', 'config_header' . $i , $title);

                $filemanageroptions = array('maxbytes' => $CFG->maxbytes,
                                            'subdirs' => 0,
                                            'maxfiles' => 1,
                                            'accepted_types' => array('.jpg', '.png', '.gif'));

                $f = $mform->addElement('filemanager', 'config_image' . $i, get_string('config_image', 'block_contact_us', $i)
                , null, $filemanageroptions);
        }

        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/edit.php');

    }

    public function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            for ($i = 1; $i <= 2; $i++) {
                $field = 'image' . $i;
                $configfield = 'config_image' . $i;
                $draftitemid = file_get_submitted_draft_itemid($configfield);
                file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_contact_us', 'images', $i,
                array('subdirs' => false));
                $configfield['itemid'] = '';
                $defaults->$configfield['itemid'] = $draftitemid;
                $this->block->config->$field = $draftitemid;
            }
        }

        parent::set_data($defaults);
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->bio;
            $draftideditor = file_get_submitted_draft_itemid('config_bio');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_bio['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id, 'block_contact_us',
            'content', 0, array('subdirs' => true), $currenttext);
            $defaults->config_bio['itemid'] = $draftideditor;
            $defaults->config_bio['format'] = $this->block->config->format;
        } else {
            $text = '';
        }
    }
}
