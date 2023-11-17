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
 * The batching Management
 *
 * @package    local_batching
 * @author     Tarun Upadhyay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class batching_filters_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];

        $data = $this->_customdata['data'];
        $disabledstartdate = array('disabled');
        $disabledenddate = array('disabled');
        if ($data['dojstartdate_enabled']) {
            $disabledstartdate = '';
        }

        if ($data['dojenddate_enabled']) {
            $disabledenddate = '';
        }

        // Left upper half of filters start.
        $mform->addElement('html', '<div class="row m-5">');
        $mform->addElement('html', '<div class="col-md-6">');

        $options = array('multiple' => true, 'noselectionstring' => get_string('all', 'local_batching'));

        $roles = array();
        $ro = $DB->get_records_menu('local_school_positions');
        foreach ($ro as $key => $r) {
            $roles[$key] = $r;
        }
        $select = $mform->addElement('autocomplete', 'roles', get_string('roles', 'local_batching'), $roles, $options);
        $select->setSelected(explode($data['roles']));
        $mform->addHelpButton('roles', 'roles', 'local_batching');

        $zones = array();
        $zs = $DB->get_records_menu('local_zones');
        foreach ($zs as $key => $z) {
            $zones[$key] = $z;
        }
        $select = $mform->addElement('autocomplete', 'zones', get_string('zones', 'local_batching'), $zones, $options);
        $select->setSelected(explode($data['zones']));
        $mform->addHelpButton('zones', 'zones', 'local_batching');

        $diets = array();
        $ds = $DB->get_records_menu('local_diets');
        foreach ($ds as $key => $d) {
            $diets[$key] = $d;
        }
        $select = $mform->addElement('autocomplete', 'diets', get_string('diets', 'local_batching'), $diets, $options);
        $select->setSelected(explode($data['diets']));
        $mform->addHelpButton('diets', 'diets', 'local_batching');

        $subjects = array();
        $ss = $DB->get_records_menu('local_subjects');
        foreach ($ss as $key => $s) {
            $subjects[$key] = $s;
        }
        $select = $mform->addElement('autocomplete', 'subjects', get_string('subjects', 'local_batching'), $subjects, $options);
        $select->setSelected(explode($data['subjects']));
        $mform->addHelpButton('subjects', 'subjects', 'local_batching');

        $mform->addElement('html', '</div>');
        // Left upper half of filters end.

        // Right upper half of filters start.
        $mform->addElement('html', '<div class="col-md-6">');

        $grades = array();
        $grs = $DB->get_records_menu('local_grades');
        foreach ($grs as $key => $gr) {
            $grades[$key] = $gr;
        }
        $select = $mform->addElement('autocomplete', 'grades', get_string('grades', 'local_batching'), $grades, $options);
        $select->setSelected(explode($data['grades']));
        $mform->addHelpButton('grades', 'grades', 'local_batching');

        $posts = array();
        $pos = $DB->get_records_menu('local_posts');
        foreach ($pos as $key => $po) {
            $posts[$key] = $po;
        }
        $select = $mform->addElement('autocomplete', 'posts', get_string('posts', 'local_batching'), $posts, $options);
        $select->setSelected(explode($data['posts']));
        $mform->addHelpButton('posts', 'posts', 'local_batching');

        $mform->addElement('date_selector', 'dojstartdate', get_string('dojstartdate', 'local_batching'), '', $disabledstartdate);
        $mform->setType('dojstartdate', PARAM_TEXT);
        $mform->addHelpButton('dojstartdate', 'dojstartdate', 'local_batching');
        $mform->addElement('advcheckbox', 'dojstartdate_enabled', get_string('enable'), '', ['class' => 'enable-date']);

        $mform->addElement('date_selector', 'dojenddate', get_string('dojenddate', 'local_batching'), '', $disabledenddate);
        $mform->setType('dojenddate', PARAM_TEXT);
        $mform->addHelpButton('dojenddate', 'dojenddate', 'local_batching');
        $mform->addElement('advcheckbox', 'dojenddate_enabled', get_string('enable'), '', ['class' => 'enable-date']);

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        // Right upper half of filters end.

        // Bottom filters start.
        $mform->addElement('html', '<div class="row m-5">');
        $mform->addElement('html', '<div class="col-md-12">');

        $mform->addElement('text', 'trainingnoofdays', get_string('trainingnoofdays', 'local_batching'), 'maxlength="120" size="25" pattern="[0-9 ]+" ');
        $mform->setType('trainingnoofdays', PARAM_TEXT);
        $mform->addRule('trainingnoofdays', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('trainingnoofdays', 'trainingnoofdays', 'local_batching');

        $mform->addElement('text', 'trainingnoofsessions', get_string('trainingnoofsessions', 'local_batching'), 'maxlength="120" size="25" pattern="[0-9 ]+" ');
        $mform->setType('trainingnoofsessions', PARAM_TEXT);
        $mform->addRule('trainingnoofsessions', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('trainingnoofsessions', 'trainingnoofsessions', 'local_batching');

        $mform->addElement('text', 'sessiontime', get_string('sessiontime', 'local_batching'), 'maxlength="120" size="25" pattern="[0-9 ]+" ');
        $mform->setType('sessiontime', PARAM_TEXT);
        $mform->addRule('sessiontime', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('sessiontime', 'sessiontime', 'local_batching');

        $mform->addElement('text', 'percentage', get_string('percentage', 'local_batching'), 'maxlength="120" size="25" pattern="[0-9 ]+" ');
        $mform->setType('percentage', PARAM_TEXT);
        $mform->addRule('percentage', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('percentage', 'percentage', 'local_batching');

        $mform->addElement('text', 'participantsperbatch', get_string('participantsperbatch', 'local_batching'), 'maxlength="120" size="25" pattern="[0-9 ]+" ');
        $mform->setType('participantsperbatch', PARAM_TEXT);
        $mform->addRule('participantsperbatch', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('participantsperbatch', 'participantsperbatch', 'local_batching');

        // Hidden optional params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_batching'));

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        // Bottom filters end.

    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}

