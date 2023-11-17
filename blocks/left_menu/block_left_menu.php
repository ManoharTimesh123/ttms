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
 * Block displaying information about current logged-in user.
 *
 * This block can be used as anti cheating measure, you
 * can easily check the logged-in user matches the person
 * operating the computer.
 *
 * @package    block_myprofile
 * @copyright  2010 Remote-Learner.net
 * @author     Olav Jordan <olav.jordan@remote-learner.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Displays the current user's profile information.
 *
 * @copyright  2010 Remote-Learner.net
 * @author     Olav Jordan <olav.jordan@remote-learner.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/local/user_management/locallib.php');

class block_left_menu extends block_base {
    /**
     * block initializations
     */

    public function init() {
        $this->title = get_string('pluginname', 'block_left_menu');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function hide_header() {
        return true;
    }

    private function check_in_array($pagetype) {

        $pagetypesallowed = [
            'my-index',
            'local-blog',
            'local-news',
            'local-announcement',
            'local-modality',
            'local-batching',
            'local-wall',
            'blocks-venue_requests',
            'course-management',
            'course-edit',
            'local-directory',
            'local-personal_training_calendar',
            'local-annual_training_calendar',
            'local-training_transcript',
            'local-reports-index',
            'report-outline-index',
            'mod-attendance-report',
            'mod-questionnaire-report',
            'mod-certificate-report',
            'mod-progress-report',
            'mod-certificate-approvals',
            'local-need_based_trainings',
            'admin-user',
            'local-profile_management-index',
            'login-change_password',
            'local-reports-course_activity-index',
            'local-reports-course_completion-index',
            'local-reports-certificate-index',
            'local-reports-attendance-index',
            'local-reports-questionnaire-index'
        ];

        foreach ($pagetypesallowed as $allowed) {
            if (strpos($pagetype, $allowed) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $CFG, $USER;
        $this->page->requires->jquery();
        $this->page->requires->js('/blocks/left_menu/js/slidebar.js');

        $systemcontext = context_system::instance();

        if ($this->content !== null) {
            return $this->content;
        }

        // Never useful unless you are logged in as real users.
        if (!isloggedin() || isguestuser()) {
            return '';
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $menulist = '';

        $home = get_string('home', 'block_left_menu');
        $directory = get_string('directory', 'block_left_menu');
        $mytraining = get_string('mytraining', 'block_left_menu');
        $annualtrainings = get_string('annualtrainings', 'block_left_menu');
        $trainingtranscipt = get_string('trainingtranscipt', 'block_left_menu');
        $usertrainings = get_string('usertrainings', 'block_left_menu');
        $training = get_string('training', 'block_left_menu');
        $courses = get_string('courses', 'block_left_menu');
        $approvalcenter = get_string('approval_center', 'block_left_menu');
        $mastermanagement = get_string('master_management', 'block_left_menu');
        $reports = get_string('reports', 'block_left_menu');
        $ownasset = get_string('ownasset', 'block_left_menu');
        $needbasedtraining = get_string('needbasedtraining', 'block_left_menu');
        $needbasedtrainingshowinterest = get_string('needbasedtrainingshowinterest', 'block_left_menu');
        $needbasedtraininglist = get_string('needbasedtraininglist', 'block_left_menu');
        $needbasedtrainingreport = get_string('needbasedtrainingreport', 'block_left_menu');
        $managetopics = get_string('needbasedtrainingreportmanagetopic', 'block_left_menu');
        $mynbttopics = get_string('myneedbasedtopic', 'block_left_menu');
        $nbttopicreport = get_string('needbasedtopicreport', 'block_left_menu');

        if (
            strpos($this->page->pagetype, 'local-blog') === 0
            || strpos($this->page->pagetype, 'local-news') === 0
            || strpos($this->page->pagetype, 'local-announcement') === 0
            || strpos($this->page->pagetype, 'local-wall') === 0
            || strpos($this->page->pagetype, 'blocks-venue_requests') === 0
            || strpos($this->page->pagetype, 'mod-certificate-approvals') === 0
        ) {
            $approvalclass = '';
        } else {
            $approvalclass = get_string('collapse', 'block_left_menu');
        }

        if (
            strpos($this->page->pagetype, 'local-batching') === 0
        ) {
            $batchingclass = '';
        } else {
            $batchingclass = get_string('collapse', 'block_left_menu');
        }

        if (
            strpos($this->page->pagetype, 'course') === 0
            || strpos($this->page->pagetype, 'local-reports-index') === 0
        ) {
            $courseclass = '';
        } else {
            $courseclass = get_string('collapse', 'block_left_menu');
        }

        if (
            strpos($this->page->pagetype, 'local-need_based_trainings') === 0
        ) {
            $needbasedtrainingclass = '';
        } else {
            $needbasedtrainingclass = get_string('collapse', 'block_left_menu');
        }

        $menu = "";

        if (has_capability('local/directory:view', $systemcontext) || is_siteadmin()) {
            $menu .= "
                <li class='list-group-item border-0'>
                <a
                    class='" . (($this->page->pagetype == 'local-directory-index') ? 'active' : '') . "'
                    href='" . new moodle_url($CFG->wwwroot . '/local/directory') . "'>
                    <i class='flaticon-elearning'></i>
                    $directory
                </a>
                </li>
                ";
        }

        if (has_capability('local/annual_training_calendar:view', $systemcontext) || is_siteadmin()) {
            $menu .= "
                <li class='list-group-item border-0'>
                <a
                    class='" . (($this->page->pagetype == 'local-annual_training_calendar-index') ? 'active' : '') . "'
                    href='" . new moodle_url($CFG->wwwroot . '/local/annual_training_calendar') . "'>
                    <i class='flaticon-3d'></i>
                    $annualtrainings
                </a>
                </li>
                ";
        }

        if (has_any_capability(
                [
                    'local/need_based_trainings:view',
                    'local/need_based_trainings:viewall',
                    'local/need_based_trainings:topicmanage',
                ],
                $systemcontext
            ) || is_siteadmin()
        ) {
            $needbasedtrainingmenu = "";

            if (has_capability('local/need_based_trainings:view', $systemcontext) || is_siteadmin()) {
                $needbasedtrainingmenu .= "
                            <li>
                            <a
                                class='" . (($this->page->pagetype == 'local-need_based_trainings-listing') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/need_based_trainings/listing.php') . "'>
                                $needbasedtraininglist
                               </a>
                            </li>
                            ";
            }

            if (has_capability('local/need_based_trainings:view', $systemcontext) || is_siteadmin()) {
                $needbasedtrainingmenu .= "
                            <li>
                            <a
                                class='" . (($this->page->pagetype == 'local-need_based_trainings-show_interest') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/need_based_trainings/show_interest.php') . "'>
                                $needbasedtrainingshowinterest
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/need_based_trainings:viewall', $systemcontext) || is_siteadmin()) {
                $needbasedtrainingmenu .= "
                            <li>
                            <a
                                class=' " . (($this->page->pagetype == 'local-need_based_trainings-need_based_training_report') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/need_based_trainings/need_based_training_report.php') . "'>
                                $needbasedtrainingreport
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/need_based_trainings:view', $systemcontext)) {
                $needbasedtrainingmenu .= "
                            <li>
                            <a
                                class='" . (($this->page->pagetype == 'local-need_based_trainings-topics-my_need_based_topics') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/need_based_trainings/topics/my_need_based_topics.php') . "'>
                                $mynbttopics
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/need_based_trainings:viewall', $systemcontext)) {
                $needbasedtrainingmenu .= "
                            <li>
                            <a
                                class='" . (($this->page->pagetype == 'local-need_based_trainings-topics-need_based_topic_report') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/need_based_trainings/topics/need_based_topic_report.php') . "'>
                                $nbttopicreport
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/need_based_trainings:topicmanage', $systemcontext)) {
                $needbasedtrainingmenu .= "
                            <li>
                            <a
                                class='" . (($this->page->pagetype == 'local-need_based_trainings-topics-listing') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/need_based_trainings/topics/listing.php') . "'>
                                $managetopics
                            </a>
                            </li>
                            ";
            }

            $menu_hidden .= "
			<li class='list-group-item border-0'>
			<a href='#needbasedtraining' data-toggle='collapse' aria-expanded='false' class='dropdown-toggle collapsed'>
			<i class='flaticon-checklist'></i> $needbasedtraining
				</a>
				<ul class='list-unstyled " . $needbasedtrainingclass . "' id='needbasedtraining'>
					" . $needbasedtrainingmenu . "
				</ul>
			</li>
			";
        }

        if (has_any_capability(
                [
                    'local/personal_training_calendar:view',
                    'local/training_transcript:view',
                    'local/blog:manageown',
                    'local/wall:manageown',
                    'local/news:manageown',
                    'local/modality:venueapprove',
                ],
                $systemcontext
            ) && !is_siteadmin()
        ) {
            $ownassetsmenu = "";

            if (has_capability('local/personal_training_calendar:view', $systemcontext)) {
                $ownassetsmenu .= "
                            <li>
                            <a
                                class='" . (($this->page->pagetype == 'local-personal_training_calendar-index') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/personal_training_calendar') . "'>
                                $mytraining
                               </a>
                            </li>
                            ";
            }

            if (has_capability('local/training_transcript:view', $systemcontext) && !has_capability('local/training_transcript:viewall', $systemcontext)) {
                $ownassetsmenu .= "
                            <li>
                            <a
                                class='" . (($this->page->pagetype == 'local-training_transcript') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/training_transcript') . "'>
                                $trainingtranscipt
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/blog:manageown', $systemcontext)) {
                $ownassetsmenu .= "
                            <li>
                            <a
                                class=' " . (($this->page->pagetype == 'local-blog-listing') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/blog/listing.php') . "'>
                                " . get_string('blogs', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/wall:manageown', $systemcontext)) {
                $ownassetsmenu .= "
                            <li>
                            <a
                                class=' " . (($this->page->pagetype == 'local-wall-manage') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/wall/manage.php') . "'>
                                " . get_string('wall', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/news:manageown', $systemcontext)) {
                $ownassetsmenu .= "
                            <li>
                            <a
                                class=' " . (($this->page->pagetype == 'local-news-listing') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/news/listing.php') . "'>
                                " . get_string('news', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/modality:venueapprove', $systemcontext)) {
                $ownassetsmenu .= "
                            <li>
                            <a
                                class=' " . (($this->page->pagetype == 'blocks-venue_requests-listing') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/blocks/venue_requests/listing.php') . "'>
                                " . get_string('venues', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            $menu .= "
			<li class='list-group-item border-0'>
			<a href='#ownasset' data-toggle='collapse' aria-expanded='false' class='dropdown-toggle collapsed'>
			<i class='flaticon-checklist'></i> $ownasset
				</a>
				<ul class='list-unstyled " . $approvalclass . "' id='ownasset'>
					" . $ownassetsmenu . "
				</ul>
			</li>
			";
        }

        if (has_any_capability(
                [
                    'local/batching:propose',
                    'local/batching:perform',
                    'local/batching:approve',
                ],
                $systemcontext
            ) || is_siteadmin()
        ) {
            $menu .= "
                    <li class='list-group-item border-0 small-menu'>
                    <a href='#training' data-toggle='collapse' aria-expanded='false' class='dropdown-toggle collapsed'>
                    <i class='flaticon-online-learning'></i> $training
                    </a>
                    <ul class='list-unstyled " . $batchingclass . "' id='training'>";
            if (has_capability('local/batching:propose', $systemcontext) || is_siteadmin()) {
                $menu .= "
                    <li>
                    <a
                        class='" . (($this->page->pagetype == 'local-batching-start') ? 'active' : '') . "'
                        href='" . new moodle_url($CFG->wwwroot . '/local/batching/start.php') . "'>
                        " . get_string('proposenewtraining', 'block_left_menu') . "
                    </a>
                    </li>
                    ";
            }

            if (has_capability('local/batching:perform', $systemcontext) || is_siteadmin()) {
                $menu .= "
                    <li>
                    <a
                        class='" . (($this->page->pagetype == 'local-batching-index') ? 'active' : '') . "'
                        href='" . new moodle_url($CFG->wwwroot . '/local/batching/index.php') . "'>
                        " . get_string('performbatching', 'block_left_menu') . "
                    </a>
                    </li>
                    ";
            }

            if (has_any_capability(
                    [
                        'local/batching:propose',
                        'local/batching:perform',
                    ],
                    $systemcontext
                ) || is_siteadmin()
            ) {
                $menu .= "
                    <li>
                    <a
                        class='" . (($this->page->pagetype == 'local-batching-batched_trainings') ? 'active' : '') . "'
                        href='" . new moodle_url($CFG->wwwroot . '/local/batching/batched_trainings.php') . "'>
                        " . get_string('batchedtrainings', 'block_left_menu') . "
                    </a>
                    </li>
                    ";
            }

            if (has_capability('local/batching:approve', $systemcontext) || is_siteadmin()) {
                $menu .= "
                    <li>
                    <a
                        class='" . (($this->page->pagetype == 'local-batching-approved_trainings') ? 'active' : '') . "'
                        href='" . new moodle_url($CFG->wwwroot . '/local/batching/approved_trainings.php') . "'>
                        " . get_string('approvedtrainings', 'block_left_menu') . "
                    </a>
                    </li>
                    ";
            }
            $menu .= "</ul>
            </li>";
        }

        if (has_any_capability(
                [
                    'moodle/course:create',
                    'moodle/category:manage'
                ],
                $systemcontext
            ) || is_siteadmin()
        ) {
            $coursemenu = "";
            if (has_capability('moodle/course:create', $systemcontext) || is_siteadmin()) {
                $coursemenu .= "
                            <li>
                            <a
                                class=' " . (($this->page->pagetype == 'course-edit') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/course/edit.php') . "'>
                                " . get_string('addcourse', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            if (has_capability('moodle/category:manage', $systemcontext) || is_siteadmin()) {
                $coursemenu .= "
                            <li>
                            <a
                                class=' " . (($this->page->pagetype == 'course-management') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/course/management.php') . "'>
                               " . get_string('managecourses', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }
            $menu .= "
                    <li class='list-group-item border-0'>
                    <a href='#courses' data-toggle='collapse' aria-expanded='false' class='dropdown-toggle collapsed'>
                    <i class='flaticon-checklist'></i> $courses
                        </a>
                        <ul class='list-unstyled " . $courseclass . "' id='courses'>
                           " . $coursemenu . "
                        </ul>
                    </li>
                    ";
        }

        if (has_any_capability(
                [
                    'local/blog:manage',
                    'local/news:manage',
                    'local/wall:manage',
                    'local/announcement:manage',
                    'mod/certificate:approve'
                ],
                $systemcontext
            ) || is_siteadmin()
        ) {
            $approvalcentermenue = '';
            if (has_capability('local/blog:manage', $systemcontext) || is_siteadmin()) {
                $approvalcentermenue .= "
                                    <li>
                                    <a
                                        class=' " . (($this->page->pagetype == 'local-blog-listing') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/blog/listing.php') . "'>
                                        " . get_string('blogs', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/news:manage', $systemcontext) || is_siteadmin()) {
                $approvalcentermenue .= "
                                    <li>
                                    <a
                                        class=' " . (($this->page->pagetype == 'local-news-listing') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/news/listing.php') . "'>
                                        " . get_string('news', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/announcement:manage', $systemcontext) || is_siteadmin()) {
                $approvalcentermenue .= "
                                    <li>
                                    <a
                                        class=' " . (($this->page->pagetype == 'local-announcement-listing') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/announcement/listing.php') . "'>
                                        " . get_string('announcements', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/wall:manage', $systemcontext) || is_siteadmin()) {
                $approvalcentermenue .= "
                                    <li>
                                    <a
                                        class=' " . (($this->page->pagetype == 'local-wall-manage') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/wall/manage.php') . "'>
                                        " . get_string('wall', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('mod/certificate:approve', $systemcontext) || is_siteadmin()) {
                $approvalcentermenue .= "
                                    <li>
                                    <a
                                        class=' " . (($this->page->pagetype == 'mod-certificate-approvals') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/mod/certificate/approvals.php') . "'>
                                        " . get_string('certificates', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            $menu .= "
                    <li class='list-group-item border-0'>
                    <a href='#approvalCenter' data-toggle='collapse' aria-expanded='false' class='dropdown-toggle collapsed'>
                    <i class='flaticon-account'></i> $approvalcenter
                    </a>
                        <ul class='list-unstyled " . $approvalclass . "' id='approvalCenter'>
                            " . $approvalcentermenue . "
                        </ul>
                    </li>
            ";
        }

        if (has_any_capability(
                [
                    'local/modality:manage',
                    'local/modality:departmentmanage',
                    'local/modality:departmentmanage',
                    'local/modality:zonemanage',
                    'local/modality:dietmanage',
                    'local/modality:schoolmanage',
                    'local/modality:subjectmanage',
                    'local/modality:schoolpositionmanage',
                    'local/modality:castemanage',
                    'local/modality:grademanage',
                    'local/modality:postmanage',
                    'local/modality:financialcategorymanage',
                    'local/modality:financialcategorydetailmanage',
                    'moodle/user:update',
                ],
                $systemcontext
            ) || is_siteadmin()
        ) {
            $mastermanagementmenu = "";
            if (has_capability('local/modality:manage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-index') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality') . "'>
                                        " . get_string('modality', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:departmentmanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_departments') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_departments.php') . "'>
                                        " . get_string('departments', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:districtmanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_districts') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_districts.php') . "'>
                                        " . get_string('districts', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:zonemanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_zones') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_zones.php') . "'>
                                        " . get_string('zones', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:dietmanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_diets') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_diets.php') . "'>
                                        DIET's
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:schoolmanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_schools') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_schools.php') . "'>
                                        " . get_string('schools', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:subjectmanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_subjects') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_subjects.php') . "'>
                                        " . get_string('subjects', 'block_left_menu') . "
                                        </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:schoolpositionmanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_school_positions') ? 'active' : '') . "'
                                        ' href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_school_positions.php') . "'>
                                        " . get_string('schoolpositions', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:castemanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_castes') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_castes.php') . "'>
                                        " . get_string('castes', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:grademanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_grades') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_grades.php') . "'>
                                        " . get_string('grades', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:postmanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_posts') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_posts.php') . "'>
                                        " . get_string('posts', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:financialcategorymanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_financial_categories') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_financial_categories.php') . "'>
                                        " . get_string('financialcategories', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('local/modality:financialcategorydetailmanage', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-modality-show_financial_details') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/modality/show_financial_details.php') . "'>
                                        " . get_string('financialcategorydetails', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('moodle/user:update', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'admin-user') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/admin/user.php') . "'>
                                        " . get_string('usermanagement', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }

            if (has_capability('moodle/user:update', $systemcontext) || is_siteadmin()) {
                $mastermanagementmenu .= "
                                    <li>
                                    <a
                                        class='" . (($this->page->pagetype == 'local-directory-admin') ? 'active' : '') . "'
                                        href='" . new moodle_url($CFG->wwwroot . '/local/directory/admin.php') . "'>
                                        " . get_string('managedirectory', 'block_left_menu') . "
                                    </a>
                                    </li>
                                    ";
            }
            $menu .= "
                    <li class='list-group-item border-0'>
                    <a href='#pageSubmenu' data-toggle='collapse' aria-expanded='false' class='dropdown-toggle collapsed'>
                    <i class='flaticon-global'></i> $mastermanagement
                    </a>
                        <ul class='list-unstyled  " . (((strpos($this->page->pagetype, 'local-modality') === 0) ||
                                                         (strpos($this->page->pagetype, 'admin-user') === 0) ||
                                                         (strpos($this->page->pagetype, 'local-directory-admin') === 0))
                             ?
                         '' : 'collapse') . "' id='pageSubmenu'>
                         " . $mastermanagementmenu . "
                        </ul>
                    </li>
            ";
        }

        if (has_any_capability(
                [
                    'local/reports:trainingactivity',
                    'local/reports:trainingcompletion',
                    'local/reports:feedback',
                    'local/reports:attendance',
                    'local/reports:certificate',
                    'local/training_transcript:viewall'
                ],
                $systemcontext
            ) || is_siteadmin()
        ) {
            $reportsmenu = '';
            if (has_capability('local/reports:trainingactivity', $systemcontext) || is_siteadmin()) {
                $reportsmenu .= "
                            <li>
                            <a
                                class='" . ((($this->page->pagetype == 'report-outline-index') ||
                                            ($this->page->pagetype == 'local-reports-course_activity-index')) ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/reports/course_activity/index.php') . "'>
                                " . get_string('trainingactivityreport', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/reports:trainingcompletion', $systemcontext) || is_siteadmin()) {
                $reportsmenu .= "
                            <li>
                            <a
                                class='" . ((($this->page->pagetype == 'mod-progress-report') ||
                                            ($this->page->pagetype == 'local-reports-course_completion-index')) ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/reports/course_completion/index.php') . "'>
                                " . get_string('trainingcompletionreport', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/reports:feedback', $systemcontext) || is_siteadmin()) {
                $reportsmenu .= "
                            <li>
                            <a
                                class='" . ((($this->page->pagetype == 'mod-questionnaire-report') ||
                                            ($this->page->pagetype == 'local-reports-questionnaire-index')) ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/reports/questionnaire/index.php') . "'>
                                " . get_string('feedbackreport', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/reports:attendance', $systemcontext) || is_siteadmin()) {
                $reportsmenu .= "
                            <li>
                            <a
                                class='" . ((($this->page->pagetype == 'mod-attendance-report') ||
                                            ($this->page->pagetype == 'local-reports-attendance-index')) ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/reports/attendance/index.php') . "'>
                                " . get_string('attendancereport', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/reports:certificate', $systemcontext) || is_siteadmin()) {
                $reportsmenu .= "
                            <li>
                            <a
                                class='" . ((($this->page->pagetype == 'mod-certificate-report') ||
                                            ($this->page->pagetype == 'local-reports-certificate-index')) ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/reports/certificate/index.php') . "'>
                                " . get_string('certificatereport', 'block_left_menu') . "
                            </a>
                            </li>
                            ";
            }

            if (has_capability('local/training_transcript:viewall', $systemcontext) || is_siteadmin()) {
                $reportsmenu .= "
                            <li>
                            <a
                                class='" . (($this->page->pagetype == 'local-training_transcript-index') ? 'active' : '') . "'
                                href='" . new moodle_url($CFG->wwwroot . '/local/training_transcript') . "'>
                                $usertrainings
                            </a>
                            </li>
                            ";
            }
            $menu .= "
                    <li class='list-group-item border-0'>
                    <a href='#pageSubmenureport' data-toggle='collapse' aria-expanded='false' class='dropdown-toggle collapsed'>
                    <i class='flaticon-checklist'></i> $reports
                    </a>
                        <ul class='list-unstyled
                        " . (((strpos($this->page->pagetype, 'local-reports') === 0) ||
                              (strpos($this->page->pagetype, 'mod') === 0) ||
                              (strpos($this->page->pagetype, 'local-training_transcript') === 0))? '' : 'collapse') . "
                        ' id='pageSubmenureport'> " . $reportsmenu . "
                        </ul>
                    </li>
            ";
        }

        if (!empty($menu) && $this->check_in_array($this->page->pagetype)) {
            $menulist =
                <<<MENU
                        <!--div class="font-weight-normal px-3 pt-3 left-side-menu-heading">MENU
                        </div-->
                        <ul class="list-group left-side-menu">
                            $menu
                        </ul>
                </li>
                </ul>
                MENU;
        }

        $this->content->text = $menulist;

        /* Get current active role. if role is not equal to instructor then dont proceed. return empty string */

        return $this->content;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }

    /**
     * allow more than one instance of the block on a page
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        // Allow more than one instance on a page.
        return false;
    }

    /**
     * allow instances to have their own configuration
     *
     * @return boolean
     */
    public function instance_allow_config() {
        // Allow instances to have their own configuration.
        return false;
    }

    /**
     * instance specialisations (must have instance allow config true)
     *
     */
    public function specialization() {
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    /**
     * post install configurations
     *
     */
    public function after_install() {
    }

    /**
     * post delete configurations
     *
     */
    public function before_delete() {
    }

}
