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

namespace theme_edumy\output\core_user\myprofile;

defined('MOODLE_INTERNAL') || die;

use \core_user\output\myprofile\category;
use core_user\output\myprofile\tree;
use core_user\output\myprofile\node;
use core_user\output\myprofile;
use html_writer;
use context_course;
use core_course_list_element;
use DateTime;
use core_date;
use moodle_url;
use ccnUserHandler;

use context_system;

class renderer extends \core_user\output\myprofile\renderer {

  public function render_tree(tree $tree) {
      global $CFG, $USER, $DB, $SESSION, $SITE, $PAGE, $OUTPUT;

      $ccn_user_id = optional_param('id', 0, PARAM_INT);
      $ccn_user_id = $ccn_user_id ? $ccn_user_id : $USER->id;       // Owner of the page.

      $ccn_page = new \stdClass();
      $ccn_page->id = $ccn_user_id;


      $ccnUserHandler = new ccnUserHandler();
      $ccnUser = $ccnUserHandler->ccnGetUserDetails($ccn_user_id);

      // print_object($ccnUser);

      if(!($ccn_page->id == $USER->id)) {
        if (!isset($SESSION->theme_edumy_counter)) {
          $SESSION->theme_edumy_counter = array();
        }
        if (!isset($SESSION->theme_edumy_counter[$ccn_page->id])) {
          $SESSION->theme_edumy_counter[$ccn_page->id] = array();
        }

        $ccn_ip = getremoteaddr();
        $ccn_ip = bin2hex($ccn_ip);
        $ccn_ip = substr($ccn_ip, 0, 15); //char15 limit
        $ccn_time_difference = 0;

        if (!isset($SESSION->theme_edumy_counter[$ccn_page->id]['time'])) {
          $sql = "SELECT MAX(time) AS mintime FROM {$CFG->prefix}theme_edumy_counter
              WHERE course = {$ccn_page->id}
              AND ip = '$ccn_ip'";

          $time = $DB->get_record_sql($sql);

          $SESSION->theme_edumy_counter[$ccn_page->id]['time'] = $time && $time->mintime ? $time->mintime : 0;
        }

        $ccn_increase = false;

        if ($SESSION->theme_edumy_counter[$ccn_page->id]['time'] < (time() - $ccn_time_difference)) {
          $dataobject = new \stdClass();
          $dataobject->ip = $ccn_ip;
          $dataobject->course = $ccn_page->id;
          $dataobject->time = time();
          $DB->insert_record('theme_edumy_counter', $dataobject, false);
          $SESSION->theme_edumy_counter[$ccn_page->id]['time'] = time();
          $ccn_increase = true;
        }

      }

      // need return first
      $return = '';

      if($PAGE->theme->settings->user_profile_layout != 1){ //Edumy Frontend
        $ccn_col_main = 'col-md-12 col-lg-8 col-xl-8';
        $ccn_col_side = 'col-lg-4 col-xl-4';
        $ccn_col_side_block = 'selected_filter_widget siderbar_contact_widget style2 mb30';
        $ccn_col_block_title = '';
        $ccn_col_side_block_content = '';
        $ccn_col_main_block = 'b0p0';
      } else { //Edumy Dashboard
        $ccn_col_main = 'col-md-12 col-lg-12 col-xl-12';
        $ccn_col_side = 'col-lg-4 col-xl-4';
        $ccn_col_side_block = 'ccnDashBl mb30 p0';
        $ccn_col_block_title = 'ccnDashBlHd';
        $ccn_col_side_block_content = 'ccnDashBlCt siderbar_contact_widget';
        $ccn_col_main_block = 'ccnDashBl b0';
      }

      // begin new
      $userData = get_complete_user_data('id', $ccn_user_id);
      $moreUserData = $DB->get_record('user', array('id' => $ccn_user_id), '*', MUST_EXIST);
      $user_details = $DB->get_record('local_user_details', array('userid' => $ccn_user_id));
      $userDescription = file_rewrite_pluginfile_urls($moreUserData->description, 'pluginfile.php', $ccn_user_id, 'user', 'profile', null);
      $userDescription = format_text($userDescription, FORMAT_HTML, array('filter' => true));

      $userFirst = $userData->firstname;
      $userLast = $userData->lastname;
      $userIcq = $userData->icq;
      $userSkype = $userData->skype;
      $userYahoo = $userData->yahoo;
      $userAim = $userData->aim;
      $userMsn = $userData->msn;
      $userPhone1 = $userData->phone1;
      $userPhone2 = $userData->phone2;
      $userSince = userdate($userData->timecreated);
      $userLastLogin = userdate($userData->lastaccess);
      $userStatus = $userData->currentlogin;
      $userEmail = $userData->email;
      $userLang = $userData->lang.'-Latn-IT-nedis';
      if (class_exists('Locale')) {
        $userLanguage = \Locale::getDisplayLanguage($userLang, $CFG->lang);
      }
      $userEnroledCourses = enrol_get_users_courses($ccn_user_id);
      $enrolmentCount = count($userEnroledCourses);

      //check if user is a teacher ANYWHERE in Moodle
      $teacherRole = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));
      $isTeacher = $DB->record_exists('role_assignments', ['userid' => $ccn_user_id, 'roleid' => $teacherRole]);
      $teachingCourses = $DB->get_records('role_assignments', ['userid' => $ccn_user_id, 'roleid' => $teacherRole]);
      $teachingCoursesCount = count($teachingCourses);

      $teachingStudentCount = 0;
      foreach($teachingCourses as $course) {
        $courseID = $course->id;
        if ($DB->record_exists('course', array('id' => $courseID))) {
          $context = context_course::instance($courseID);
          $numberOfUsers = count_enrolled_users($context);
          $teachingStudentCount+= $numberOfUsers;
        }
      }

      $userLastCourses = $userData->lastcourseaccess;

      $ccnProfileCountTable = 'theme_edumy_counter';
      $ccnProfileCountConditions = array('course'=>$ccn_page->id);
      $ccnProfileViews = $DB->get_records($ccnProfileCountTable,array('course'=>$ccn_page->id));
      $ccnProfileCount = count($ccnProfileViews);

      // $userAvatar = $OUTPUT->user_picture($userData, array('size' => 150, 'class' => 'img-fluid'));
      // $userAvatar = new moodle_url('/user/pix.php/'.$USER->id.'/f1.jpg');
      // $userAvatar = '<img src="'.$userAvatar.'" alt="'.$userFirst.' '. $userLast.'" height="150" width="150" />';
      $hiddenFields = explode(',',$CFG->hiddenuserfields);
      $return .= '
      <section class="our-team">
		    <div class="">';
          // if($userAvatar && $PAGE->theme->settings->user_profile_layout != 1){ // EdumyFront ONLY
          //   $return .='
          //   <div id="ccn_instructor_personal_infor">
          //     <div class="instructor_personal_infor">
          //       <div class="instructor_thumb text-center">'.$userAvatar.'</div>
          //     </div>
          //   </div>';
          // }
          $return .='
          <div class="row">
            <div class="'.$ccn_col_main.'">
              <div class="row">
                <div class="col-lg-12">';
                if(!in_array('description', $hiddenFields) && $userDescription && $PAGE->theme->settings->user_profile_layout != 1){ // EdumyFront
                $return .='
                  <div class="cs_row_two">
                    <div class="'.$ccn_col_main_block.' cs_overview ">
                      <h4>'.$userFirst.' '.$userLast.'</h4>
                      '.$userDescription.'
                    </div>
                  </div>';
                } elseif(!in_array('description', $hiddenFields) && $PAGE->theme->settings->user_profile_layout == 1){ //Edumy Dash even without userDescription present
                  $return .='
                  <div class="cs_row_two mb30">
                    <div class="'.$ccn_col_main_block.' cs_overview ">
                    <div class="row">
                    <div class="col-xs-12 col-md-10">
                      <h4>'.$userFirst.' '.$userLast.'</h4>
                      '.$userDescription.'
                      </div>
                      <div class="col-xs-12 col-md-2">
                        <div class="instructor_personal_infor mb0">
                          <div class="instructor_thumb text-center">'.$userAvatar.'</div>
                        </div>
                      </div>
                      </div>
                    </div>
                  </div>';
                }



                $return .='
                <div class="cs_row_three profile-acordian-accordion-wrapper">
                  <div class="--course_content">';
                    $return .= $this->show_user_details($userData, $user_details);

                    if(is_siteadmin()) {
                        $categories = $tree->categories;
                        foreach ($categories as $category) {
                            $return .= $this->render($category);
                        }
                    }
                    $return .='
                  </div>
                </div>';

                $return .='
              </div>
            </div>
          </div>';

        // $return .= $this->show_user_stats($ccnUser);

      $return .='
			</div>
		</div>
	</section>';

      return $return;
  }

  /**
   * Render a category.
   *
   * @param category $category
   *
   * @return string
   */
  public function render_category(category $category) {
      $classes = $category->classes;
      $return = '<div class="details '.$classes.'">
                  <div id="accordion" class="panel-group cc_tab">
                    <div class="panel bg-white rounded shadow-sm p-0 mb-4">';
      $return .= '    <div class="panel-heading accordion-bg">
                        <h4 class="panel-title">
                          <a href="#panel-'.$category->name.'" class="accordion-toggle link" data-toggle="collapse" data-parent="#accordion">'.$category->title.'</a>
                        </h4>
                      </div>';
      $nodes = $category->nodes;
      if (empty($nodes)) {
          // No nodes, nothing to render.
          return '';
      }
      $return .= '<div id="panel-'.$category->name.'" class="panel-collapse collapse py-0 px-4">
                    <div class="panel-body">
                      <div class="my_resume_eduarea">';
      foreach ($nodes as $node) {
          if(!($node->content)) {
            $return .= '<div class="content style-link"><div class="circle"></div><h4 class="edu_stats edu_stats_link">'.$this->render($node).'</h4></div>';
          } else {
            $return .= $this->render($node);
          }
      }
      $return .='</div></div></div>';
      $return .= '</div></div></div>';
      return $return;
  }

  /**
   * Render a node.
   *
   * @param node $node
   *
   * @return string
   */
  public function render_node(node $node) {
      $return = '';
      if (is_object($node->url)) {
          $header = \html_writer::link($node->url, $node->title);
      } else {
          $header = $node->title;
      }
      // $icon = $node->icon;
      // if (!empty($icon)) {
      //     $header .= $this->render($icon);
      // }
      $content = $node->content;
      $classes = $node->classes;
      if (!empty($content)) {
          if ($header) {
              // There is some content to display below this make this a header.
              $return = '<h4 class="edu_stats">'.$header.'</h4>';
              $return .= '<p class="edu_center">'.$content.'</p>';
          } else {
              $return = \html_writer::span($content);
          }
          if ($classes) {
              $return = '<div class="content"><div class="circle"></div>'.$return.'</div>';
          } else {
              $return = '<div class="content"><div class="circle"></div>'.$return.'</div>';
          }
      } else {
                $return = $header;
      }

      return $return;
  }

  public function show_user_stats($userrecord){
    global $DB, $PAGE;
    $output = '';

    if($PAGE->theme->settings->user_profile_layout != 1){ //Edumy Frontend
      $ccn_col_side = 'col-lg-4 col-xl-4 profile-stats-count-wrapper';
    } else { //Edumy Dashboard
      $ccn_col_side = 'col-lg-4 col-xl-4 profile-stats-count-wrapper';
    }
    $output .= '<div class="'.$ccn_col_side.'">';
    
    // Medals
    $certification_count = 13;
    $certification_label = "Medals";
    $output .= '<div class="stats_container border-0 shadow-sm rounded  text-center px-2 py-4 mt-0 mb-3">
                  <div class="stats_count font-weight-bold display-4">'.$certification_count.'</div>
                  <div class="stats_label">'.$certification_label.'</div>
                </div>';

    
    // Certifications
    $certification_count = 18;
    $certification_label = "Certificates";
    $output .= '<div class="stats_container border-0 shadow-sm rounded  text-center px-2 py-4 mt-0 mb-3">
                  <div class="stats_count font-weight-bold display-4">'.$certification_count.'</div>
                  <div class="stats_label">'.$certification_label.'</div>
                </div>';

    // Badges
    $certification_count = 5;
    $certification_label = "Badges";
    $output .= '<div class="stats_container border-0 shadow-sm rounded  text-center px-2 py-4 mt-0 mb-3">
                  <div class="stats_count font-weight-bold display-4">'.$certification_count.'</div>
                  <div class="stats_label">'.$certification_label.'</div>
                </div>';

    
    // Mooc Courses
    $certification_count = 20;
    $certification_label = "Courses";
    $output .= '<div class="stats_container border-0 shadow-sm rounded  text-center px-2 py-4 mt-0 mb-3">
                  <div class="stats_count font-weight-bold display-4">'.$certification_count.'</div>
                  <div class="stats_label">'.$certification_label.'</div>
                </div>';

    
    // Trainings
    $certification_count = 10;
    $certification_label = "Trainings";
    $output .= '<div class="stats_container border-0 shadow-sm rounded  text-center px-2 py-4 mt-0 mb-3">
                  <div class="stats_count font-weight-bold display-4">'.$certification_count.'</div>
                  <div class="stats_label">'.$certification_label.'</div>
                </div>';

    $output .= '</div>';
    return $output;
  }

  public function show_user_details($user_data, $user_details){
    global $DB, $OUTPUT, $CFG;

    $userAvatar = $OUTPUT->user_picture($user_data, array('size' => 150, 'class' => 'img-fluid rounded-circle'));

    $editlink = '';
    $systemcontext = context_system::instance();
    if(has_capability('moodle/user:update', $systemcontext)){
      // Added one class mr-4
      $editlink = '<a class="d-inline-block mr-4 p-1" title="'.get_string('edit').'" href="'.$CFG->wwwroot.'/user/editadvanced.php?id='.$user_data->id.'">'.get_string('edit').'</a>';
    } else {
        /* INTG Customisation Start : Added else block for editing user profile */
        $editlink = '<a class="d-inline-block mr-4 p-1" title="'.get_string('edit').'" href="'.$CFG->wwwroot.'/local/profile_management">'.get_string('edit').'</a>';
        /* INTG Customisation End */
    }
    
    $return = $content = '';
    $category = 'personal_details';
    $category_title = 'Personal details';

    $salutation = '';
    if(!empty($user_details->salutation)){
      $salutation = $user_details->salutation.' ';
    }
    $content .= '<div class="col-5 profile_label disabled mb-3">Name</div>
                      <div class="col-7 font-weight-bold">'.$salutation.fullname($user_data).'</div>';
    
    if(!empty($user_data->phone1)){
      $content .= '<div class="col-5 profile_label disabled mb-3">Mobile Number</div>
                      <div class="col-7 font-weight-bold">'.$user_data->phone1.'</div>';
    }
    
    $content .= '<div class="col-5 profile_label disabled mb-3">'.get_string('email').'</div>
                      <div class="col-7 font-weight-bold">'.$user_data->email.'</div>';

    if (!empty($user_data->idnumber)) {
        $useridnumber = $user_data->idnumber;
    } else {
        $useridnumber = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content .= '<div class="col-5 profile_label disabled mb-3">'.get_string('idnumber').'</div>
                  <div class="col-7 font-weight-bold">' . $useridnumber . '</div>';

    if (!empty($user_data->address)) {
        $useraddress = $user_data->address;
    } else {
        $useraddress = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }
    
    $content .= '<div class="col-5 profile_label disabled mb-3">'.get_string('address').'</div>
                  <div class="col-7 font-weight-bold">' . $useraddress . '</div>';

    if(!empty($user_data->skype) || !empty($user_data->icq) || !empty($user_data->yahoo) || !empty($user_data->aim) || !empty($user_data->msn) ){
      $social_links = '<ul class="list-unstyled h5">';
        if(!empty($user_data->skype)){
          $social_links .= '<li>'.get_string("skypeid").' - '.$user_data->skype.' </li>';
        }
        if(!empty($user_data->icq)){
          $social_links .= '<li>'.get_string("icqnumber").' - '.$user_data->icq.' </li>';
        }
        if(!empty($user_data->yahoo)){
          $social_links .= '<li>'.get_string("yahooid").' - '.$user_data->yahoo.' </li>';
        }
        if(!empty($user_data->aim)){
          $social_links .= '<li>'.get_string("aimid").' - '.$user_data->aim.' </li>';
        }
        if(!empty($user_data->msn)){
          $social_links .= '<li>'.get_string("msnid").' - '.$user_data->msn.' </li>';
        }
      $social_links .= '</ul>';
      $content .= '<div class="col-5 profile_label disabled mb-3">Social Media</div>
                        <div class="col-7">'.$social_links.'</div>';
    }

    $return .= '<div class="details">
                  <div id="accordion" class="panel-group cc_tab">
                    <div class="panel bg-white rounded shadow-sm p-0 mb-4">';

      $return .= '    <div class="panel-heading accordion-bg">
                        <h4 class="panel-title">
                          <a href="#panel-'.$category.'" class="accordion-toggle link" data-toggle="collapse" data-parent="#accordion">'.$category_title.'</a>
                        </h4>
                      </div>';
      // Removed class px-4
      $return .= '<div id="panel-'.$category.'" class="panel-collapse collapse py-0  show">
                    <div class="panel-body row">';
                    
           $return .= '<div class="col-12 col-md-3 text-center">'.$userAvatar.'</div>';
            
           $return .= '<div class="col-12 col-md-9">';
            $return .= '<div class="my_resume_eduarea">
                        <div class="content style-link">
                          <h4 class="row edu_stats edu_stats_link">'.$content.'</h4>
                        </div>
                      </div>';
          $return .='</div>';
          $return .= '<div class="col-12 text-right mb-3">'.$editlink.'</div>';

      $return .='</div>
              </div>';

      $return .= '</div>
                </div>
              </div>';

    // Professional details
    $category = 'professional_details';
    $category_title = 'Professional details';
    $user_school = $DB->get_record('local_schools', array('id' => $user_details->schoolid));

    if(!empty($user_details->department)){
      $user_departments = explode(',', $user_details->department);
      $userdepartments = array();
      foreach($user_departments as $user_department){
        $user_dept = $DB->get_field('local_departments', 'name', array('id' => $user_department));
        $userdepartments[] = $user_dept;
      }
      $user_depts = implode(', ', $userdepartments);
    }

    if(!empty($user_details->position)){
      $user_positions = explode(',', $user_details->position);
      $userpositions = array();
      foreach($user_positions as $user_position){
        $user_school_pos = $DB->get_field('local_school_positions', 'shortname', array('id' => $user_position));
        $userpositions[] = $user_school_pos;
      }
      $user_school_positions = implode(', ', $userpositions);
    }

    if(!empty($user_details->subject)){
      $user_subjects = explode(',', $user_details->subject);
      $usersubjects = array();
      foreach($user_subjects as $user_subject){
        $user_subs = $DB->get_field('local_subjects', 'name', array('id' => $user_subject));
        $usersubjects[] = $user_subs;
      }
      $user_subjects = implode(', ', $usersubjects);
    }

    if (!empty($user_school->code)) {
        $userschoolcode = $user_school->code;
    } else {
        $userschoolcode = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }
    $content = '';
    $content .= '<div class="col-5 profile_label disabled mb-3">School ID</div>
                  <div class="col-7 font-weight-bold">' . $userschoolcode . '</div>';

    if (!empty($user_school->name)) {
        $userschoolname = $user_school->name;
    } else {
        $userschoolname = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content .= '<div class="col-5 profile_label disabled mb-3">School name</div>
                  <div class="col-7 font-weight-bold">' . $userschoolname . '</div>';
    
    if (!empty($user_depts)) {
        $userdepartments = $user_depts;
    } else {
        $userdepartments = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content .= '<div class="col-5 profile_label disabled mb-3">Department</div>
                  <div class="col-7 font-weight-bold">' . $userdepartments . '</div>';

    if (!empty($user_school_positions)) {
        $userschoolposition = $user_school_positions;
    } else {
        $userschoolposition = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content .= '<div class="col-5 profile_label disabled mb-3">School Position</div>
                  <div class="col-7 font-weight-bold">' . $userschoolposition . '</div>';
    
    if (!empty($user_subjects)) {
        $usersubjects = $user_subjects;
    } else {
        $usersubjects = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content .= '<div class="col-5 profile_label disabled mb-3">Subjects</div>
                  <div class="col-7 font-weight-bold">' . $usersubjects . '</div>';
    if (!empty($user_school->description)) {
        $userschooldescription = $user_school->description;
    } else {
        $userschooldescription = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content .= '<div class="col-5 profile_label disabled mb-3">School address</div>
                  <div class="col-7 font-weight-bold">' . $userschooldescription . '</div>';
    if (!empty($user_details->caste)) {
        $caste = $DB->get_record('local_castes', array('id' => $user_details->caste));
        $usercaste = $caste->name;
    } else {
        $usercaste = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content .= '<div class="col-5 profile_label disabled mb-3">Caste</div>
                  <div class="col-7 font-weight-bold">' . $usercaste . '</div>';

    if (!empty($user_details->jobtype)) {
        $userjobtype = ucfirst($user_details->jobtype);
    } else {
        $userjobtype = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content .= '<div class="col-5 profile_label disabled mb-3">Job Type</div>
                  <div class="col-7 font-weight-bold">' . $userjobtype . '</div>';

    if (!empty($user_details->doj)) {
        $userdateofjoining = date('Y-m-d', $user_details->doj);
    } else {
        $userdateofjoining = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content .= '<div class="col-5 profile_label disabled mb-3">Date Of Joining </div>
                  <div class="col-7 font-weight-bold">' . $userdateofjoining . '</div>';

    $return .= '<div class="details">
                  <div id="accordion" class="panel-group cc_tab">
                    <div class="panel bg-white rounded shadow-sm p-0 mb-4">';

      $return .= '    <div class="panel-heading accordion-bg">
                        <h4 class="panel-title">
                          <a href="#panel-'.$category.'" class="accordion-toggle link" data-toggle="collapse" data-parent="#accordion">'.$category_title.'</a>
                        </h4>
                      </div>';
      
      $return .= '<div id="panel-'.$category.'" class="panel-collapse collapse py-0 px-4">
                    <div class="panel-body">
                      <div class="my_resume_eduarea">';
            $return .= '<div class="content style-link">
                          <h4 class="row edu_stats edu_stats_link">'.$content.'</h4>
                        </div>';
      $return .='</div></div></div>';

      $return .= '</div></div></div>';

    // Accomplishment details
    $category = 'accomplishment_details';
    $category_title = 'Accomplishment details';

    if (!empty($user_details->grade)) {
        $grade = $DB->get_record('local_grades', array('id' => $user_details->grade));
        $usergrade = $grade->name;
    } else {
        $usergrade = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    if (!empty($user_details->post)) {
        $post = $DB->get_record('local_posts', array('id' => $user_details->post));
        $userpost = $post->name;
    } else {
        $userpost = '<div class="not-available">' . get_string('notavailable', 'local_batching') . '</div>';
    }

    $content = '';
    $content .= '<div class="col-5 profile_label disabled mb-3">Grade</div>
                      <div class="col-7 font-weight-bold">' . $usergrade . '</div>';
    
    $content .= '<div class="col-5 profile_label disabled mb-3">Post</div>
                      <div class="col-7 font-weight-bold">' . $userpost . '</div>';
    
    $return .= '<div class="details">
                  <div id="accordion" class="panel-group cc_tab">
                    <div class="panel bg-white rounded shadow-sm p-0 mb-4">';

      $return .= '    <div class="panel-heading accordion-bg">
                        <h4 class="panel-title">
                          <a href="#panel-'.$category.'" class="accordion-toggle link" data-toggle="collapse" data-parent="#accordion">'.$category_title.'</a>
                        </h4>
                      </div>';
      
      $return .= '<div id="panel-'.$category.'" class="panel-collapse collapse py-0 px-4">
                    <div class="panel-body">
                      <div class="my_resume_eduarea">';
            $return .= '<div class="content style-link">
                          <h4 class="row edu_stats edu_stats_link">'.$content.'</h4>
                        </div>';
      $return .='</div></div></div>';

      $return .= '</div></div></div>';

    return $return;
  }

}
