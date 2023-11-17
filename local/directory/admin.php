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
 * Bulk user registration script from a comma separated file
 *
 * @package    local
 * @subpackage self registration
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('local_admin_form.php');
require_once('fieldadd_form.php');
require_once('lib.php');

require_login();

global $CFG, $DB, $OUTPUT, $PAGE;
require_once($CFG->dirroot.'/user/lib.php');

$PAGE->requires->jquery();
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_directory'));
$PAGE->set_heading('user fields');
echo $OUTPUT->header();

$mformfieldadd = new local_fieldadd();
$mformdirectory = new local_directory();

// Form processing and displaying is done here.
if ($mformfieldadd->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect('#');
} else if ($fromform1 = $mformfieldadd->get_data()) {
    $fielddetail = new stdClass();

    if ($fromform1->fieldname) {
        $fielddetail->userfields = $fromform1->fieldname;

        if ($fromform1->dependent) {
            $fielddetail->dependent = $fromform1->dependent;
            $fielddetail->dependant_on = $fromform1->dependent_on;
        }
    }

    $fieldid = $DB->insert_record('local_user_public_fields', $fielddetail);

    if ($fieldid) {
        $url = new moodle_url('/local/directory/admin.php');
        redirect($url, "Insert Successfully");
    }

} else {
    echo '<div class="directory-page-form bg-white rounded-lg p-5 box-shadow">';   $mformfieldadd->display();
}


// Form processing and displaying is done here.
if ($mformdirectory->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect('#');
} else if ($fromform = $mformdirectory->get_data()) {

    $fields = $DB->get_records('local_user_public_fields');

    foreach ($fields as $fieldvalue) {
        $fieldname = $fieldvalue->userfields;
        if ($fromform->$fieldname) {
            $DB->update_record('local_user_public_fields', array('id' => $fieldvalue->id, 'visible' => 1));
        } else {
            $DB->update_record('local_user_public_fields', array('id' => $fieldvalue->id, 'visible' => 0));
        }
    }

    $url = new moodle_url('/local/directory/admin.php');
    redirect($url, 'Updated');
} else {

    // Displays the.
    $fromform = new stdClass();
    $visible = $DB->get_records('local_user_public_fields', array('visible' => 1));
    foreach ($visible as $visiblevalue) {
        $fieldname = $visiblevalue->userfields;
        $fromform->$fieldname = 1;
    }
    $mformdirectory->set_data($fromform);
    $mformdirectory->display();
    echo '</div>';
}
echo $OUTPUT->footer();
