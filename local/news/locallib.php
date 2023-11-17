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
 * The News Management
 *
 * @package    local_news
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/user_management/locallib.php');

function add_news($data) {
    global $DB, $USER;

    $systemcontext = context_system::instance();

    $postdata = new stdClass();
    $postdata->title = strip_tags($data->title);
    $postdata->description = $data->description['text'];
    $postdata->datefrom = $data->datefrom;
    $postdata->dateto = $data->dateto;

    $schoolid = check_user_is_hos_and_get_school();
    $usereditownnews = false;

    if (!has_capability('local/news:manage', $systemcontext)) {
        $usereditownnews = true;
        if (!$schoolid) {
            throw new \moodle_exception(get_string('newshoserror', 'local_news'), 'local_news', '', 'Only HOS can add news');
        }
    }

    if ($postdata->dateto < $postdata->datefrom) {
        throw new \moodle_exception(get_string('newsendateerror', 'local_news'), 'local_news', '', 'End date must be greater that or equal to start date');
    }
    if ($data->id > 0) {

        $news = $DB->get_record('local_news', array('id' => $data->id, 'deleted' => 0));

        if ($usereditownnews && $news->createdby !== $USER->id) {
            throw new \moodle_exception(get_string('newseditownerror', 'local_news'), 'local_news', '');
        }

        $postdata->id = $news->id;
        $postdata->approved = 0;
        $postdata->timemodified = time();
        $postdata->updatedby = $USER->id;
        $DB->update_record('local_news', $postdata);
        return $news->id;
    } else {
        $postdata->timecreated = time();
        $postdata->timemodified = time();
        $postdata->schoolid = $schoolid;
        $postdata->createdby = $USER->id;
        $postdata->updatedby = $USER->id;
        $newsid = $DB->insert_record('local_news', $postdata);
        return $newsid;
    }

}

function check_user_is_hos_and_get_school() {
    global $USER;

    $userdetails = get_user_profile_details($USER, ['details', 'hos']);

    if (!empty($userdetails[$USER->id]->hos)) {

        foreach ($userdetails[$USER->id]->hos as $hos) {
            return $hos->id;
        }
    }

    return false;
}

function delete_news($id) {
    global $DB, $USER;

    $systemcontext = context_system::instance();

    $news = $DB->get_record('local_news', array('id' => $id, 'deleted' => 0));
    if (!$news) {
        throw new dml_exception('recordnotfound', 'local_news', get_string('newsmissingerrormsg', 'local_news'));
    }

    // When the required permission do not match while deleting the data.
    if (!is_siteadmin() &&
        !has_capability('local/news:manage', $systemcontext) &&
        (has_capability('local/news:manageown', $systemcontext) && $news->createdby !== $USER->id)
    ) {
        throw new \moodle_exception(get_string('newseditownerror', 'local_news'), 'local_news', '');
    }

    $postdata = new stdClass();
    $postdata->id = $news->id;
    $postdata->deleted = 1;
    $postdata->timedeleted = time();
    $postdata->deletedby = $USER->id;

    return $DB->update_record('local_news', $postdata);
}

function change_news_status($id) {
    global $DB, $USER;

    $news = $DB->get_record('local_news', array('id' => $id));
    if ($news->approved == 0) {
        $status = 1;
    } else {
        $status = 0;
    }
    $newsdata = new stdClass();
    $newsdata->id = $news->id;
    $newsdata->approved = $status;
    $newsdata->updatedby = $USER->id;

    return $DB->update_record('local_news', $newsdata);
}
