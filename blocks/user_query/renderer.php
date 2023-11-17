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
 * User Overall ratings
 * @package    block_user_query
 */


function render_user_query_form() {
    global $CFG;
    $data = '';
    $data .= '<div id = "userformmsg"></div>';
    $data .= '<form class="userForm" id="userForm">
	                <div class="form-group">
                        <label for="useremail" id="useremail">' . get_string('user_email', 'block_user_query') . '</label>
                        <input id="useremail" name="useremail" type="email" required="required"
						title="user email details" value="" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="user_query_label" id="user_query_label">' . get_string('user_query', 'block_user_query') . '</label>
                        <textarea id="userquery" name="userquery" rows="3"
						title="user query details" required="required" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="submit" id="submit" class="btn btn-primary">' . get_string('save', 'block_user_query') . '</button>
                    </div>
                </form>';
    return $data;
}
