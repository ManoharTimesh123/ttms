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
class external_user_records extends external_multiple_structure {
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
                    'data' => new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'The name of the user'),
                            'email' => new external_value(PARAM_RAW, 'The email of the user'),
                            'profile_picture' => new external_value(PARAM_RAW, 'The image of the user'),
                            'uid' => new external_value(PARAM_RAW, 'The uid of the user'),
                            'school_name' => new external_value(PARAM_RAW, 'The school name of the user'),
                            'school_code' => new external_value(PARAM_RAW, 'The school code of the user'),
                            'position' => new external_value(PARAM_RAW, 'The position of the user'),
                            'subject' => new external_value(PARAM_RAW, 'The subject of the user'),
                        )
                    )
                )
            )
        );
    }
}

