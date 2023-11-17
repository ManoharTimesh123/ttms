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
 * The modality Management
 *
 * @package local_modality
 * @author Tarun Upadhyay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class financial_details_form extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $customdata = ($this->_customdata['data']) ? $this->_customdata['data'] : null;

        $financialcategories = array();
        $cats = $DB->get_records_menu('local_financial_categories');
        foreach ($cats as $key => $cat) {
            $financialcategories[$key] = $cat;
        }
        $options = array('multiple' => false, 'noselectionstring' => get_string('select'));
        $mform->addElement('select', 'category', get_string('financialcategoryname', 'local_modality'), $financialcategories, $options);
        if ($customdata) {
            $selectedcategory = explode(',', $customdata->category);
            $mform->getElement('category')->setSelected($selectedcategory);
        }

        $financialgrades = array();
        $fingrades = $DB->get_records_menu('local_financial_grades');
        foreach ($fingrades as $key => $fingrade) {
            $financialgrades[$key] = $fingrade;
        }
        $options = array('multiple' => false, 'noselectionstring' => get_string('select'));
        $mform->addElement('select', 'grade', get_string('financialgradename', 'local_modality'), $financialgrades, $options);
        if ($customdata) {
            $selectedsubcategory = explode(',', $customdata->grade);
            $mform->getElement('grade')->setSelected($selectedsubcategory);
        }
        $mform->hideIf('grade', 'category', 'value', 3);

        $financiallunchtypes = array();
        $finlunches = $DB->get_records_menu('local_financial_lunch_types');
        foreach ($finlunches as $key => $finlunch) {
            $financiallunchtypes[$key] = $finlunch;
        }
        $options = array('multiple' => false, 'noselectionstring' => get_string('select'));
        $mform->addElement('select', 'lunchtype', get_string('financiallunchtype', 'local_modality'), $financiallunchtypes, $options);
        if ($customdata) {
            $selectedsubcategory = explode(',', $customdata->lunchtype);
            $mform->getElement('lunchtype')->setSelected($selectedsubcategory);
        }
        $mform->hideIf('lunchtype', 'category', 'value', 10);

        $financialdependents = array();
        $dependents = $DB->get_records('local_financial_dependents');
        foreach ($dependents as $dependent) {
            $financialdependents[$dependent->code] = $dependent->name;
        }
        $options = array('multiple' => false, 'noselectionstring' => get_string('select'));
        $mform->addElement('select', 'dependenton', get_string('financialdependenton', 'local_modality'), $financialdependents, $options);
        if ($customdata) {
            $selecteddependenton = explode(',', $customdata->dependenton);
            $mform->getElement('dependenton')->setSelected($selecteddependenton);
        }

        $mform->addElement('text', 'fromvalue', get_string('financialfromvalue', 'local_modality'), 'maxlength="50" size="25"');
        $mform->setType('fromvalue', PARAM_TEXT);
        $mform->addRule('fromvalue', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'tovalue', get_string('financialtovalue', 'local_modality'), 'maxlength="50" size="25"');
        $mform->setType('tovalue', PARAM_TEXT);
        $mform->addRule('tovalue', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'value', get_string('financialvalue', 'local_modality'), 'maxlength="50" size="25"');
        $mform->setType('value', PARAM_TEXT);
        $mform->addRule('value', get_string('required'), 'required', null, 'client');

        // Hidden optional params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_modality'));

    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        return $errors;
    }

    public function set_data($data) {
        global $DB;

        parent::set_data($data);
    }
}
