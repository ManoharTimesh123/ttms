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
 * Training Transcript
 *
 * @package    local_training_transcript
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/training_transcript/renderer.php');
global $USER;

$systemcontext = \context_system::instance();
$trainingid = $_POST['trainingid'];
$user = $_POST['userid'];
$userid = $USER->id;

if (has_capability('local/training_transcript:viewall', $systemcontext) &&
    isset($user) &&
    !empty($user)
) {
    $userid = $user;
}

echo render_training_transcript_detail_grid($trainingid, $userid);
exit();
