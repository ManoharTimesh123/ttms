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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

global $CFG, $DB, $PAGE;

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir.'/pdflib.php');
require($CFG->dirroot . '/local/batching/proposals_form.php');
require_once($CFG->dirroot.'/local/batching/renderer.php');
require_once($CFG->dirroot . '/local/batching/lib.php');
require($CFG->dirroot . '/local/batching/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Batching id.

require_login();

$systemcontext = context_system::instance();
if (!has_any_capability(['local/batching:perform', 'local/batching:propose'], $systemcontext)) {
    throw new moodle_exception('nopermission', 'error', '', null, get_string('nopermission', 'local_batching'));
}

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/batching/proposals.php', array('id' => $id));
$PAGE->set_title(get_string('batchingproposals', 'local_batching'));
$PAGE->set_heading(get_string('batchingproposals', 'local_batching'));
$PAGE->set_pagelayout('admin');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/batching/js/batching_custom.js', true);

if ($id > 0) {
    $batchting = get_batchings($id)[$id];
    if ($batchting->status == 'corrigendum' || $batchting->status == 'addendum') {
        upload_corrigendum_or_addendum_file($id, $batchting->status);
    } else {
        upload_proposal_file($id);
    }
}

$PAGE->navbar->add(get_string('pluginname', 'local_batching'), new moodle_url('/local/batching/index.php'),
    navigation_node::TYPE_SETTING);

$returnurl = new moodle_url($CFG->wwwroot.'/local/batching/financials.php', array('id' => $id));
$url = new moodle_url($CFG->wwwroot.'/local/batching/proposals.php', array('id' => $id));
$nexturl = new moodle_url($CFG->wwwroot.'/local/batching/financials.php', ['id' => $id]);

$mform = new batching_proposal_form(null, array('id' => $id));
$data->summary = array('text' => $data->summary, 'format' => $data->summary);
$data->filenumber = ($batchting->file_number) ? $batchting->file_number : '';
$data->comment = ($batchting->comment) ? $batchting->comment : '';

$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($url);
} else if ($data = $mform->get_data()) {

    $data->status = '';

    $savechanges = get_string('savechanges', 'local_batching');
    $approveproposal = get_string('approveproposal', 'local_batching');
    $rejectproposal = get_string('rejectproposal', 'local_batching');
    $corrigendumproposal = get_string('corrigendumproposal', 'local_batching');
    $addendumproposal = get_string('addendumproposal', 'local_batching');

     if ($data->submitbutton == $savechanges) {
        $data->status = get_string('batched', 'local_batching');
    } else if ($data->submitbutton == $approveproposal) {
        $data->status = get_string('approved', 'local_batching');
    } else if ($data->submitbutton == $rejectproposal) {
        $data->status = get_string('rejected', 'local_batching');
    } else if ($data->submitbutton == $corrigendumproposal) {
        $data->status = get_string('corrigendum', 'local_batching');
        $url = new moodle_url($CFG->wwwroot.'/local/batching/financials.php', array('id' => $id));
    } else if ($data->submitbutton == $addendumproposal) {
        $data->status = get_string('addendum', 'local_batching');
        $url = new moodle_url($CFG->wwwroot.'/local/batching/financials.php', array('id' => $id));
    }

    if (!empty($data->status)) {
        update_proposal($data);
    }

    redirect($url);
}
echo $OUTPUT->header();

echo batching_stepper(7);

$mform->display();

echo $OUTPUT->footer();

