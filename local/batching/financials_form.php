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

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/user_management/locallib.php');

class batching_financial_form extends moodleform {


    public function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $itemid = $this->_customdata['itemid'];
        $batchting = get_batchings($id)[$id];
        if ($batchting->status == 'corrigendum' || $batchting->status == 'addendum') {
            get_or_add_record_in_proposal_log_by_batching($id, $batchting->status);
        }
        $mform->addElement('html', '<div class="row">');

        $financialcategories = get_financial_category();
        $financialcategoriesarray = array();
        $financialcategoriesarray[''] = '--Select--';
        foreach ($financialcategories as $key => $category) {
            $financialcategoriesarray[$key] = $category->name;
        }

        $mform->addElement('html', '<div class="col-md-6"><div class="form-group">');
        $mform->addElement('select', 'category', 'Category', $financialcategoriesarray);
        $mform->setType('category', PARAM_INT);
        $mform->addRule('category', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('category', 'category', 'local_batching');
        $mform->addElement('html', '</div></div>');

        $mform->addElement('html', '<div class="col-md-6"><div class="form-group" style="min-height: 100px;" id="category_details">');
        $mform->addElement('html', '</div></div>');

        // Form elements for lunch only.

        $lunchcategory = $DB->get_record('local_financial_categories', ['code' => 'lunchrefreshment']);
        $lunchcategoryid = $lunchcategory->id;

        $mform->addElement('html', '<div class="col-md-12"><div class="form-group">');

        $financiallunchtypes = ['' => '--select--'];
        $finlunches = $DB->get_records('local_financial_lunch_types', ['code' => 'lunchtype']);
        foreach ($finlunches as $key => $finlunch) {
            $financiallunchtypes[$key] = $finlunch->name;
        }

        $mform->addElement('select', 'lunch[type]', 'Lunch Type', $financiallunchtypes , ['class' => 'lunchitems']);
        $mform->setType('lunch[type]', PARAM_INT);
        $mform->addHelpButton('lunch[type]', 'lunch_type', 'local_batching');
        $mform->hideIf('lunch[type]', 'category', 'value', $lunchcategoryid);

        $financialteas = [];
        $finlunches = $DB->get_records('local_financial_lunch_types', ['code' => 'tea']);
        foreach ($finlunches as $key => $finlunch) {
            $financialteas[$key] = $finlunch;
        }

        if (!empty($financialteas)) {
            foreach ($financialteas as $financialtea) {
                $mform->addElement('advcheckbox', 'lunch[tea][' . $financialtea->id . ']', $financialtea->name , '', ['class' => 'lunchitems', ]);
                $mform->hideIf('lunch[tea][' . $financialtea->id . ']', 'category', 'value', $lunchcategoryid);
            }
        }

        $financialwaters = [];
        $finlunches = $DB->get_records('local_financial_lunch_types', ['code' => 'water']);
        foreach ($finlunches as $key => $finlunch) {
            $financialwaters[$key] = $finlunch;
        }

        if (!empty($financialwaters)) {
            foreach ($financialwaters as $financialwater) {
                $mform->addElement('advcheckbox', 'lunch[water][' . $financialwater->id . ']', $financialwater->name, '', ['class' => 'lunchitems']);
                $mform->hideIf('lunch[water][' . $financialwater->id . ']', 'category', 'value', $lunchcategoryid);
            }
        }

        $mform->addElement('html', '</div></div>');
        // End Lunch form elements.

        $mform->addElement('html', '<div class="col-md-4"><div class="form-group">');
        $mform->addElement('textarea', 'itemtitle', get_string('itemtitle', 'local_batching'));
        $mform->setType('itemtitle', PARAM_TEXT);
        $mform->addRule('itemtitle', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('itemtitle', 'itemtitle', 'local_batching');
        $mform->addElement('html', '</div></div>');

        $mform->addElement('html', '<div class="col-md-4"><div class="form-group">');
        $mform->addElement('text', 'itemunit', get_string('itemunit', 'local_batching'));
        $mform->setType('itemunit', PARAM_INT);
        $mform->addRule('itemunit', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('itemunit', 'itemunit', 'local_batching');
        $mform->addElement('html', '</div></div>');

        $mform->addElement('html', '<div class="col-md-4"><div class="form-group">');
        $mform->addElement('text', 'itemcost', get_string('itemcost', 'local_batching'));
        $mform->setType('itemcost', PARAM_TEXT);
        $mform->addRule('itemcost', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('itemcost', 'itemcost', 'local_batching');
        $mform->addElement('html', '</div></div>');

        $mform->addElement('html', '</div>');

        // Hidden optional params.
        $mform->addElement('hidden', 'idbatching', $id, array('id' => 'id_batching'));
        $mform->setType('idbatching', PARAM_INT);
        $mform->setDefault('idbatching', $id);

        $mform->addElement('hidden', 'itemid', $itemid);
        $mform->setType('itemid', PARAM_INT);
        $mform->setDefault('itemid', $itemid);

        $mform->addElement('hidden', 'batchingstatus', $batchting->status);
        $mform->setType('batchingstatus', PARAM_TEXT);
        $mform->setDefault('batchingstatus', $batchting->status);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges', 'local_batching'));

        $mform->addElement('html', '<table class="financial_table generaltable dataTable no-footer">');

        $batchingfinacials = get_financial_by_batching($id);

        if (!empty($batchingfinacials)) {
             $mform->addElement('html', '<thead><tr role="row"><th class"header c0 sorting_asc">Category</th><th class"header c0 sorting_asc">Title</th><th class"header c0 sorting_asc">Unit</th><th class"header c0 sorting_asc">Cost</th><th class"header c0 sorting_asc">Action</th></tr></thead>');

            $totalcost = 0;
            foreach ($batchingfinacials as $financial) {
                $itemediturl = new moodle_url($CFG->wwwroot . '/local/batching/financials.php', ['id' => $financial->batching, 'itemid' => $financial->id]);

                $mform->addElement('html', '<tr class="lastrow odd">');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . $financial->categoryname . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . $financial->title . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . $financial->unit . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1">' . custom_money_format($financial->cost) . '</td>');
                $mform->addElement('html', '<td class="cell c0 sorting_1"><a  href="' . $itemediturl . '"><i class="fa fa-pencil"></i></a></td>');
                $mform->addElement('html', '</tr>');

                $totalcost = $totalcost + $financial->cost;
            }
            $mform->addElement('html', '<tr class="lastrow odd">');
            $mform->addElement('html', '<td class="cell c0 sorting_1"></td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1"><b>TOTAL</b></td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1"></td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1"><b>' . custom_money_format($totalcost) . '</b></td>');
            $mform->addElement('html', '<td class="cell c0 sorting_1"></td>');
            $mform->addElement('html', '</tr>');

        }

      $mform->addElement('html', '<a href="' . new moodle_url($CFG->wwwroot . '/local/batching/proposals.php', ['id' => $id]) . '" class="btn btn-primary" style="float:right;
            background-color:green !important; border: green !important;">Create Proposal</a><div style="float:right;padding:8px 10px 0 0">Finished with adding financials?</div>');
        $mform->addElement('html', '</table>');
		
    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        if (!is_numeric($data['itemcost'])) {
            $errors['itemcost'] = get_string('itemcosterrormessage', 'local_batching');
        }
        if ($data['itemunit'] <= 0) {
            $errors['itemunit'] = get_string('itemuniterrormessage', 'local_batching');
        }
        return $errors;
    }
}
