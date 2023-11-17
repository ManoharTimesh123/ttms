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

namespace local_customapi\external;

use external_multiple_structure;
use external_single_structure;
use external_value;

/**
 * An array of records matching an externalid to a moodle id.
 *
 * Used when sending an api response, to provide the caller some information on success.
 *
 * @package local_customapi
 * @package local_customapi
 */
class external_wall_records extends external_multiple_structure {
    /**
     * Constructor
     */
    public function __construct(
        $entity = 'Data entity being amended',
        $crud = 'CRUD operation performed',
        $data = 'Items'
    ) {
        parent::__construct(
            new external_single_structure(
                array(
                    'entity' => new external_value(PARAM_TEXT, $entity, VALUE_OPTIONAL),
                    'crud' => new external_value(PARAM_TEXT, $crud, VALUE_OPTIONAL),
                    'data' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'The ID of the item'),
                                'course_id' => new external_value(PARAM_INT, 'The ID of the course'),
                                'course_name' => new external_value(PARAM_RAW, 'The name of the course'),
                                'user_like_post' => new external_value(PARAM_BOOL, 'Current user like post or not'),
                                'post_content' => new external_value(PARAM_RAW, 'The name of the item'),
                                'post_file' => new external_value(PARAM_RAW, 'The file of the item'),
                                'post_added_by' => new external_value(PARAM_RAW, 'The description of the item'),
                                'created_date' => new external_value(PARAM_INT, 'The created date of the item'),
                                'post_like_count' => new external_value(PARAM_INT, 'like count of  item'),
                                'post_share_count' => new external_value(PARAM_INT, 'Share count of  item'),
                                'post_comment_count' => new external_value(PARAM_INT, 'Comment count of  item'),
                                'post_comment' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'id' => new external_value(PARAM_INT, 'The ID of the item'),
                                            'description' => new external_value(PARAM_RAW, 'The description of the item'),
                                            'commented_by' => new external_value(PARAM_RAW, 'The commenter name'),
                                            'timecreated' => new external_value(PARAM_RAW, 'The created time of the item'),
                                        )
                                    )
                                ),
                            )
                        )
                    )
                )
            )
        );
    }
}

