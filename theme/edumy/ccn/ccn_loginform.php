<?php
/*
@ccnRef: @login_form
*/

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/authlib.php');
include_once($CFG->dirroot . '/theme/edumy/ccn/page_handler/ccn_page_handler.php');
/* INTG Customization Start : Include file to get moodle captcha core function  */
require_once($CFG->libdir . '/recaptchalib_v2.php');
/* INTG Customization End  */

if($SESSION){
  $ccnPageHandler = new ccnPageHandler();
  $ccnGetPageUrl = $ccnPageHandler->ccnGetPageUrl();
  if(!strpos($ccnGetPageUrl->path, 'login')){
    $SESSION->wantsurl = (new moodle_url($this->page->url))->out(false);
  }
}
/* INTG Customization Start : Get setting value of self registration  */
$enableselfregistration = get_config('local_self_registration', "enable_self_registration");
/* INTG Customization End  */

if (signup_is_enabled()) {
  $signup = $CFG->wwwroot . '/login/signup.php';
}
$forgot = $CFG->wwwroot . '/login/forgot_password.php';
$username = get_moodle_cookie();
$_ccnlogin = '';
if (!isloggedin() or isguestuser()) {   // Show the block
  if (empty($CFG->authloginviaemail)) {
    $strusername = get_string('username');
  } else {
    $strusername = get_string('usernameemail');
  }
  $_ccnlogin .= "\n".'<form class="loginform" id="login" method="post" action="'.get_login_url().'">';

  $_ccnlogin .= '<div class="form-group">';
  $_ccnlogin .= '<input type="text" name="username" placeholder="'.get_string('username', 'theme_edumy').'" id="login_username" ';
  $_ccnlogin .= ' class="form-control" value="'.s($username).'" autocomplete="username"/></div>';

  $_ccnlogin .= '<div class="form-group">';

  $_ccnlogin .= '<input type="password" name="password" id="login_password" placeholder="'.get_string('password', 'theme_edumy').'" ';
  $_ccnlogin .= ' class="form-control" value="" autocomplete="current-password"/>';
  $_ccnlogin .= '</div>';

  /* INTG Customization Start : Rendering captcha */
    if ($_SERVER['SCRIPT_NAME'] != '/local/self_registration/index.php' &&
        $_SERVER['SCRIPT_NAME'] != '/login/index.php'
    ) {

        $_ccnlogin .= '<div class="form-group">';
        $_ccnlogin .= recaptcha_get_challenge_html(RECAPTCHA_API_URL, $CFG->recaptchapublickey, null);
        $_ccnlogin .= '</div>';
    }
  /* INTG Customization End */

  if (isset($CFG->rememberusername) and $CFG->rememberusername == 2) {
    $checked = $username ? 'checked="checked"' : '';

    $_ccnlogin .='
      <div class="form-group custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" name="rememberusername" id="rememberusername">
        <label class="custom-control-label" for="rememberusername">'.get_string('rememberusername', 'admin').'</label>
        <a class="tdu btn-fpswd float-right" href="'.$forgot.'">'.get_string('forgotaccount').'</a>
      </div>';
  }

  // INTG Customization added mb-4
  $_ccnlogin .= '<button type="submit" class="btn btn-log btn-block btn-thm2 mb-4">'.get_string('login').'</button>';
  $_ccnlogin .= '<input type="hidden" name="logintoken" value="'.s(\core\session\manager::get_login_token()).'" />';

  $_ccnlogin .= "</form>\n";


    // INTG Customization added span tag and btn btn-signup btn-block p-3 rounded classes added in a tag

    /* INTG Customisation Start : Check self registration is enabled or not */
    if ($enableselfregistration) {
        $_ccnlogin .='
          <div class="form-group custom-control pl-0">
            <span class="text-center d-block">Do not have an account?</span>
            <a class="tdu btn-fpswd btn btn-signup btn-block p-3 rounded" href="'.$CFG->wwwroot . '/local/self_registration'.'">Sign Up</a>
          </div>';
    }
    /* INTG Customisation Start */

  $authsequence = get_enabled_auth_plugins(true); // Get all auths, in sequence.
  $potentialidps = array();
  foreach ($authsequence as $authname) {
      $authplugin = get_auth_plugin($authname);
      $potentialidps = array_merge($potentialidps, $authplugin->loginpage_idp_list($this->page->url->out(false)));
  }

  if (!empty($potentialidps)) {
      $_ccnlogin .= '<div class="potentialidps">';
      $_ccnlogin .= '<h6>' . get_string('potentialidps', 'auth') . '</h6>';
      $_ccnlogin .= '<div class="potentialidplist">';
      foreach ($potentialidps as $idp) {
          $_ccnlogin .= '<div class="potentialidp">';
          $_ccnlogin .= '<a class="btn btn-secondary btn-block" ';
          $_ccnlogin .= 'href="' . $idp['url']->out() . '" title="' . s($idp['name']) . '">';
          if (!empty($idp['iconurl'])) {
              $_ccnlogin .= '<img src="' . s($idp['iconurl']) . '" width="24" height="24" class="mr-1"/>';
          }
          $_ccnlogin .= s($idp['name']) . '</a></div>';
      }
      $_ccnlogin .= '</div>';
      $_ccnlogin .= '</div>';
  }
}
/* Login */
