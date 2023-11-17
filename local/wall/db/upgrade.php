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

function xmldb_local_wall_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    $result = true;

    if ($result && (int) $oldversion < 2023033100) {
        $tablelocalpostlikes = new xmldb_table('local_post_likes');
        $tablepostlikeaddfield = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NULL, null, 0, 'postunlike');

        if (!$dbman->field_exists($tablelocalpostlikes, $tablepostlikeaddfield)) {
            $dbman->add_field($tablelocalpostlikes, $tablepostlikeaddfield, $continue = true, $feedback = true);
        }

        $tablelocalpostshares = new xmldb_table('local_post_shares');
        $tablepostshareaddfield = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NULL, null, 0, 'socialprovider' );

        if (!$dbman->field_exists($tablelocalpostshares, $tablepostshareaddfield)) {
            $dbman->add_field($tablelocalpostshares, $tablepostshareaddfield, $continue = true, $feedback = true);
        }
        upgrade_plugin_savepoint($result, 2023033100, 'local', 'wall');
    }
    if ($result && (int) $oldversion < 2023041000) {
        $tablelocalposts = $table = new xmldb_table('local_posts');
        $tablelocalpostcomments = new xmldb_table('local_post_comments');
        $tablelocalpostlikes = new xmldb_table('local_post_likes');
        $tablelocalpostshares = new xmldb_table('local_post_shares');

        if ($dbman->table_exists($tablelocalposts)) {
            $dbman->rename_table($tablelocalposts, 'local_wall_posts', $continue = true, $feedback = true);
        }
        if ($dbman->table_exists($tablelocalpostcomments)) {
            $dbman->rename_table($tablelocalpostcomments, 'local_wall_post_comments', $continue = true, $feedback = true);
        }
        if ($dbman->table_exists($tablelocalpostlikes)) {
            $dbman->rename_table($tablelocalpostlikes, 'local_wall_post_likes', $continue = true, $feedback = true);
        }
        if ($dbman->table_exists($tablelocalpostshares)) {
            $dbman->rename_table($tablelocalpostshares, 'local_wall_post_shares', $continue = true, $feedback = true);
        }
        upgrade_plugin_savepoint($result, 2023041000, 'local', 'wall');
    }

    return $result;
}
