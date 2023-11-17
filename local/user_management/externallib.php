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
 * The Course Package
 *
 * @package    local_user_management
 * @author     Nadia Farheen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Nadia Farheen Limited
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/user_management/locallib.php');

class local_user_management_external extends \external_api {

    public static function user_profile_get_parameters() {

        return new external_function_parameters(array());
    }

    public static function user_profile_get() {
        global $USER, $CFG;

        $loggedinuserid = $USER->id;
        $usercontext = \context_user::instance($loggedinuserid);
        $userprofile = get_user_profile_details($USER, ['details', 'all']);

        if ($USER->picture) {
            $profilepicture = $CFG->wwwroot . '/pluginfile.php/' . $usercontext->id . '/user/icon/edumy/f1?rev=' . $USER->picture;
        } else {
            $profilepicture = $CFG->wwwroot . '/user/pix.php/'. $loggedinuserid . '/f1.jpg';
        }

        $formatteduserprofile = array();

        foreach ($userprofile as $userid => $profile) {

            $formatteduserprofile['school_name'] = '';
            $formatteduserprofile['school_code'] = '';
            $formatteduserprofile['uid'] = '';
            $formatteduserprofile['name'] = $profile->firstname . ' ' . $profile->lastname;
            $formatteduserprofile['email'] = $profile->email;
            $formatteduserprofile['profile_picture'] = $profilepicture;
            $formatteduserprofile['doj'] = ($profile->doj) ? $profile->doj : '';
            $formatteduserprofile['rating'] = ($profile->overallrating) ? $profile->overallrating : '';

            foreach ($profile->details as $detailid => $detail) {
                $formatteduserprofile['uid'] = $detail->uid;
            }

            if (isset($profile->hierarchy_details) && !empty($profile->hierarchy_details)) {

                foreach ($profile->hierarchy_details as $hierarchydetailid => $hierarchydetails) {
                    $formatteduserprofile['school_name'] = $hierarchydetails->name;
                    $formatteduserprofile['school_code'] = $hierarchydetails->code;
                }
            }

            $position = '';
            if (isset($profile->position_details) && !empty($profile->position_details)) {

                foreach ($profile->position_details as $positiondetailsid => $positiondetails) {
                    $position .= $positiondetails->name . ', ';
                }
            }

            $formatteduserprofile['position'] = rtrim($position, ', ');

            $subject = '';
            if (isset($profile->subject_details) && !empty($profile->subject_details)) {

                foreach ($profile->subject_details as $subjectdetailsid => $subjectdetails) {
                    $subject .= $subjectdetails->name.', ';
                }
            }

            $formatteduserprofile['subject'] = rtrim($subject, ', ');

            $grade = '';
            if (isset($profile->grade_details) && !empty($profile->grade_details)) {

                foreach ($profile->grade_details as $gradedetailid => $gradedetails) {
                    $grade .= $gradedetails->name.', ';
                }
            }

            $formatteduserprofile['grade'] = rtrim($grade, ', ');

            $post = '';
            if (isset($profile->post_details) && !empty($profile->post_details)) {

                foreach ($profile->post_details as $postdetailid => $postdetails) {
                    $post .= $postdetails->name.', ';
                }
            }

            $formatteduserprofile['post'] = rtrim($post, ', ');

            $caste = '';
            if (isset($profile->caste_details) && !empty($profile->caste_details)) {

                foreach ($profile->caste_details as $castedetailid => $castedetail) {
                    $caste .= $castedetail->name.', ';
                }
            }

            $formatteduserprofile['caste'] = rtrim($caste, ', ');

            $formatteduserprofile['achievements'] = $profile->achievements;
        }

        $userprofilefield[] = $formatteduserprofile;

        return [
            'data' => $userprofilefield
        ];
    }

    public static function user_profile_get_returns() {
        return new external_single_structure(
            array(
                'data' => new external_value(PARAM_RAW, 'user profile data')
            )
        );
    }

}
