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
 * Custom Notifications
 *
 * @package    local_customnotifications
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2023 TTMS Limited
 */

global $CFG, $DB, $USER;
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require($CFG->dirroot.'/local/customnotifications/template_form.php');
require_once($CFG->dirroot.'/local/customnotifications/lib.php');

$systemcontext = context_system::instance();
require_login();

$id = optional_param('id', 0, PARAM_INT);

// require_capability('local/customnotifications:manage_customnotifications', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/customnotifications/template.php', array('id' => $id));
$PAGE->set_title(get_string('template', 'local_customnotifications'));
$PAGE->set_heading(get_string('template', 'local_customnotifications'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->navbar->add(get_string('administrationsite'), new moodle_url($CFG->wwwroot.'/admin/search.php'),
    navigation_node::TYPE_SETTING);
$PAGE->navbar->add(get_string('template', 'local_customnotifications'));

$url = new moodle_url($CFG->wwwroot.'/local/customnotifications/index.php', array('id' => 0));

$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);

$mform = new notify_template_form(null, array('id' => $id, 'options' => $editoroptions));

if ($id) {
    $template = $DB->get_record('local_notification_templates', array('id' => $id));
    $template->messagecontent = array('text' => $template->messagecontent, 'format' => $template->messagecontentformat);
    $template->plaintext = array('text' => $template->plaintext, 'format' => $template->messagecontentformat);
    $mform->set_data($template);
}

if ($mform->is_cancelled()) {
    redirect($url);
} else if ($data = $mform->get_data()) {
    $templateid = add_update_template($data);
    redirect($url);
}
echo $OUTPUT->header();

/*if ($delete) {
    // Delete the template with confirmation
    $deletecoursetype = get_string('deletetemplate', 'local_customnotifications');
    $formcontinue = new single_button(new moodle_url($CFG->wwwroot.'/local/customnotifications/index.php',
                                        array('agree' => 1, 'id' => $id)), get_string('yes'));
    $formcancel = new single_button(new moodle_url($CFG->wwwroot.'/local/customnotifications/index.php',
                                        array('agree' => 0, 'id' => $id)), get_string('no'));
    echo $OUTPUT->confirm($deletecoursetype, $formcontinue, $formcancel);
} else {*/
    $mform->display();
// }

echo $OUTPUT->footer();

?>

<style>
div.editor_atto_toolbar {
    display: none;
}
.editor_atto_content_wrap {
    border-top: 1px solid #8f959e;
    width: 72%;
}
</style>

