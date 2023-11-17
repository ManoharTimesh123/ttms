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
 * Training Schedule
 * @package    block_training_schedule
 */

defined('MOODLE_INTERNAL') || die();

function renderer_role_block($courseid, $groupid, $roleid) {
    global $DB, $OUTPUT;

    $context = context_course::instance($courseid);
    $allgroupusers = get_enrolled_users($context, '', $groupid);
    $roleblocktext = '';

    foreach ($allgroupusers as $allgroupuser) {
        if ($DB->record_exists('role_assignments', ['userid' => $allgroupuser->id, 'roleid' => $roleid, 'contextid' => $context->id])) {
            $roleblocktext .= '<div class="bg-white rounded-lg p-3 shadow-sm mb-3">';
            $roleblocktext .= '<div class="media">';

            if ($allgroupuser->username) {
                $roleblocktext .= $OUTPUT->user_picture($allgroupuser, array('size' => 50)) . '</br>';
            }

            $roleblocktext .= '<div class="media-body ml-1">';
            if ($allgroupuser->firstname) {
                $roleblocktext .= '<h5 class="font-weight-bold"> ' . $allgroupuser->firstname . '</h5>';
            }

            if ($allgroupuser->email) {
                $roleblocktext .= '<p class="mb-0"> ' . $allgroupuser->email . '</p>';
            }

            $roleblocktext .= '</div></div></div>';
        }
    }
    return $roleblocktext;
}
