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
 * The Wall Post Management
 *
 * @package local_wall
 */
defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_wall_create_post' => array(
        'classname' => 'local_wall_external',
        'methodname' => 'post_create',
        'classpath' => 'local/wall/externallib.php',
        'description' => 'Create wall post',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_wall_update_post' => array(
        'classname' => 'local_wall_external',
        'methodname' => 'post_update',
        'classpath' => 'local/wall/externallib.php',
        'description' => 'Update wall post',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_wall_delete_post' => array(
        'classname' => 'local_wall_external',
        'methodname' => 'post_delete',
        'classpath' => 'local/wall/externallib.php',
        'description' => 'Delete wall post',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_wall_get_all_post' => array(
        'classname' => 'local_wall_external',
        'methodname' => 'get_posts',
        'classpath' => 'local/wall/externallib.php',
        'description' => 'Get all post',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_wall_create_post_comment' => array(
        'classname' => 'local_wall_external',
        'methodname' => 'post_comment_create',
        'classpath' => 'local/wall/externallib.php',
        'description' => 'Create wall post comment',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_wall_delete_post_comment' => array(
        'classname' => 'local_wall_external',
        'methodname' => 'post_comment_delete',
        'classpath' => 'local/wall/externallib.php',
        'description' => 'Delete wall post comment',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_wall_post_like' => array(
        'classname' => 'local_wall_external',
        'methodname' => 'post_like',
        'classpath' => 'local/wall/externallib.php',
        'description' => 'Add wall post like',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
    'local_wall_post_share' => array(
        'classname' => 'local_wall_external',
        'methodname' => 'post_share',
        'classpath' => 'local/wall/externallib.php',
        'description' => 'Add wall post share',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
    ),
);
