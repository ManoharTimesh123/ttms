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
 * Admin setting to detect and set permissions in Azure AD.
 *
 * @package local_o365
 * @author James McQuillan <james.mcquillan@remote-learner.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_o365\adminsetting;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/lib/adminlib.php');

/**
 * Admin setting to detect and set permissions in Azure AD.
 */
class azuresetup extends \admin_setting {

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $heading
     * @param string $description
     */
    public function __construct($name, $heading, $description) {
        $this->nosave = true;
        parent::__construct($name, $heading, $description, '0');
    }

    /**
     * Return the setting
     *
     * @return mixed returns config if successful else null
     */
    public function get_setting() {
        return true;
    }

    /**
     * Write the setting.
     *
     * We do this manually so just pretend here.
     *
     * @param mixed $data Incoming form data.
     * @return string Always empty string representing no issues.
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * Return an XHTML string for the setting.
     *
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        $button = \html_writer::tag('button', get_string('settings_detectperms_update', 'local_o365'),
            ['class' => 'refreshperms', 'style' => 'margin: 0 0 0.75rem']);
        $results = \html_writer::tag('div', '', ['class' => 'results']);
        $settinghtml = $button.$results;

        if (\local_o365\adminsetting\detectoidc::setup_step_complete() === true) {
            $existingsetting = $this->config_read($this->name);
            if (!empty($existingsetting)) {
                $messageattrs = [
                    'class' => 'permmessage'
                ];
                $message = \html_writer::tag('span', get_string('settings_detectperms_valid', 'local_o365'), $messageattrs);
            } else {
                $messageattrs = [
                    'class' => 'permmessage'
                ];
                $message = \html_writer::tag('span', get_string('settings_detectperms_invalid', 'local_o365'), $messageattrs);
            }
        } else {
            $icon = $OUTPUT->pix_icon('i/warning', 'prerequisite not complete', 'moodle');
            $message = \html_writer::tag('span', get_string('settings_detectperms_nocreds', 'local_o365'));
            $settinghtml .= \html_writer::tag('div', $icon.$message, ['class' => 'alert-info alert local_o365_statusmessage']);
        }

        // Using a <script> tag here instead of $PAGE->requires->js() because using $PAGE object loads file too late.
        $scripturl = new \moodle_url('/local/o365/classes/adminsetting/azuresetup.js');
        $settinghtml .= '<script src="'.$scripturl->out().'"></script>';

        $lastresults = get_config('local_o365', 'azuresetupresult');
        if (!empty($lastresults)) {
            $lastresults = @unserialize($lastresults);
            $lastresults = (!empty($lastresults) && is_object($lastresults)) ? $lastresults : false;
            $lastresults = json_encode(['success' => true, 'data' => $lastresults]);
        } else {
            $lastresults = json_encode(false);
        }

        $unifiedenabled = (\local_o365\rest\unified::is_enabled() === true) ? 'true' : 'false';

        $ajaxurl = new \moodle_url('/local/o365/ajax.php');
        $settinghtml .= '<script>
$(function() {
    var opts = {
        url: "'.$ajaxurl->out().'",
        lastresults: '.$lastresults.',
        iconsuccess: "'.addslashes($OUTPUT->pix_icon('t/check', 'success', 'moodle')).'",
        iconinfo: "'.addslashes($OUTPUT->pix_icon('i/warning', 'information', 'moodle')).'",
        iconerror: "'.addslashes($OUTPUT->pix_icon('t/delete', 'error', 'moodle')).'",

        strupdate: "'.addslashes(get_string('settings_azuresetup_update', 'local_o365')).'",
        strchecking: "'.addslashes(get_string('settings_azuresetup_checking', 'local_o365')).'",
        strmissingperms: "'.addslashes(get_string('settings_azuresetup_missingperms', 'local_o365')).'",
        strmissingappperms: "'.addslashes(get_string('settings_azuresetup_missingappperms', 'local_o365')).'",
        strpermscorrect: "'.addslashes(get_string('settings_azuresetup_permscorrect', 'local_o365')).'",
        strapppermscorrect: "'.addslashes(get_string('settings_azuresetup_apppermscorrect', 'local_o365')).'",
        strfixperms: "'.addslashes(get_string('settings_detectperms_fixperms', 'local_o365')).'",
        strfixprereq: "'.addslashes(get_string('settings_detectperms_fixprereq', 'local_o365')).'",
        strerrorfix: "'.addslashes(get_string('settings_detectperms_errorfix', 'local_o365')).'",
        strerrorcheck: "'.addslashes(get_string('settings_azuresetup_errorcheck', 'local_o365')).'",
        strnoinfo: "'.addslashes(get_string('settings_azuresetup_noinfo', 'local_o365')).'",

        strappdataheader: "'.addslashes(get_string('settings_azuresetup_appdataheader', 'local_o365')).'",
        strappdatadesc: "'.addslashes(get_string('settings_azuresetup_appdatadesc', 'local_o365')).'",
        strappdatareplyurlcorrect: "'.addslashes(get_string('settings_azuresetup_appdatareplyurlcorrect', 'local_o365')).'",
        strappdatareplyurlincorrect: "'.addslashes(get_string('settings_azuresetup_appdatareplyurlincorrect', 'local_o365')).'",
        strappdatareplyurlgeneralerror: "'.
            addslashes(get_string('settings_azuresetup_appdatareplyurlgeneralerror', 'local_o365')).'",
        strappdatasignonurlcorrect: "'.addslashes(get_string('settings_azuresetup_appdatasignonurlcorrect', 'local_o365')).'",
        strappdatasignonurlincorrect: "'.addslashes(get_string('settings_azuresetup_appdatasignonurlincorrect', 'local_o365')).'",
        strappdatasignonurlgeneralerror: "'.
            addslashes(get_string('settings_azuresetup_appdatasignonurlgeneralerror', 'local_o365')).'",
        strdetectedval: "'.addslashes(get_string('settings_azuresetup_detectedval', 'local_o365')).'",
        strcorrectval: "'.addslashes(get_string('settings_azuresetup_correctval', 'local_o365')).'",

        showunified: '.$unifiedenabled.',
        strunifiedheader: "'.addslashes(get_string('settings_azuresetup_unifiedheader', 'local_o365')).'",
        strunifieddesc: "'.addslashes(get_string('settings_azuresetup_unifieddesc', 'local_o365')).'",
        strunifiederror: "'.addslashes(get_string('settings_azuresetup_unifiederror', 'local_o365')).'",
        strunifiedpermerror: "'.addslashes(get_string('settings_azuresetup_strunifiedpermerror', 'local_o365')).'",
        strunifiedmissing: "'.addslashes(get_string('settings_azuresetup_unifiedmissing', 'local_o365')).'",
        strunifiedactive: "'.addslashes(get_string('settings_azuresetup_unifiedactive', 'local_o365')).'",

        strlegacyheader: "'.addslashes(get_string('settings_azuresetup_legacyheader', 'local_o365')).'",
        strlegacydesc: "'.addslashes(get_string('settings_azuresetup_legacydesc', 'local_o365')).'",
        strlegacyerror: "'.addslashes(get_string('settings_azuresetup_legacyerror', 'local_o365')).'",

        strtenanterror: "'.addslashes(get_string('settings_azuresetup_strtenanterror', 'local_o365')).'"
    };
    $("#admin-'.$this->name.'").azuresetup(opts);
});
                        </script>';

        return format_admin_setting($this, $this->visiblename, $settinghtml, $this->description, true, '', null, $query);
    }
}
