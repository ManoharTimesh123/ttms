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


class block_courses_popular_slider_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG;

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Title.
        $mform->addElement('text', 'config_title', get_string('config_title', 'theme_edumy'));
        $mform->setDefault('config_title', 'Featured Courses');
        $mform->setType('config_title', PARAM_RAW);

        // Subtitle.
        $mform->addElement('text', 'config_subtitle', get_string('config_subtitle', 'theme_edumy'));
        $mform->setDefault('config_subtitle', 'Cum doctus civibus efficiantur in imperdiet deterruisset.');
        $mform->setType('config_subtitle', PARAM_RAW);

        /* INTG Customization Start : Course View Limit. */
        $mform->addElement('text', 'config_course_view_limit', get_string('config_course_limit', 'block_courses_popular_slider'));
        $mform->setDefault('config_course_view_limit', 12);
        $mform->setType('config_course_view_limit', PARAM_INT);
        /* INTG Customization End */
        $options = array(
            '0' => 'Hidden',
            '1' => 'Visible',
        );
        $select = $mform->addElement('select', 'config_course_image', get_string('config_image', 'theme_edumy'), $options);
        $select->setSelected('1');

        $options = array(
            '0' => 'Hidden',
            '1' => 'Visible',
            '2' => 'Visible (max 50 characters)',
            '3' => 'Visible (max 100 characters)',
            '4' => 'Visible (max 150 characters)',
            '5' => 'Visible (max 200 characters)',
            '6' => 'Visible (max 350 characters)',
            '7' => 'Visible (max 500 characters)',
        );
        $select = $mform->addElement('select', 'config_description', get_string('config_description', 'theme_edumy'), $options);
        $select->setSelected('0');

        $options = array(
            '0' => 'Hidden',
            '1' => 'Visible',
        );
        $select = $mform->addElement('select', 'config_enrol_btn', get_string('config_enrol_btn', 'theme_edumy'), $options);
        $select->setSelected('0');

        $mform->addElement('text', 'config_enrol_btn_text', get_string('config_enrol_btn_text', 'theme_edumy'));
        $mform->setDefault('config_enrol_btn_text', 'Buy Now');
        $mform->setType('config_enrol_btn_text', PARAM_RAW);

        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('select_from_dropdown_multiple', 'theme_edumy'),
        );
        $mform->addElement('course', 'config_courses', get_string('courses'), $options);

        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/edit.php');

    }
}
