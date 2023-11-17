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
 * Announcement
 *
 * @package    local_announcement
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class announcement_create_form extends moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;
        $id = ($this->_customdata['data']) ? $this->_customdata['data']->id : null;
        $customdata = ($this->_customdata['data']) ? $this->_customdata['data'] : null;
        $mform->addElement('header', 'announcement', get_string('createannouncement', 'local_announcement'), '');

        $mform->addElement('text', 'title', get_string('name', 'local_announcement'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required', null, 'client');

        $mform->addElement('editor', 'description', get_string('announcementdescription', 'local_announcement'), null);
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', get_string('required'), 'required', null, 'client');

        $mform->addElement('filemanager', 'announcementimage', get_string('uploadfile', 'local_announcement'), null,
        array('accepted_types' => array('.png', '.jpg', '.jpeg'), 'maxbytes' => 2097152, 'maxfiles' => 1));

        $mform->addElement('date_selector', 'datefrom', get_string('announcementvisiblefrom', 'local_announcement'));
        $mform->addElement('date_selector', 'dateto', get_string('announcementvisibleto', 'local_announcement'));

        $districts = $DB->get_records('local_districts', null , 'name desc');
        foreach ($districts as $district) {
            $districtsarray[$district->id] = $district->name;
        }
        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
        $select = $mform->addElement('autocomplete', 'districts', get_string('district', 'local_announcement'),
        $districtsarray, $options);
        if ($customdata) {
            $selecteddistricts = explode(',', $customdata->districtid);
            $select->setSelected($selecteddistricts);
        }
        $select->setMultiple(true);

        $diets = $DB->get_records('local_diets', null, 'name desc');
        foreach ($diets as $diet) {
            $dietsarray[$diet->id] = $diet->name;
        }

        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
        $select = $mform->addElement('autocomplete', 'diets', get_string('diet', 'local_announcement'), $dietsarray, $options);
        if ($customdata) {
            $selecteddiets = explode(',', $customdata->dietid);
            $select->setSelected($selecteddiets);
        }
        $select->setMultiple(true);

        $zones = $DB->get_records('local_zones', null, 'name desc');
        foreach ($zones as $zone) {
            $zonearray[$zone->id] = $zone->name;
        }

        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
        $select = $mform->addElement('autocomplete', 'zones', get_string('zone', 'local_announcement'), $zonearray, $options);
        if ($customdata) {
            $selectedzones = explode(',', $customdata->zoneid);
            $select->setSelected($selectedzones);
        }
        $select->setMultiple(true);

        $schools = $DB->get_records('local_schools', null, 'name desc');
        foreach ($schools as $school) {
            $schoolarray[$school->id] = $school->name;
        }

        $options = array('multiple' => true, 'noselectionstring' => get_string('select'));
        $select = $mform->addElement('autocomplete', 'schools', get_string('school', 'local_announcement'), $schoolarray, $options);
        if ($customdata) {
            $selectedschools = explode(',', $customdata->schoolid);
            $select->setSelected($selectedschools);
        }
        $select->setMultiple(true);

        $mform->addElement('advcheckbox', 'global', get_string('globalannouncement', 'local_announcement'), array(),
        array('group' => 1), array(0, 1));

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges', 'local_announcement'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['dateto'] < $data['datefrom']) {
            $errors['dateto'] = 'End date must be equal or greater than start date.';
        }
        return $errors;
    }

}
