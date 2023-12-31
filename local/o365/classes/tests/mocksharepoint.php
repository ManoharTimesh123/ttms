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
 * A mock sharepoint API class.
 *
 * @package local_o365
 * @author James McQuillan <james.mcquillan@remote-learner.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_o365\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * A mock sharepoint API class.
 *
 * @codeCoverageIgnore
 */
class mocksharepoint extends \local_o365\rest\sharepoint {
    /**
     * Create a course subsite.
     *
     * @param \stdClass $course A course record to create the subsite from.
     * @return \stdClass An association record.
     */
    public function create_course_subsite($course) {
        return parent::create_course_subsite($course);
    }
}
