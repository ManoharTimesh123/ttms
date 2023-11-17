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
 * Bulk user registration script from a comma separated file
 *
 * @package local
 * @subpackage user_management
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
global $CFG, $DB;
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/local/user_management/lib.php');
require_once($CFG->dirroot.'/admin/tool/uploaduser/locallib.php');
require_once($CFG->dirroot.'/local/user_management/user_form.php');
require_once($CFG->dirroot.'/admin/tool/uploaduser/classes/local/field_value_validators.php');
use tool_uploaduser\local\field_value_validators;

$iid = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

core_php_time_limit::raise(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);

$title = get_string('uploadusers', 'local_user_management');
$PAGE->set_pagelayout('admin');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url(new moodle_url('/local/user_management/user_upload.php', array()));
$PAGE->requires->jquery();
$PAGE->requires->js('/local/user_management/js/jquery.dataTables.min.js', true);
$PAGE->set_heading($title);
$PAGE->set_title($title);

require_capability('moodle/site:uploadusers', context_system::instance());

$struserrenamed = get_string('userrenamed', 'tool_uploaduser');
$strusernotrenamedexists = get_string('usernotrenamedexists', 'error');
$strusernotrenamedmissing = get_string('usernotrenamedmissing', 'error');
$strusernotrenamedoff = get_string('usernotrenamedoff', 'error');
$strusernotrenamedadmin = get_string('usernotrenamedadmin', 'error');

$struserupdated = get_string('useraccountupdated', 'tool_uploaduser');
$strusernotupdated = get_string('usernotupdatederror', 'error');
$strusernotupdatednotexists = get_string('usernotupdatednotexists', 'error');
$strusernotupdatedadmin = get_string('usernotupdatedadmin', 'error');

$struseruptodate = get_string('useraccountuptodate', 'tool_uploaduser');

$struseradded = get_string('newuser');
$strusernotadded = get_string('usernotaddedregistered', 'error');
$strusernotaddederror = get_string('usernotaddederror', 'error');

$struserdeleted = get_string('userdeleted', 'tool_uploaduser');
$strusernotdeletederror = get_string('usernotdeletederror', 'error');
$strusernotdeletedmissing = get_string('usernotdeletedmissing', 'error');
$strusernotdeletedoff = get_string('usernotdeletedoff', 'error');
$strusernotdeletedadmin = get_string('usernotdeletedadmin', 'error');
$strcannotassignrole = get_string('cannotassignrole', 'error');

$struserauthunsupported = get_string('userauthunsupported', 'error');
$stremailduplicate = get_string('useremailduplicate', 'error');

$strinvalidpasswordpolicy = get_string('invalidpasswordpolicy', 'error');
$errorstr = get_string('error');

$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);

$returnurl = new moodle_url('/local/user_management/user_upload.php', array());

$today = time();
$today = make_timestamp(customdateformat('YEAR', $today), customdateformat('MONTH', $today), customdateformat('DAY', $today), 0, 0, 0);

// Array of all valid fields for validation.
$stdfields = array('id', 'username', 'email', 'emailstop',
        'city', 'country', 'lang', 'timezone', 'mailformat',
        'maildisplay', 'maildigest', 'htmleditor', 'autosubscribe',
        'institution', 'department', 'idnumber', 'skype',
        'msn', 'aim', 'yahoo', 'icq', 'phone1', 'phone2', 'address',
        'url', 'description', 'descriptionformat', 'password',
         'auth',    // Watch out when changing auth type or using external auth plugins!.
         'oldusername', // Use when renaming users - this is the original username.
         'suspended',  // 1 means suspend user account, 0 means activate user account, nothing means keep as is for existing users.
         'theme',    // Define a theme for user when 'allowuserthemes' is enabled.
         'deleted',    // 1 means delete user.
         'mnethostid', // Can not be used for adding, updating or deleting of users - only for enrolments, groups, cohorts and suspending.
         'interests', 'firstaccess', 'lastaccess',
    );
// Include all name fields.
$stdfields = array_merge($stdfields, get_all_user_name_fields());

$prefields = array();
$prefields = array('custom_salutation', 'custom_type', 'custom_dob', 'custom_gender', 'custom_department', 'custom_subjects', 'custom_position', 'custom_uid', 'custom_school', 'custom_doj', 'custom_jobtype', 'custom_grade', 'custom_caste', 'custom_post');
if ($proffields = $DB->get_records('user_info_field')) {
    foreach ($proffields as $key => $proffield) {
        $profilefieldname = 'profile_field_'.$proffield->shortname;
        $prefields[] = $profilefieldname;
        // Re-index $proffields with key as shortname. This will be.
        // Used while checking if profile data is key and needs to be converted (eg. menu profile field).
        $proffields[$profilefieldname] = $proffield;
        unset($proffields[$key]);
    }
}

if (empty($iid)) {
    $mform1 = new local_user_uploaduser_form(null, array());
    
    $setdata = new stdClass();
    $mform1->set_data($setdata);

    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploaduser');
        $cir = new csv_import_reader($iid, 'uploaduser');

        $content = $mform1->get_file_content('userfile');

        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        $csvloaderror = $cir->get_error();
        unset($content);

        if (!is_null($csvloaderror)) {
            print_error('csvloaderror', '', $returnurl, $csvloaderror);
        }
        // Test if columns ok.
        $filecolumns = uu_validate_user_upload_columns($cir, $stdfields, $prefields, $returnurl);
        // Continue to form2.

    } else {
        echo $OUTPUT->header();

        echo $OUTPUT->heading_with_help(get_string('uploadusers', 'tool_uploaduser'), 'uploadusers', 'tool_uploaduser');
        echo '<hr/>';
        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {
    $cir = new csv_import_reader($iid, 'uploaduser');
    $filecolumns = uu_validate_user_upload_columns($cir, $stdfields, $prefields, $returnurl);
}

$mform2 = new admin_uploaduser_form2(null, array('columns' => $filecolumns, 'data' => array('iid' => $iid, 'previewrows' => $previewrows)));
// If a file has been uploaded, then process it.
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);

} else if ($formdata = $mform2->get_data()) {
    // Print the header.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadusersresult', 'tool_uploaduser'));
    $optype = $formdata->uutype;

    $updatetype = isset($formdata->uuupdatetype) ? $formdata->uuupdatetype : 0;
    $createpasswords = (!empty($formdata->uupasswordnew) && $optype != UU_USER_UPDATE);
    $updatepasswords = (!empty($formdata->uupasswordold)  && $optype != UU_USER_ADDNEW && $optype != UU_USER_ADDINC && ($updatetype == UU_UPDATE_FILEOVERRIDE || $updatetype == UU_UPDATE_ALLOVERRIDE));
    $allowrenames = (!empty($formdata->uuallowrenames) && $optype != UU_USER_ADDNEW && $optype != UU_USER_ADDINC);
    $allowdeletes = (!empty($formdata->uuallowdeletes) && $optype != UU_USER_ADDNEW && $optype != UU_USER_ADDINC);
    $allowsuspends = (!empty($formdata->uuallowsuspends));
    $bulk = $formdata->uubulk;
    $noemailduplicates = empty($CFG->allowaccountssameemail) ? 1 : $formdata->uunoemailduplicates;
    $standardusernames = $formdata->uustandardusernames;
    $resetpasswords = isset($formdata->uuforcepasswordchange) ? $formdata->uuforcepasswordchange : UU_PWRESET_NONE;

    // Verification moved to two places: after upload and into form2.
    $usersnew = 0;
    $usersupdated = 0;
    $usersuptodate = 0; // Not printed yet anywhere.
    $userserrors = 0;
    $deletes = 0;
    $deleteerrors = 0;
    $renames = 0;
    $renameerrors = 0;
    $usersskipped = 0;
    $weakpasswords = 0;

    // Caches.
    $ccache = array(); // Course cache - do not fetch all courses here, we  will not probably use them all anyway!.
    $cohorts = array();
    $rolecache = uu_allowed_roles_cache(); // Course roles lookup cache.
    $sysrolecache = uu_allowed_sysroles_cache(); // System roles lookup cache.
    $manualcache = array(); // Cache of used manual enrol plugins in each course.
    $supportedauths = uu_supported_auths(); // Officially supported plugins that are enabled.

    // We use only manual enrol plugin here, if it is disabled no enrol is done.
    if (enrol_is_enabled('manual')) {
        $manual = enrol_get_plugin('manual');
    } else {
        $manual = null;
    }

    // Clear bulk selection.
    if ($bulk) {
        $SESSION->bulk_users = array();
    }

    // Init csv import helper.
    $cir->init();
    $linenum = 1; // Column header is first line.

    // Init upload progress tracker.
    $upt = new uu_progress_tracker();
    $upt->start(); // Start table.
    $validation = array();
    while ($line = $cir->next()) {
        $upt->flush();
        $linenum++;

        $upt->track('line', $linenum);

        $user = new stdClass();

        // Add fields to user object.
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                // This should not happen.
                continue;
            }
            $key = $filecolumns[$keynum];
            if (strpos($key, 'profile_field_') === 0) {
                // NOTE: bloody mega hack alert!!.
                if (isset($USER->$key) && is_array($USER->$key)) {
                    // This must be some hacky field that is abusing arrays to store content and format.
                    $user->$key = array();
                    $user->{$key['text']} = $value;
                    $user->{$key['format']} = FORMAT_MOODLE;
                } else {
                    $user->$key = trim($value);
                }
            } else {
                $user->$key = trim($value);
            }

            if (in_array($key, $upt->columns)) {
                // Default value in progress tracking table, can be changed later.
                $upt->track($key, s($value), 'normal');
            }
        }
        
        // Custom fields setting to their specific array or ids.
        $typearray = array(0 => 'Internal', 1 => 'External');
        $user->custom_type = array_search($user->custom_type, $typearray);
        
        // Custom Set correct DOB.
        $user->custom_dob = strtotime($user->custom_dob);
        
        // Custom Set correct gender.
        $genderarray = array(null => 'Select', "male" => 'Male', "female" => 'Female', "others" => 'Others');
        $user->custom_gender = array_search($user->custom_gender, $genderarray);

        // Custom Set correct department.
        $userdepartments = explode(',', $user->custom_department);
        $sql = "SELECT id, code
			 FROM {local_departments}";
        $deptslist = $DB->get_records_sql_menu($sql);
        $depnames = array();
        foreach ($userdepartments as $userdepartment) {
            $userdepartment = trim($userdepartment);
            $depnames[] = array_search($userdepartment, $deptslist);
        }
        $user->custom_department = $depnames;
        
        // Custom Set correct subjects.
        $usersubjects = explode(',', $user->custom_subjects);
        $sql = "SELECT id, code 
			FROM {local_subjects} ";
        $subtslist = $DB->get_records_sql_menu($sql);
        $subnames = array();
        foreach ($usersubjects as $usersubject) {
            $usersubject = trim($usersubject);
            $subnames[] = array_search($usersubject, $subtslist);
        }
        $user->custom_subjects = $subnames;

        // Custom set correct grade.
        $gradelist = $DB->get_record('local_grades', array('name' => strtoupper($user->custom_grade)));
        if ($gradelist) {
            $user->custom_grade = $gradelist->id;
        }

        // Custom set correct caste.
        $castelist = $DB->get_record('local_castes', array('name' => strtoupper($user->custom_caste)));
        if ($castelist) {
            $user->custom_caste = $castelist->id;
        }

        // Custom set correct post.
        $postlist = $DB->get_record('local_posts', array('name' => strtoupper($user->custom_post)));
        if ($postlist) {
            $user->custom_post = $postlist->id;
        }

        // Custom Set correct DOJ.
        $user->custom_doj = strtotime($user->custom_doj);

        // Custom Set correct jobtype.
        $user->custom_jobtype = trim($user->custom_jobtype);

        // Custom Set correct school positions.
        $userpositions = explode(',', $user->custom_position);
        $sql = "SELECT id, shortname 
			FROM {local_school_positions} ";
        $poslist = $DB->get_records_sql_menu($sql);
        $posnames = array();
        foreach ($userpositions as $userposition) {
            $userposition = trim($userposition);
            $posnames[] = array_search($userposition, $poslist);
        }
        $user->custom_position = $posnames;
        
        // Custom Set correct school.
        $userschool = $user->custom_school;
        $sql = "SELECT id, code 
			FROM {local_schools} ";
        $schoolslist = $DB->get_records_sql_menu($sql);
        $userschool = trim($userschool);
        $schnames = array_search($userschool, $schoolslist);
        $user->custom_school = $schnames;
        // Custom end of custom data setting.

        $user->username = strtolower($user->username);

        if (!isset($user->username)) {
            // Prevent warnings below.
            $user->username = '';
        }

        if ($optype == UU_USER_ADDNEW || $optype == UU_USER_ADDINC) {
            // User creation is a special case - the username may be constructed from templates using firstname and lastname.
            // Better never try this in mixed update types.
            $error = false;
            if (!isset($user->firstname) || $user->firstname === '') {
                $upt->track('status', get_string('missingfield', 'error', 'firstname'), 'error');
                $upt->track('firstname', $errorstr, 'error');
                $error = true;
            }
            if ($error) {
                $userserrors++;
                continue;
            }
            // We require username too - we might use template for it though.
            if (empty($user->username) && !empty($formdata->username)) {
                $user->username = uu_process_template($formdata->username, $user);
                $upt->track('username', s($user->username));
            }
        }

        // Normalize username.
        $originalusername = $user->username;
        if ($standardusernames) {
            $user->username = core_user::clean_field($user->username, 'username');
        }

        // Make sure we really have username.
        if (empty($user->username)) {
            $upt->track('status', get_string('missingfield', 'error', 'username'), 'error');
            $upt->track('username', $errorstr, 'error');
            $userserrors++;
            continue;
        } else if ($user->username === 'guest') {
            $upt->track('status', get_string('guestnoeditprofileother', 'error'), 'error');
            $userserrors++;
            continue;
        }

        if ($user->username !== core_user::clean_field($user->username, 'username')) {
            $upt->track('status', get_string('invalidusername', 'error', 'username'), 'error');
            $upt->track('username', $errorstr, 'error');
            $userserrors++;
        }

        if (empty($user->mnethostid)) {
            $user->mnethostid = $CFG->mnet_localhost_id;
        }

        if ($existinguser = $DB->get_record('user', array('username' => $user->username, 'mnethostid' => $user->mnethostid))) {
            $upt->track('id', $existinguser->id, 'normal', false);
        }

        if ($user->mnethostid == $CFG->mnet_localhost_id) {
            $remoteuser = false;

            // Find out if username incrementing required.
            if ($existinguser && $optype == UU_USER_ADDINC) {
                $user->username = uu_increment_username($user->username);
                $existinguser = false;
            }

        } else {
            if (!$existinguser || $optype == UU_USER_ADDINC) {
                $upt->track('status', get_string('errormnetadd', 'tool_uploaduser'), 'error');
                $userserrors++;
                continue;
            }

            $remoteuser = true;

            // Make sure there are no changes of existing fields except the suspended status.
            foreach ((array)$existinguser as $k => $v) {
                if ($k === 'suspended') {
                    continue;
                }
                if (property_exists($user, $k)) {
                    $user->$k = $v;
                }
                if (in_array($k, $upt->columns)) {
                    if ($k === 'password' || $k === 'oldusername' || $k === 'deleted') {
                        $upt->track($k, '', 'normal', false);
                    } else {
                        $upt->track($k, s($v), 'normal', false);
                    }
                }
            }
            unset($user->oldusername);
            unset($user->password);
            $user->auth = $existinguser->auth;
        }

        // Notify about nay username changes.
        if ($originalusername !== $user->username) {
            $upt->track('username', '', 'normal', false); // Clear previous.
            $upt->track('username', s($originalusername).'-->'.s($user->username), 'info');
        } else {
            $upt->track('username', s($user->username), 'normal', false);
        }

        // Verify if the theme is valid and allowed to be set.
        if (isset($user->theme)) {
            list($status, $message) = field_value_validators::validate_theme($user->theme);
            if ($status !== 'normal' && !empty($message)) {
                $upt->track('status', $message, $status);
                // Unset the theme when validation fails.
                unset($user->theme);
            }
        }

        // Add default values for remaining fields.
        $formdefaults = array();
        if (!$existinguser || ($updatetype != UU_UPDATE_FILEOVERRIDE && $updatetype != UU_UPDATE_NOCHANGES)) {
            foreach ($stdfields as $field) {
                if (isset($user->$field)) {
                    continue;
                }
                // All validation moved to form2.
                if (isset($formdata->$field)) {
                    // Process templates.
                    $user->$field = uu_process_template($formdata->$field, $user);
                    $formdefaults[$field] = true;
                    if (in_array($field, $upt->columns)) {
                        $upt->track($field, s($user->$field), 'normal');
                    }
                }
            }
            foreach ($prefields as $field) {
                if (isset($user->$field)) {
                    continue;
                }
                if (isset($formdata->$field)) {
                    // Process templates.
                    $user->$field = uu_process_template($formdata->$field, $user);

                    // Form contains key and later code expects value.
                    // Convert key to value for required profile fields.
                    require_once($CFG->dirroot.'/user/profile/field/'.$proffields[$field]->datatype.'/field.class.php');
                    $profilefieldclass = 'profile_field_'.$proffields[$field]->datatype;
                    $profilefield = new $profilefieldclass($proffields[$field]->id);
                    if (method_exists($profilefield, 'convert_external_data')) {
                        $user->$field = $profilefield->edit_save_data_preprocess($user->$field, null);
                    }

                    $formdefaults[$field] = true;
                }
            }
        }

        // Delete user.
        if (!empty($user->deleted)) {
            if (!$allowdeletes || $remoteuser) {
                $usersskipped++;
                $upt->track('status', $strusernotdeletedoff, 'warning');
                continue;
            }
            if ($existinguser) {
                if (is_siteadmin($existinguser->id)) {
                    $upt->track('status', $strusernotdeletedadmin, 'error');
                    $deleteerrors++;
                    continue;
                }
                if (delete_user($existinguser)) {
                    $upt->track('status', $struserdeleted);
                    $deletes++;
                } else {
                    $upt->track('status', $strusernotdeletederror, 'error');
                    $deleteerrors++;
                }
            } else {
                $upt->track('status', $strusernotdeletedmissing, 'error');
                $deleteerrors++;
            }
            continue;
        }
        // We do not need the deleted flag anymore.
        unset($user->deleted);

        // Renaming requested?
        if (!empty($user->oldusername) ) {
            if (!$allowrenames) {
                $usersskipped++;
                $upt->track('status', $strusernotrenamedoff, 'warning');
                continue;
            }

            if ($existinguser) {
                $upt->track('status', $strusernotrenamedexists, 'error');
                $renameerrors++;
                continue;
            }

            if ($user->username === 'guest') {
                $upt->track('status', get_string('guestnoeditprofileother', 'error'), 'error');
                $renameerrors++;
                continue;
            }

            if ($standardusernames) {
                $oldusername = core_user::clean_field($user->oldusername, 'username');
            } else {
                $oldusername = $user->oldusername;
            }

            // No guessing when looking for old username, it must be exact match.
            if ($olduser = $DB->get_record('user', array('username' => $oldusername, 'mnethostid' => $CFG->mnet_localhost_id))) {
                $upt->track('id', $olduser->id, 'normal', false);
                if (is_siteadmin($olduser->id)) {
                    $upt->track('status', $strusernotrenamedadmin, 'error');
                    $renameerrors++;
                    continue;
                }
                $DB->set_field('user', 'username', $user->username, array('id' => $olduser->id));
                $upt->track('username', '', 'normal', false); // Clear previous.
                $upt->track('username', s($oldusername).'-->'.s($user->username), 'info');
                $upt->track('status', $struserrenamed);
                $renames++;
            } else {
                $upt->track('status', $strusernotrenamedmissing, 'error');
                $renameerrors++;
                continue;
            }
            $existinguser = $olduser;
            $existinguser->username = $user->username;
        }

        // Can we process with update or insert?
        $skip = false;
        switch ($optype) {
            case UU_USER_ADDNEW:
                if ($existinguser) {
                    $usersskipped++;
                    $upt->track('status', $strusernotadded, 'warning');
                    $skip = true;
                }
                break;

            case UU_USER_ADDINC:
                if ($existinguser) {
                    // This should not happen!.
                    $upt->track('status', $strusernotaddederror, 'error');
                    $userserrors++;
                    $skip = true;
                }
                break;

            case UU_USER_ADD_UPDATE:
                break;

            case UU_USER_UPDATE:
                if (!$existinguser) {
                    $usersskipped++;
                    $upt->track('status', $strusernotupdatednotexists, 'warning');
                    $skip = true;
                }
                break;

            default:
                // Unknown type.
                $skip = true;
        }

        if ($skip) {
            continue;
        }

        if ($existinguser) {
            $user->id = $existinguser->id;

            $upt->track('username', html_writer::link(new moodle_url('/user/profile.php', array('id' => $existinguser->id)), s($existinguser->username)), 'normal', false);
            $upt->track('suspended', $stryesnooptions[$existinguser->suspended] , 'normal', false);
            $upt->track('auth', $existinguser->auth, 'normal', false);

            if (is_siteadmin($user->id)) {
                $upt->track('status', $strusernotupdatedadmin, 'error');
                $userserrors++;
                continue;
            }

            $existinguser->timemodified = time();
            // Do NOT mess with timecreated or firstaccess here!.
            // Load existing profile data.
            $doupdate = false;
            $dologout = false;

            if ($updatetype != UU_UPDATE_NOCHANGES && !$remoteuser) {
                if (!empty($user->auth) && $user->auth !== $existinguser->auth) {
                    $upt->track('auth', s($existinguser->auth).'-->'.s($user->auth), 'info', false);
                    $existinguser->auth = $user->auth;
                    if (!isset($supportedauths[$user->auth])) {
                        $upt->track('auth', $struserauthunsupported, 'warning');
                    }
                    $doupdate = true;
                    if ($existinguser->auth === 'nologin') {
                        $dologout = true;
                    }
                }
                $allcolumns = array_merge($stdfields, $prefields);

                foreach ($allcolumns as $column) {
                    if ($column === 'username' || $column === 'password' || $column === 'auth' || $column === 'suspended') {
                        // These can not be changed here.
                        continue;
                    }
                    if (!property_exists($user, $column) || !property_exists($existinguser, $column)) {
                        continue;
                    }
                    if ($updatetype == UU_UPDATE_MISSING) {
                        if (!is_null($existinguser->$column) && $existinguser->$column !== '') {
                            continue;
                        }
                    } else if ($updatetype == UU_UPDATE_ALLOVERRIDE) {
                        // We override everything.

                    } else if ($updatetype == UU_UPDATE_FILEOVERRIDE) {
                        if (!empty($formdefaults[$column])) {
                            // Do not override with form defaults.
                            continue;
                        }
                    }
                    if ($existinguser->$column !== $user->$column) {
                        if ($column === 'email') {
                            $select = $DB->sql_like('email', ':email', false, true, false, '|');
                            $params = array('email' => $DB->sql_like_escape($user->email, '|'));
                            if ($DB->record_exists_select('user', $select , $params)) {

                                $changeincase = core_text::strtolower($existinguser->$column) === core_text::strtolower(
                                                $user->$column);

                                if ($changeincase) {
                                    // If only case is different then switch to lower case and carry on.
                                    $user->$column = core_text::strtolower($user->$column);
                                    continue;
                                } else if ($noemailduplicates) {
                                    $upt->track('email', $stremailduplicate, 'error');
                                    $upt->track('status', $strusernotupdated, 'error');
                                    $userserrors++;
                                    continue 2;
                                } else {
                                    $upt->track('email', $stremailduplicate, 'warning');
                                }
                            }
                            if (!validate_email($user->email)) {
                                $upt->track('email', get_string('invalidemail'), 'warning');
                            }
                        }

                        if ($column === 'lang') {
                            if (empty($user->lang)) {
                                // Do not change to not-set value.
                                continue;
                            } else if (core_user::clean_field($user->lang, 'lang') === '') {
                                $upt->track('status', get_string('cannotfindlang', 'error', $user->lang), 'warning');
                                continue;
                            }
                        }

                        if (in_array($column, $upt->columns)) {
                            $upt->track($column, s($existinguser->$column).'-->'.s($user->$column), 'info', false);
                        }
                        $existinguser->$column = $user->$column;
                        $doupdate = true;
                    }
                }
            }

            try {
                $auth = get_auth_plugin($existinguser->auth);
            } catch (Exception $e) {
                $upt->track('auth', get_string('userautherror', 'error', s($existinguser->auth)), 'error');
                $upt->track('status', $strusernotupdated, 'error');
                $userserrors++;
                continue;
            }
            $isinternalauth = $auth->is_internal();

            // Deal with suspending and activating of accounts.
            if ($allowsuspends && isset($user->suspended) && $user->suspended !== '') {
                $user->suspended = $user->suspended ? 1 : 0;
                if ($existinguser->suspended != $user->suspended) {
                    $upt->track('suspended', '', 'normal', false);
                    $upt->track('suspended', $stryesnooptions[$existinguser->suspended].'-->'.$stryesnooptions[$user->suspended], 'info', false);
                    $existinguser->suspended = $user->suspended;
                    $doupdate = true;
                    if ($existinguser->suspended) {
                        $dologout = true;
                    }
                }
            }

            // Changing of passwords is a special case.
            // Do not force password changes for external auth plugins!.
            $oldpw = $existinguser->password;

            if ($remoteuser) {
                // Do not mess with passwords of remote users.

            } else if (!$isinternalauth) {
                $existinguser->password = AUTH_PASSWORD_NOT_CACHED;
                $upt->track('password', '-', 'normal', false);
                // Clean up prefs.
                unset_user_preference('create_password', $existinguser);
                unset_user_preference('auth_forcepasswordchange', $existinguser);

            } else if (!empty($user->password)) {
                if ($updatepasswords) {
                    // Check for passwords that we want to force users to reset next.
                    // time they log in.
                    $errmsg = null;
                    $weak = !check_password_policy($user->password, $errmsg, $user);
                    if ($resetpasswords == UU_PWRESET_ALL || ($resetpasswords == UU_PWRESET_WEAK && $weak)) {
                        if ($weak) {
                            $weakpasswords++;
                            $upt->track('password', $strinvalidpasswordpolicy, 'warning');
                        }
                        set_user_preference('auth_forcepasswordchange', 1, $existinguser);
                    } else {
                        unset_user_preference('auth_forcepasswordchange', $existinguser);
                    }
                    unset_user_preference('create_password', $existinguser); // No need to create password any more.

                    // Use a low cost factor when generating bcrypt hash otherwise.
                    // Hashing would be slow when uploading lots of users. Hashes.
                    // Will be automatically updated to a higher cost factor the first.
                    // Time the user logs in.
                    $existinguser->password = hash_internal_user_password($user->password, true);
                    $upt->track('password', $user->password, 'normal', false);
                } else {
                    // Do not print password when not changed.
                    $upt->track('password', '', 'normal', false);
                }
            }

            $existinguser->userid = $user->id;
            $existinguser->salutation = $user->custom_salutation;
            $existinguser->type = $user->custom_type;
            $existinguser->dob = $user->custom_dob;
            $existinguser->subject = $user->custom_subjects;
            $existinguser->position = $user->custom_position;
            $existinguser->uid = $user->custom_uid;
            $existinguser->schoolid = $user->custom_school;
            $existinguser->doj = $user->custom_doj;
            $existinguser->jobtype = $user->custom_jobtype;
            $existinguser->grade = $user->custom_grade;
            $existinguser->caste = $user->custom_caste;
            $existinguser->post = $user->custom_post;

            if ($doupdate || $existinguser->password !== $oldpw) {
                // We want only users that were really updated.

                user_update_user($existinguser, false, false);
                // Custom update user custom fields.

                save_user_customdetails($existinguser);

                $upt->track('status', $struserupdated);
                $usersupdated++;

                if (!$remoteuser) {
                    // Pre-process custom profile menu fields data from csv file.
                    $existinguser = uu_pre_process_custom_profile_data($existinguser);
                    // Save custom profile fields data from csv file.
                }

                if ($bulk == UU_BULK_UPDATED || $bulk == UU_BULK_ALL) {
                    if (!in_array($user->id, $SESSION->bulk_users)) {
                        $SESSION->bulk_users[] = $user->id;
                    }
                }

                // Trigger event.
                \core\event\user_updated::create_from_userid($existinguser->id)->trigger();
            } else {

                // No user information changed.
                $upt->track('status', $struseruptodate);
                $usersuptodate++;

                if ($bulk == UU_BULK_ALL) {
                    if (!in_array($user->id, $SESSION->bulk_users)) {
                        $SESSION->bulk_users[] = $user->id;
                    }
                }
            }

            if ($dologout) {
                \core\session\manager::kill_user_sessions($existinguser->id);
            }

        } else {
            // Save the new user to the database.

            $user->confirmed = 1;
            $user->timemodified = time();
            $user->timecreated = time();
            $user->mnethostid = $CFG->mnet_localhost_id; // We support ONLY local accounts here, sorry.

            if (!isset($user->suspended) || $user->suspended === '') {
                $user->suspended = 0;
            } else {
                $user->suspended = $user->suspended ? 1 : 0;
            }
            $upt->track('suspended', $stryesnooptions[$user->suspended], 'normal', false);

            if (empty($user->auth)) {
                $user->auth = 'manual';
            }
            $upt->track('auth', $user->auth, 'normal', false);

            // Do not insert record if new auth plugin does not exist!.
            try {
                $auth = get_auth_plugin($user->auth);
            } catch (Exception $e) {
                $upt->track('auth', get_string('userautherror', 'error', s($user->auth)), 'error');
                $upt->track('status', $strusernotaddederror, 'error');
                $userserrors++;
                continue;
            }
            if (!isset($supportedauths[$user->auth])) {
                $upt->track('auth', $struserauthunsupported, 'warning');
            }

            $isinternalauth = $auth->is_internal();

            if (empty($user->email)) {
                $upt->track('email', get_string('invalidemail'), 'error');
                $upt->track('status', $strusernotaddederror, 'error');
                $userserrors++;
                continue;

            } else if ($DB->record_exists('user', array('email' => $user->email))) {
                if ($noemailduplicates) {
                    $upt->track('email', $stremailduplicate, 'error');
                    $upt->track('status', $strusernotaddederror, 'error');
                    $userserrors++;
                    continue;
                } else {
                    $upt->track('email', $stremailduplicate, 'warning');
                }
            }
            if (!validate_email($user->email)) {
                $upt->track('email', get_string('invalidemail'), 'warning');
            }

            if (empty($user->lang)) {
                $user->lang = '';
            } else if (core_user::clean_field($user->lang, 'lang') === '') {
                $upt->track('status', get_string('cannotfindlang', 'error', $user->lang), 'warning');
                $user->lang = '';
            }

            $forcechangepassword = false;

            if ($isinternalauth) {
                if (empty($user->password)) {
                    if ($createpasswords) {
                        $user->password = get_string('tobegenerated', 'local_user_management');
                        $upt->track('password', '', 'normal', false);
                        $upt->track('password', get_string('uupasswordcron', 'tool_uploaduser'), 'warning', false);
                    } else {
                        $upt->track('password', '', 'normal', false);
                        $upt->track('password', get_string('missingfield', 'error', 'password'), 'error');
                        $upt->track('status', $strusernotaddederror, 'error');
                        $userserrors++;
                        continue;
                    }
                } else {
                    $errmsg = null;
                    $weak = !check_password_policy($user->password, $errmsg, $user);
                    if ($resetpasswords == UU_PWRESET_ALL || ($resetpasswords == UU_PWRESET_WEAK && $weak)) {
                        if ($weak) {
                            $weakpasswords++;
                            $upt->track('password', $strinvalidpasswordpolicy, 'warning');
                        }
                        $forcechangepassword = true;
                    }
                    // Use a low cost factor when generating bcrypt hash otherwise.
                    // Hashing would be slow when uploading lots of users. Hashes.
                    // Will be automatically updated to a higher cost factor the first.
                    // Time the user logs in.
                    $user->password = hash_internal_user_password($user->password, true);
                }
            } else {
                $user->password = AUTH_PASSWORD_NOT_CACHED;
                $upt->track('password', '-', 'normal', false);
            }
            // To avoid exponential values coming into the database.
            $user->phone1 = number_format($user->phone1, 0, '', '');

            $user->salutation = $user->custom_salutation;
            $user->type = $user->custom_type;
            $user->dob = $user->custom_dob;
            $user->subject = $user->custom_subjects;
            $user->position = $user->custom_position;
            $user->uid = $user->custom_uid;
            $user->schoolid = $user->custom_school;
            $user->doj = $user->custom_doj;
            $user->jobtype = $user->custom_jobtype;
            $user->grade = $user->custom_grade;
            $user->caste = $user->custom_caste;
            $user->post = $user->custom_post;

            $user->id = user_create_user($user, false, false);
            $user->userid = $user->id;
            // Custom save profile fields data.
            save_user_customdetails($user);

            // Participant role assignment on user creation.
            $participantroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
            role_assign($participantroleid, $user->id, 1);

            $upt->track('username', html_writer::link(new moodle_url('/user/profile.php', array('id' => $user->id)), s($user->username)), 'normal', false);

            // Pre-process custom profile menu fields data from csv file.
            $user = uu_pre_process_custom_profile_data($user);
            
            if ($forcechangepassword) {
                set_user_preference('auth_forcepasswordchange', 1, $user);
            }
            if ($user->password === 'to be generated') {
                set_user_preference('create_password', 1, $user);
            }

            // Trigger event.
            \core\event\user_created::create_from_userid($user->id)->trigger();

            $upt->track('status', $struseradded);
            $upt->track('id', $user->id, 'normal', false);
            $usersnew++;
			
            // Make sure user context exists.
            context_user::instance($user->id);

            if ($bulk == UU_BULK_NEW || $bulk == UU_BULK_ALL) {
                if (!in_array($user->id, $SESSION->bulk_users)) {
                    $SESSION->bulk_users[] = $user->id;
                }
            }
        }

        // Update user interests.
        if (isset($user->interests) && strval($user->interests) !== '') {
            useredit_update_interests($user, preg_split('/\s*,\s*/', $user->interests, -1, PREG_SPLIT_NO_EMPTY));
        }

        // Add to cohort first, it might trigger enrolments indirectly - do NOT create cohorts here!.
        foreach ($filecolumns as $column) {
            if (!preg_match('/^cohort\d+$/', $column)) {
                continue;
            }

            if (!empty($user->$column)) {
                $addcohort = $user->$column;
                if (!isset($cohorts[$addcohort])) {
                    if (is_number($addcohort)) {
                        // Only non-numeric idnumbers!.
                        $cohort = $DB->get_record('cohort', array('id' => $addcohort));
                    } else {
                        $cohort = $DB->get_record('cohort', array('idnumber' => $addcohort));
                        if (empty($cohort) && has_capability('moodle/cohort:manage', context_system::instance())) {
                            // Cohort was not found. Create a new one.
                            $cohortid = cohort_add_cohort((object)array(
                                'idnumber' => $addcohort,
                                'name' => $addcohort,
                                'contextid' => context_system::instance()->id
                            ));
                            $cohort = $DB->get_record('cohort', array('id' => $cohortid));
                        }
                    }

                    if (empty($cohort)) {
                        $cohorts[$addcohort] = get_string('unknowncohort', 'core_cohort', s($addcohort));
                    } else if (!empty($cohort->component)) {
                        // Cohorts synchronised with external sources must not be modified!.
                        $cohorts[$addcohort] = get_string('external', 'core_cohort');
                    } else {
                        $cohorts[$addcohort] = $cohort;
                    }
                }

                if (is_object($cohorts[$addcohort])) {
                    $cohort = $cohorts[$addcohort];
                    if (!$DB->record_exists('cohort_members', array('cohortid' => $cohort->id, 'userid' => $user->id))) {
                        cohort_add_member($cohort->id, $user->id);
                        // We might add special column later, for now let's abuse enrolments.
                        $upt->track('enrolments', get_string('useradded', 'core_cohort', s($cohort->name)));
                    }
                } else {
                    // Error message.
                    $upt->track('enrolments', $cohorts[$addcohort], 'error');
                }
            }
        }


        // Find course enrolments, groups, roles/types and enrol periods.
        // This is again a special case, we always do this for any updated or created users.
        foreach ($filecolumns as $column) {
            if (preg_match('/^sysrole\d+$/', $column)) {

                if (!empty($user->$column)) {
                    $sysrolename = $user->$column;
                    if ($sysrolename[0] == '-') {
                        $removing = true;
                        $sysrolename = substr($sysrolename, 1);
                    } else {
                        $removing = false;
                    }

                    if (array_key_exists($sysrolename, $sysrolecache)) {
                        $sysroleid = $sysrolecache[$sysrolename]->id;
                    } else {
                        $upt->track('enrolments', get_string('unknownrole', 'error', s($sysrolename)), 'error');
                        continue;
                    }

                    if ($removing) {
                        if (user_has_role_assignment($user->id, $sysroleid, SYSCONTEXTID)) {
                            role_unassign($sysroleid, $user->id, SYSCONTEXTID);
                            $upt->track('enrolments', get_string('unassignedsysrole',
                                    'tool_uploaduser', $sysrolecache[$sysroleid]->name));
                        }
                    } else {
                        if (!user_has_role_assignment($user->id, $sysroleid, SYSCONTEXTID)) {
                            role_assign($sysroleid, $user->id, SYSCONTEXTID);
                            $upt->track('enrolments', get_string('assignedsysrole',
                                    'tool_uploaduser', $sysrolecache[$sysroleid]->name));
                        }
                    }
                }

                continue;
            }
            if (!preg_match('/^course\d+$/', $column)) {
                continue;
            }
            $i = substr($column, 6);

            if (empty($user->{'course'.$i})) {
                continue;
            }
            $shortname = $user->{'course'.$i};
            if (!array_key_exists($shortname, $ccache)) {
                if (!$course = $DB->get_record('course', array('shortname' => $shortname), 'id, shortname')) {
                    $upt->track('enrolments', get_string('unknowncourse', 'error', s($shortname)), 'error');
                    continue;
                }
                $ccache[$shortname] = $course;
                $ccache[$shortname]->groups = null;
            }
            $courseid = $ccache[$shortname]->id;
            $coursecontext = context_course::instance($courseid);
            if (!isset($manualcache[$courseid])) {
                $manualcache[$courseid] = false;
                if ($manual) {
                    if ($instances = enrol_get_instances($courseid, false)) {
                        foreach ($instances as $instance) {
                            if ($instance->enrol === 'manual') {
                                $manualcache[$courseid] = $instance;
                                break;
                            }
                        }
                    }
                }
            }

            if ($courseid == SITEID) {
                // Technically frontpage does not have enrolments, but only role assignments.
                // let's not invent new lang strings here for this rarely used feature.

                if (!empty($user->{'role'.$i})) {
                    $rolename = $user->{'role'.$i};
                    if (array_key_exists($rolename, $rolecache)) {
                        $roleid = $rolecache[$rolename]->id;
                    } else {
                        $upt->track('enrolments', get_string('unknownrole', 'error', s($rolename)), 'error');
                        continue;
                    }

                    role_assign($roleid, $user->id, context_course::instance($courseid));

                    $a = new stdClass();
                    $a->course = $shortname;
                    $a->role = $rolecache[$roleid]->name;
                    $upt->track('enrolments', get_string('enrolledincourserole', 'enrol_manual', $a));
                }

            } else if ($manual && $manualcache[$courseid]) {

                // Find role.
                $roleid = false;
                if (!empty($user->{'role'.$i})) {
                    $rolename = $user->{'role'.$i};
                    if (array_key_exists($rolename, $rolecache)) {
                        $roleid = $rolecache[$rolename]->id;
                    } else {
                        $upt->track('enrolments', get_string('unknownrole', 'error', s($rolename)), 'error');
                        continue;
                    }

                } else if (!empty($user->{'type'.$i})) {
                    // If no role, then find "old" enrolment type.
                    $addtype = $user->{'type'.$i};
                    if ($addtype < 1 || $addtype > 3) {
                        $upt->track('enrolments', $strerror. ': typeN = 1|2|3', 'error');
                        continue;
                    } else if (empty($formdata->{'uulegacy'. $addtype})) {
                        continue;
                    } else {
                        $roleid = $formdata->{'uulegacy'. $addtype};
                    }
                } else {
                    // No role specified, use the default from manual enrol plugin.
                    $roleid = $manualcache[$courseid]->roleid;
                }

                if ($roleid) {
                    // Find duration and/or enrol status.
                    $timeend = 0;
                    $timestart = $today;
                    $status = null;

                    if (isset($user->{'enrolstatus'.$i})) {
                        $enrolstatus = $user->{'enrolstatus'.$i};
                        if ($enrolstatus == '') {
                            $status = null;
                        } else if ($enrolstatus === (string)ENROL_USER_ACTIVE) {
                            $status = ENROL_USER_ACTIVE;
                        } else if ($enrolstatus === (string)ENROL_USER_SUSPENDED) {
                            $status = ENROL_USER_SUSPENDED;
                        } else {
                            debugging('Unknown enrolment status.');
                        }
                    }

                    if (!empty($user->{'enroltimestart'.$i})) {
                        $parsedtimestart = strtotime($user->{'enroltimestart'.$i});
                        if ($parsedtimestart !== false) {
                            $timestart = $parsedtimestart;
                        }
                    }

                    if (!empty($user->{'enrolperiod'.$i})) {
                        $duration = (int)$user->{'enrolperiod'.$i} * 60 * 60 * 24; // Convert days to seconds.
                        if ($duration > 0) { // Sanity check.
                            $timeend = $timestart + $duration;
                        }
                    } else if ($manualcache[$courseid]->enrolperiod > 0) {
                        $timeend = $timestart + $manualcache[$courseid]->enrolperiod;
                    }

                    $manual->enrol_user($manualcache[$courseid], $user->id, $roleid, $timestart, $timeend, $status);

                    $a = new stdClass();
                    $a->course = $shortname;
                    $a->role = $rolecache[$roleid]->name;
                    $upt->track('enrolments', get_string('enrolledincourserole', 'enrol_manual', $a));
                }
            }

            // Find group to add to.
            if (!empty($user->{'group'.$i})) {
                // Make sure user is enrolled into course before adding into groups.
                if (!is_enrolled($coursecontext, $user->id)) {
                    $upt->track('enrolments', get_string('addedtogroupnotenrolled', '', $user->{'group'.$i}), 'error');
                    continue;
                }
                // Build group cache.
                if (is_null($ccache[$shortname]->groups)) {
                    $ccache[$shortname]->groups = array();
                    if ($groups = groups_get_all_groups($courseid)) {
                        foreach ($groups as $gid => $group) {
                            $ccache[$shortname]->groups[$gid] = new stdClass();
                            $ccache[$shortname]->groups[$gid]->id = $gid;
                            $ccache[$shortname]->groups[$gid]->name = $group->name;
                            if (!is_numeric($group->name)) { // Only non-numeric names are supported!!!.
                                $ccache[$shortname]->groups[$group->name] = new stdClass();
                                $ccache[$shortname]->groups[$group->name]->id = $gid;
                                $ccache[$shortname]->groups[$group->name]->name = $group->name;
                            }
                        }
                    }
                }
                // Group exists?
                $addgroup = $user->{'group'.$i};
                if (!array_key_exists($addgroup, $ccache[$shortname]->groups)) {
                    // If group doesn't exist,  create it.
                    $newgroupdata = new stdClass();
                    $newgroupdata->name = $addgroup;
                    $newgroupdata->courseid = $ccache[$shortname]->id;
                    $newgroupdata->description = '';
                    $gid = groups_create_group($newgroupdata);
                    if ($gid) {
                        $ccache[$shortname]->groups[$addgroup] = new stdClass();
                        $ccache[$shortname]->groups[$addgroup]->id = $gid;
                        $ccache[$shortname]->groups[$addgroup]->name = $newgroupdata->name;
                    } else {
                        $upt->track('enrolments', get_string('unknowngroup', 'error', s($addgroup)), 'error');
                        continue;
                    }
                }
                $gid = $ccache[$shortname]->groups[$addgroup]->id;
                $gname = $ccache[$shortname]->groups[$addgroup]->name;

                try {
                    if (groups_add_member($gid, $user->id)) {
                        $upt->track('enrolments', get_string('addedtogroup', '', s($gname)));
                    } else {
                        $upt->track('enrolments', get_string('addedtogroupnot', '', s($gname)), 'error');
                    }
                } catch (moodle_exception $e) {
                    $upt->track('enrolments', get_string('addedtogroupnot', '', s($gname)), 'error');
                    continue;
                }
            }
        }
        $validation[$user->username] = core_user::validate($user);
    }
    $upt->close(); // Close table.
    if (!empty($validation)) {
        foreach ($validation as $username => $result) {
            if ($result !== true) {
                \core\notification::warning(get_string('invaliduserdata', 'tool_uploaduser', s($username)));
            }
        }
    }
    $cir->close();
    $cir->cleanup(true);

    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != UU_USER_UPDATE) {
        echo get_string('userscreated', 'tool_uploaduser').': '.$usersnew.'<br />';
    }
    if ($optype == UU_USER_UPDATE || $optype == UU_USER_ADD_UPDATE) {
        echo get_string('usersupdated', 'tool_uploaduser').': '.$usersupdated.'<br />';
    }
    if ($allowdeletes) {
        echo get_string('usersdeleted', 'tool_uploaduser').': '.$deletes.'<br />';
        echo get_string('deleteerrors', 'tool_uploaduser').': '.$deleteerrors.'<br />';
    }
    if ($allowrenames) {
        echo get_string('usersrenamed', 'tool_uploaduser').': '.$renames.'<br />';
        echo get_string('renameerrors', 'tool_uploaduser').': '.$renameerrors.'<br />';
    }
    if ($usersskipped) {
        echo get_string('usersskipped', 'tool_uploaduser').': '.$usersskipped.'<br />';
    }
    echo get_string('usersweakpassword', 'tool_uploaduser').': '.$weakpasswords.'<br />';
    echo get_string('errors', 'tool_uploaduser').': '.$userserrors.'</p>';
    echo $OUTPUT->box_end();

    if ($bulk) {
        echo $OUTPUT->continue_button($bulknurl);
    } else {
        echo $OUTPUT->continue_button($bulknurl);
    }
    echo $OUTPUT->footer();
    die;
}

// Print the header.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('uploaduserspreview', 'tool_uploaduser'));

// NOTE: this is JUST csv processing preview, we must not prevent import from here if there is something in the file!!.
// This was intended for validation of csv formatting and encoding, not filtering the data!!!!.
// We definitely must not process the whole file!.

// Preview table data.
$data = array();
$cir->init();
$linenum = 1; // Column header is first line.
$noerror = true; // Keep status of any error.
while ($linenum <= $previewrows && $fields = $cir->next()) {
    $linenum++;
    $rowcols = array();
    $rowcols['line'] = $linenum;
    foreach ($fields as $key => $field) {
        $rowcols[$filecolumns[$key]] = s(trim($field));
    }
    $rowcols['status'] = array();
    $rowcols['username'] = strtolower($rowcols['username']);
    if (empty($rowcols['email'])) {
        $rowcols['email'] = $rowcols['username'].'@dummymail.com';
    }
    if (isset($rowcols['username'])) {
        $stdusername = core_user::clean_field($rowcols['username'], 'username');
        if ($rowcols['username'] !== $stdusername) {
            $rowcols['status'][] = get_string('invalidusernameupload');
        }
        if ($userid = $DB->get_field('user', 'id', array('username' => $stdusername, 'mnethostid' => $CFG->mnet_localhost_id))) {
            $rowcols['username'] = html_writer::link(new moodle_url('/user/profile.php', array('id' => $userid)), $rowcols['username']);
        }
    } else {
        $rowcols['status'][] = get_string('missingusername');
    }

    if (isset($rowcols['email'])) {
        if (!validate_email($rowcols['email'])) {
            $rowcols['status'][] = get_string('invalidemail');
        }

        $select = $DB->sql_like('email', ':email', false, true, false, '|');
        $params = array('email' => $DB->sql_like_escape($rowcols['email'], '|'));
        if ($DB->record_exists_select('user', $select , $params)) {
            $rowcols['status'][] = $stremailduplicate;
        }
    }

    if (isset($rowcols['city'])) {
        $rowcols['city'] = $rowcols['city'];
    }

    if (isset($rowcols['theme'])) {
        list($status, $message) = field_value_validators::validate_theme($rowcols['theme']);
        if ($status !== 'normal' && !empty($message)) {
            $rowcols['status'][] = $message;
        }
    }

    // Custom fields validation for form 1 submission.
    if (isset($rowcols['custom_salutation'])) {
        $salutationarray = array('Dr.', 'Mr.', 'Mrs.', 'Ms.');
        if (empty($rowcols['custom_salutation'])) {
            // $rowcols['status'][] = 'Missing Salutation';
        } else {
            if (!in_array($rowcols['custom_salutation'], $salutationarray)) {
                $rowcols['status'][] = get_string('invalidsolution', 'local_user_management');
            }
        }
    }

    if (isset($rowcols['custom_type'])) {
        $typearray = array('Internal', 'External');
        if (empty($rowcols['custom_type'])) {
            $rowcols['status'][] = get_string('missingusertype', 'local_user_management');
        } else {
            if (!in_array($rowcols['custom_type'], $typearray)) {
                $rowcols['status'][] = get_string('invalidusertype', 'local_user_management');
            }
        }
    }

    if (isset($rowcols['custom_dob'])) {
        if (!empty($rowcols['custom_dob'])) {
            $date = explode("-", $rowcols['custom_dob']);
            if (checkdate ($date[1], $date[0], $date[2])) {
                // Proper date format.
            } else {
                $rowcols['status'][] = get_string('invaliddateofbirth', 'local_user_management');
            }
        }
    }

    if (isset($rowcols['custom_gender'])) {
        $genderarray = array("male", "female", "others");
        if (!in_array($rowcols['custom_gender'], $genderarray)) {
            $rowcols['status'][] = 'Invalid Gender';
        }
    }

    if (isset($rowcols['custom_uid'])) {
        $rowcols['custom_uid'] = trim($rowcols['custom_uid']);
        if (strlen($rowcols['custom_uid']) > 12) {
            $rowcols['status'][] = 'Invalid UID';
        }
    }

    if (isset($rowcols['custom_department'])) {
        if (empty($rowcols['custom_department'])) {
            // $rowcols['status'][] = 'Missing Department';
        } else {
            $depts = explode(',', $rowcols['custom_department']);
            foreach ($depts as $dept) {
                $dept = trim($dept);
                if (!$DB->record_exists('local_departments', array('code' => $dept))) {
                    $rowcols['status'][] = 'Invalid Department - '.$dept;
                }
            }
        }
    }
    
    if (isset($rowcols['custom_subjects'])) {
        if (empty($rowcols['custom_subjects'])) {
            // $rowcols['status'][] = 'Missing Subject';
        } else {
            $subs = explode(',', $rowcols['custom_subjects']);
            foreach ($subs as $sub) {
                $sub = trim($sub);
                if (!$DB->record_exists('local_subjects', array('code' => $sub))) {
                    $rowcols['status'][] = get_string('invalidsubject', 'local_user_management');
                }
            }
        }
    }
    
    if (isset($rowcols['custom_position'])) {
        if (empty($rowcols['custom_position'])) {
            // $rowcols['status'][] = 'Missing School position';
        } else {
            $schoolposs = explode(',', $rowcols['custom_position']);
            foreach ($schoolposs as $schoolpos) {
                $schoolpos = trim($schoolpos);
                if (!$DB->record_exists('local_school_positions', array('shortname' => $schoolpos))) {
                    $rowcols['status'][] = get_string('invalidschoolposition', 'local_user_management');
                }
            }
        }
    }

    if (isset($rowcols['custom_school'])) {
        if (empty($rowcols['custom_school'])) {
            // $rowcols['status'][] = 'Missing School';
        } else {
            $school = $rowcols['custom_school'];
            $school = trim($school);
            if (!$DB->record_exists('local_schools', array('code' => $school))) {
                $rowcols['status'][] = get_string('invalidschool', 'local_user_management');
            }
        }
    }

    if (isset($rowcols['custom_doj'])) {
        if (!empty($rowcols['custom_doj'])) {
            $date = explode("-", $rowcols['custom_doj']);
            if (checkdate ($date[1], $date[0], $date[2])) {
                // Proper date format.
            } else {
                $rowcols['status'][] = get_string('invaliddateofjoining', 'local_user_management');
            }
        }
    }

    if (isset($rowcols['custom_jobtype'])) {
        $jobtypearray = array("contract", "regular");
        if (!in_array($rowcols['custom_jobtype'], $jobtypearray)) {
            $rowcols['status'][] = get_string('invalidjobtype', 'local_user_management');
        }
    }

    if (isset($rowcols['custom_grade'])) {
        if (empty($rowcols['custom_grade'])) {
            // $rowcols['status'][] = 'Missing Grade';
        } else {
            $grade = strtoupper($rowcols['custom_grade']);
            $grade = trim($grade);
            if (!$DB->record_exists('local_grades', array('name' => $grade))) {
                $rowcols['status'][] = get_string('invalidgrade', 'local_user_management');
            }
        }
    }

    if (isset($rowcols['custom_post'])) {
        if (empty($rowcols['custom_post'])) {
            // $rowcols['status'][] = 'Missing Post';
        } else {
            $post = strtoupper($rowcols['custom_post']);
            $post = trim($post);
            if (!$DB->record_exists('local_posts', array('name' => $post))) {
                $rowcols['status'][] = get_string('invalidpost', 'local_user_management');
            }
        }
    }

    if (isset($rowcols['custom_caste'])) {
        if (empty($rowcols['custom_caste'])) {
            // $rowcols['status'][] = 'Missing Caste';
        } else {
            $caste = strtoupper($rowcols['custom_caste']);
            if (!$DB->record_exists('local_castes', array('name' => $caste))) {
                $rowcols['status'][] = get_string('invalidcaste', 'local_user_management');
            }
        }
    }

    // Check if rowcols have custom profile field with correct data and update error state.
    $noerror = uu_check_custom_profile_data($rowcols) && $noerror;
    $rowcols['status'] = implode('<br />', $rowcols['status']);
    $data[] = $rowcols;
}
if ($fields = $cir->next()) {
    $data[] = array_fill(0, count($fields) + 2, '...');
}
$cir->close();

$table = new html_table();
$table->id = "uupreview";
$table->attributes['class'] = 'generaltable';
$table->tablealign = 'center';
$table->summary = get_string('uploaduserspreview', 'tool_uploaduser');
$table->head = array();
$table->data = $data;

$table->head[] = get_string('uucsvline', 'tool_uploaduser');
foreach ($filecolumns as $column) {
    $table->head[] = $column;
}
$table->head[] = get_string('status');

echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap table-responsive'));

// Print the form if valid values are available.
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;
