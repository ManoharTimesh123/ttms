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
 * Bulk user upload forms
 *
 * @package    local
 * @subpackage otp_form
 * @copyright  2007 Dan Poltawski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/editlib.php');


/**
 * Registration for Student with user information.
 *
 * @copyright  2007 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_fieldadd extends moodleform {
    public function definition () {
        $mform = $this->_form;
        $mform->addElement('html', '<div class="heading">' . get_string('addfield', 'local_directory') .'</div>');
        $mform->addElement('text', 'fieldname', get_string('fieldname', 'local_directory'), get_string('fieldname', 'local_directory'));
        $mform->addRule('fieldname', get_string('fieldrequired', 'local_directory'), 'required');
        $mform->setType('fieldname', PARAM_RAW);

        $mform->addElement('text', 'dependent', get_string('dependenttablename', 'local_directory'), get_string('dependenttablename', 'local_directory'));

        $mform->addElement('text', 'dependent_on', get_string('dependantfield', 'local_directory'), get_string('dependantfield', 'local_directory'));

        $this->add_action_buttons();

    }

    /**
     * Validate incoming form data.
     * @param array $fields
     * @param array $files
     * @return array
     */
    public function validation($fields, $files) {
        global $CFG, $DB;

        $errors = parent::validation($fields, $files);

        $fields = (object)$fields;
        if (!is_string($fields->fieldname)) {
            $errors['fieldname'] = get_string('fieldnamevalidations', 'local_directory');
        } else if ($fields->fieldname) {
            $paramsuser = [
                'user_table_name' => $CFG->prefix.'user',
                'user_details_table_name' => $CFG->prefix.'local_user_details',
                'columnname' => $fields->fieldname
            ];
            $sqluser = "SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE (TABLE_NAME = :user_table_name OR TABLE_NAME = :user_details_table_name)
            AND COLUMN_NAME = :columnname";
            $usercolumnname = $DB->get_record_sql($sqluser, $paramsuser);
            if (!$usercolumnname) {
                $errors['fieldname'] = get_string('columnnamenotexists', 'local_directory');
            }
        }

        if ($fields->dependent) {
            if ($fields->dependent_on) {
                $params = [
                    'table_name' => $CFG->prefix.$fields->dependent,
                    'columnname' => $fields->dependent_on
                ];
                $sql = "SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = :table_name
                AND COLUMN_NAME = :columnname";
                $columnname = $DB->get_record_sql($sql, $params);
                if (!$columnname) {
                    $errors['dependent_on'] = get_string('columnnamenotexists', 'local_directory');
                }
            } else {

                $errors['dependent_on'] = get_string('fieldrequired', 'local_directory');
            }
        }
        return $errors;
    }

}
