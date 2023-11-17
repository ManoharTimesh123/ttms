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
 * This main view page for a local_modality
 *
 * @package local_modality
 * @copyright  2016 Mike Churchward (mike.churchward@poetgroup.org)
 * @author Mike Churchward
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

 defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
/* INTG Customization Start : Calling a function for finding all user role*/
require_once('locallib.php');

class user_overall_ratings_form extends moodleform {
    public function definition() {
        
        $mform =& $this->_form;
        $roleoptions = get_all_user_course_role();
        $mform->addElement('select' , 'provided_by_user_roleid' , get_string('rateprovidedby' , 'local_modality') , $roleoptions);
        $mform->addHelpButton('provided_by_user_roleid' , 'provided_by_user_roleid' , 'local_modality' );
        $mform->addElement('select' , 'received_by_user_roleid' , get_string('ratereceivedby' , 'local_modality') , $roleoptions);
        $mform->addHelpButton('received_by_user_roleid' , 'received_by_user_roleid' , 'local_modality' );
        $mform->addElement('text' , 'rate_weightage' , get_string('rateweightage' , 'local_modality'));
        $mform->addHelpButton('rate_weightage' , 'rate_weightage' , 'local_modality' );
        $this->add_action_buttons();
    }
}

/* INTG Customization End */
