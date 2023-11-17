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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

class local_customnotifications_renderer extends plugin_renderer_base {

    public function list_templates() {
        global $DB, $CFG;

        $templates = $DB->get_records('local_notification_templates');

        $header = array(get_string('templatename', 'local_customnotifications'),
                        get_string('templatecode', 'local_customnotifications'),
                        get_string('subject', 'local_customnotifications'),
                        get_string('messagecontent', 'local_customnotifications'),
                        get_string('plaintext', 'local_customnotifications'),
                        get_string('smstext', 'local_customnotifications'),
                        get_string('action', 'local_customnotifications'));

        $table = new html_table();
        $table->head = $header;

        $output = '';
        if (!$templates) {
            $cell = new html_table_cell();
            $cell->text = '<div class="alert alert-info" style="text-align: left;">' . get_string('nodataavailable', 'local_customnotifications') . '</div>';
            $cell->colspan = 42;
            $row = new html_table_row();
            $row->cells[] = $cell;
            $table->data = array($row);
            $table->align = array('left');
            $table->id = 'templates-list';
            $table->width = '100%';
            $output .= html_writer::table($table);

        } else {
            $table->size = array('5%', '5%', '30%', '30%', '20%', '5%', '5%');
            $table->align = array( 'left', 'left', 'left', 'left', 'left', 'left', 'center');
            $table->width = '99%';
            if ($templates) {
                $data = array();
                foreach ($templates as $template) {
                    $row = array();
                    if ($template->fromuser == -1) {
                        $fromuser = $DB->get_record('user', array('id' => 2));
                        $fromusername = fullname($fromuser, true);
                    } else {
                        $fromusername = $DB->get_field('role', 'name', array('id' => $template->fromuser));
                    }
                    $row[] = $template->name;
                    $row[] = $template->templatecode;
                    $row[] = $template->subject;
                    $row[] = strlen($template->messagecontent) > 500 ? substr($template->messagecontent, 0, 500).'....' : $template->messagecontent;
                    $row[] = strlen($template->plaintext) > 500 ? substr($template->plaintext, 0, 500).'....' : $template->plaintext;
                    $row[] = $template->templateid.'</br>'.$template->smstext;

                    $actionurl = new moodle_url($CFG->wwwroot.'/local/customnotifications/template.php',
                                                    array('id' => $template->id));
                    $actionicons = html_writer::link($actionurl, '<i class="fa fa-edit"></i>', array('title' => 'Edit'));
                    $actionicons .= '<span style="margin-right:8px;"></span>';
                    $deleteurl = new moodle_url($CFG->wwwroot.'/local/customnotifications/template.php',
                                                    array('id' => $template->id, 'delete' => 1));
                    $actionicons .= html_writer::link($deleteurl, '<i class="fa fa-trash"></i>', array('title' => 'Delete'));
                    $row[] = $actionicons;
                    $data[] = $row;
                }

            }

            $table->data = $data;
            $table->id = 'templates-list';
            $output .= html_writer::table($table);

             $output .= html_writer::script(' $(document).ready(function() {
                                                    $("#templates-list").dataTable();
                                                });');
        }
        return $output;
    }

    public function render_template_button() {
        global $CFG, $DB;
        $url = $CFG->wwwroot.'/local/customnotifications/template.php';

        return $this->output->single_button(new moodle_url(
                    $url, array('id' => 0)),
                    get_string('addtemplate', 'local_customnotifications'));
    }
}
