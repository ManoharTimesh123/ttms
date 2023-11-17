<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * A4_non_embedded certificate type
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
/* INTG Customization Start: Certificate customizations for the attendance data on certificate for online/offline trainings.*/
global $DB;
/* INTG Customization End*/
$pdf = new PDF($certificate->orientation, 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetTitle($certificate->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// Define variables
// Landscape
if ($certificate->orientation == 'L') {
    $x = 10;
    $y = 30+30;
    $sealx = 230;
    $sealy = 150;
    $sigx = 47;
    $sigy = 155;
    $custx = 47;
    $custy = 155;
    $wmarkx = 40;
    $wmarky = 31;
    $wmarkw = 212;
    $wmarkh = 148;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 297;
    $brdrh = 210;
    $codey = 175;
/* INTG Customization Start: Added variables for attendance.*/
    $attendancex = $x + 90;
    $attendancey = $y + 62;
/* INTG Customization End. */
} else { // Portrait
    $x = 10;
    $y = 40;
    $sealx = 150;
    $sealy = 220;
    $sigx = 30;
    $sigy = 230;
    $custx = 30;
    $custy = 230;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = 250;
/* INTG Customization Start: Added variables for attendance.*/
    $attendancex = $x + 50;
    $attendancey = $y + 80;
/* INTG Customization End. */
}

/* INTG Customization Start: Added course details record for getting additional course data.*/
$course_details = $DB->get_record('local_course_details', array('course' => $course->id));
if(empty($course_details->certificatetemplate)) {
    certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
} else {
    $certificate->borderstyle = $course_details->certificatetemplate;
    certificate_print_image($pdf, $certificate, 'borders', $brdrx, $brdry, $brdrw, $brdrh);
}
/* INTG Customization End. */
certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.2);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx+165, $sigy+20, '', '');

// Add text
$pdf->SetTextColor(0, 0, 120);
/* INTG Customization Start: Certificate customizations for changes in the alignments of the elements in the certificate.*/
$pdf->SetTextColor(0, 0, 120);
certificate_print_text($pdf, $x + 0, $y + 5, 'C', 'Helvetica', 'B', 26, strtoupper(fullname($USER)));
$pdf->SetTextColor(32, 32, 32);
$contentx = $x + 10;
certificate_print_text($pdf, $contentx + 15, $y + 35, 'L', 'Helvetica', '', 14, 'Course: ');
certificate_print_text($pdf, $contentx + 35, $y + 35, 'L', 'Helvetica', 'B', 14, format_string($course->fullname));
certificate_print_text($pdf, $contentx + 15, $y + 37, 'L', 'Helvetica', '', 14, '_____________________________________');

certificate_print_text($pdf, $contentx + 12, $y + 35, 'C', 'Helvetica', '', 14, 'From: ');
certificate_print_text($pdf, $contentx + 55, $y + 35, 'C', 'Helvetica', 'B', 14, date('d/m/Y', $course->startdate));
certificate_print_text($pdf, $contentx + 42, $y + 37, 'C', 'Helvetica', '', 14, '________________');

if(!empty($course->enddate)){
    certificate_print_text($pdf, $contentx + 120, $y + 35, 'C', 'Helvetica', '', 14, 'To: ');
    certificate_print_text($pdf, $contentx + 160, $y + 35, 'C', 'Helvetica', 'B', 14, date('d/m/Y', $course->enddate));
    certificate_print_text($pdf, $contentx + 155, $y + 37, 'C', 'Helvetica', '', 14, '________________');
}

$schoolname = $DB->get_field('local_schools', 'name', array('id'=>$course_details->venue));
certificate_print_text($pdf, $contentx + 15, $y + 48, 'L', 'Helvetica', '', 14, 'School: ');
certificate_print_text($pdf, $contentx + 35, $y + 48, 'L', 'Helvetica', 'B', 14, $schoolname);
certificate_print_text($pdf, $contentx + 15, $y + 50, 'L', 'Helvetica', '', 14, '_____________________________________');

certificate_print_text($pdf, $contentx + 15, $y + 48, 'C', 'Helvetica', '', 14, 'Held at: ');
certificate_print_text($pdf, $contentx + 100, $y + 48, 'C', 'Helvetica', 'B', 14, $schoolname);
certificate_print_text($pdf, $contentx + 100, $y + 50, 'C', 'Helvetica', '', 14, '_____________________________________');

$certificatedate = certificate_get_date($certificate, $certrecord, $course);
if(!empty($certificatedate)){
    certificate_print_text($pdf, $x + 70, $y + 105, 'L', 'Helvetica', '', 12, $certificatedate);
}
certificate_print_text($pdf, $x + 105, $y + 105, 'C', 'Helvetica', '', 12, "Director SCERT");

certificate_print_text($pdf, $x + 3, $y + 128, 'L', 'Helvetica', '', 10, certificate_get_grade($certificate, $course));
certificate_print_text($pdf, $x + 3, $y + 130, 'L', 'Helvetica', '', 10, certificate_get_outcome($certificate, $course));
if ($certificate->printhours) {
    certificate_print_text($pdf, $x + 3, $y + 122, 'L', 'Helvetica', '', 10, get_string('credithours', 'certificate') . ': ' . $certificate->printhours);
}
/* INTG Customization End. */

/* INTG Customization Start: Certificate customizations for Adding attendance session data for online/offline trainings.*/
// Adding session attendance.
$moocmodalityid = $DB->get_field('local_modality', 'id', array('shortname' => 'mooc'));
$training_status = 'The participant has attended the above training';

// Check attendance for the course if marked present.
$sql = "SELECT cm.id, cm.instance
        FROM {course_modules} as cm 
        JOIN {modules} as m on m.id = cm.module
        LEFT JOIN {local_course_details} as lcd on lcd.course = cm.course
        WHERE cm.course = :course AND cm.visible = 1 
        AND m.name = 'attendance' AND lcd.batching = 1 AND lcd.modality <> :moocmodality
        ORDER BY cm.id ASC ";
$course_attendances = $DB->get_records_sql($sql, array('course' => $course->id, 'moocmodality' => $moocmodalityid));

$punctual_status = '';
if(!empty($course_attendances)){
    foreach($course_attendances as $course_attendance){
        $attendancename = $DB->get_field('attendance', 'name', array('id' => $course_attendance->instance));
        $attendance_sql = "SELECT * FROM {course_modules} as cm
                             JOIN {attendance} a ON a.id = cm.instance
                             JOIN {attendance_sessions} ats ON ats.attendanceid = a.id
                             JOIN {attendance_log} atl ON atl.sessionid = ats.id
                             JOIN {attendance_statuses} atst ON atst.id = atl.statusid
                            WHERE a.id = :attendanceid AND atl.studentid = :userid ";
        $attendance_records = $DB->get_records_sql($attendance_sql, array('attendanceid' => $course_attendance->instance, 'userid' => $USER->id));

        if(!empty($attendance_records)){
            foreach($attendance_records as $attendance_record){
                $punctual_status = 'Not-Puntual';
                if(($attendance_record->timetaken - $attendance_record->sessdate) < 900){
                    $punctual_status = 'Puntual';
                }
            }
        }
    }
    if(!empty($punctual_status)){
        $training_status .= ' and was '.$punctual_status;
    }
}

certificate_print_text($pdf, $x, $y + 65, 'C', 'Helvetica', '', 14, $training_status);
/* INTG Customization End. */

certificate_print_text($pdf, $x+3, $codey+20, 'L', 'Helvetica', '', 10, 'Certificate Code: '.certificate_get_code($certificate, $certrecord));
$i = 0;
if ($certificate->printteacher) {
    $context = context_module::instance($cm->id);
    if ($teachers = get_users_by_capability($context, 'mod/certificate:printteacher', '', $sort = 'u.lastname ASC', '', '', '', '', false)) {
        foreach ($teachers as $teacher) {
            $i++;
            certificate_print_text($pdf, $sigx, $sigy + ($i * 4), 'L', 'Helvetica', '', 12, fullname($teacher));
        }
    }
}

certificate_print_text($pdf, $custx, $custy, 'L', null, null, null, $certificate->customtext);
