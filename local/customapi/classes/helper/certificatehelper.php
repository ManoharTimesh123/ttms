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

namespace local_customapi\helper;

use local_customapi\exception\customapiException;
use stdClass;
use Throwable;
use mod_certificate_external;

require_once($CFG->dirroot . '/course/externallib.php');

class certificatehelper {

    public static function read_certificates_by_course($courseids) {
        
        $courseids = $courseids['courseids'];
        
        $coursecertificateissue = \mod_certificate_external::get_certificates_by_courses($courseids);

        return [
            'records' => $coursecertificateissue,
        ];
    }

    public static function read_view_certificate($certificateinfo) {
        
        $certificateid = $certificateinfo['certificateid'];
        
        $viewcertificate = \mod_certificate_external::view_certificate($certificateid);

        return [
            'records' => $viewcertificate,
        ];
    }

    public static function read_issue_certificate($certificateinfo) {
        
        $certificateid = $certificateinfo['certificateid'];
        
        $issuecertificate = \mod_certificate_external::issue_certificate($certificateid);
        return [
            'records' => $issuecertificate,
        ];
    }

    public static function read_all_issued_certificates($certificateinfo) {
        
        $certificateid = $certificateinfo['certificateid'];
        
        $issuedcertificate = \mod_certificate_external::get_issued_certificates($certificateid);

        return [
            'records' => $issuedcertificate,
        ];
    }
}
