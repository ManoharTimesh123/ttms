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

require_once($CFG->dirroot . '/local/news/externallib.php');

class newshelper {

    public static function create_news($params) {

        try {

            $file = (isset($_FILES)) ? $_FILES : null;
            $news = \local_news_external::news_create($params, $file);

            if (!$news['data'][0]['processed']) {
                throw new customapiException('wserrorcreatenews', $params, $news['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorcreatenews', $params, $err);
        }

        return [
            'entity' => 'news',
            'crud' => 'c',
            'externalid' => count($params),
        ];
    }

    public static function read_news() {

        $news = \local_news_external::news_get();

        return [
            'records' => $news['data'],
        ];
    }

    public static function update_news($params) {

        try {

            $file = (isset($_FILES)) ? $_FILES : null;
            $news = \local_news_external::news_update($params, $file);

            if (!$news['data'][0]['processed']) {
                throw new customapiException('wserrorcreatenews', $params, $news['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorcreatenews', $params, $err);
        }

        return [
            'entity' => 'news',
            'crud' => 'c',
            'externalid' => $params['newsid'],
        ];
    }

    public static function delete_news($params) {

        try {

            $news = \local_news_external::news_delete($params);

            if (!$news['data'][0]['processed']) {
                throw new customapiException('wserrornotfundnews', $params, $news['data'][0]['record'] );
            }
        } catch (Exception $err) {
            throw new customapiException('wserrorcreatenews', $params, $err);
        }

        return [
            'entity' => 'news',
            'crud' => 'c',
            'externalid' => $params['newsid'],
        ];
    }
}
