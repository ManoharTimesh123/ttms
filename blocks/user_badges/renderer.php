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
 * User Badges
 * @package    block_user_badges
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/user_badges/locallib.php');

function render_user_badges_grid() {

    $userbadges = get_badges();

    $badgehtml = '';

    if ($userbadges) {
        $badgehtml .= '<div class="stats_container border-0 shadow-sm rounded  text-center px-2 py-4 mt-0 mb-3">
                   <div class="stats_count font-weight-bold display-4">' . $userbadges . '</div>
                   <div class="stats_label">' . get_string('badges', 'block_user_badges') . '</div>
                </div>';
    }

    return $badgehtml;
}
