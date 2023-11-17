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
 * Course Catalog
 *
 * @package    local_course_catalog
 */

defined('MOODLE_INTERNAL') || die();

function build_category_tree($categories) {
    $output = '';
    if (!empty($categories)) {
        $output .= '<ul class="collapse show list-group list-group-flush">';
        foreach ($categories as $category) {
            $output .= '<li><a  href="javascript:void(0)" id="'. $category['id'] .'" class="text-reset course_catalog_category">'. $category['name'] .'</a>';
            if (!empty($category['children'])) {
                $output .= build_category_tree($category['children']);
            }
            $output .= '</li>';
        }
        $output .= '</ul>';
    }
    return $output;
}
function build_category_hierarchy($parent_id = 0, $categories = []) {
    global $DB;
    $get_course_category_sql = <<<SQL_QUERY
            SELECT c1.id AS category_id, c1.name AS category_name, c2.name AS parent_category_name,
            c1.idnumber AS idnumber
            FROM {course_categories} c1
            LEFT JOIN {course_categories} c2 ON c1.parent = c2.id
            WHERE c1.parent = :parent
            SQL_QUERY;
    $params = [
        'parent' => $parent_id,
    ];
    $course_categories = $DB->get_records_sql($get_course_category_sql, $params);
    foreach ($course_categories as $row) {
        if ($row->idnumber != 'SCERTTRAININGS') {
            $category = [
                'id' => $row->category_id,
                'name' => $row->category_name,
                'parent_name' => $row->parent_category_name,
                'children' => []
            ];
            $children = build_category_hierarchy($row->category_id, []);
            if (!empty($children)) {
                $category['children'] = $children;
            }
            $categories[] = $category;
        }
    }
    return $categories;
}
function traverse_records($childcategory) {
    $result = [];
    foreach ($childcategory as $item) {
        $result[] = $item;
        if (isset($item['children']) && is_array($item['children'])) {
            $result = array_merge($result, traverse_records($item['children']));
        }
    }
    return $result;
}

function get_course_by_category_id($categoryid) {
    global $DB, $CFG;

    $childcategory = build_category_hierarchy($categoryid);

    $categorywithallchilds = traverse_records($childcategory);

    $categoryids = [];
    if (!empty($categorywithallchilds)) {
        $categoryids[] = $categoryid;
        foreach ($categorywithallchilds as $child) {
            $categoryids[] = $child['id'];
        }
    } else {
        $categoryids[] = $categoryid;
    }

    [$insql, $inparams] = $DB->get_in_or_equal($categoryids);
    $get_course_sql = <<<SQL_QUERY
            SELECT c.id, c.fullname, cc.name as cat_name
            FROM {course} c
            JOIN {course_categories} cc ON cc.id = c.category
            WHERE c.category $insql
            SQL_QUERY;

    $courses = $DB->get_records_sql($get_course_sql, $inparams);
    $coursesarr = array();
    if (!empty($courses)) {
        foreach ($courses as $course) {
             /* INTG Customization Start : Adding course rating in all courses in course catalog page */
            $cid = $course->id;
            $block = block_instance('cocoon_course_rating');
            $ratingdata = $block->external_star_rating($cid);
            $courseimgurl = \core_course\external\course_summary_exporter::get_course_image($course);
            if (empty($courseimgurl)) {
                $courseimgurl = $CFG->wwwroot . '/blocks/course_catalog/pix/no-image.png';
            }
            $courseurl = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
            $courselist = [];
            $courselist['course_name'] = $course->fullname;
            $courselist['course_url'] = $courseurl;
            $courselist['course_image'] = $courseimgurl;
            $courselist['category_name'] = $course->cat_name;
            $courselist['course_rating'] = $ratingdata;
            $coursesarr[] = $courselist;
            /* INTG Customization End */
        }
    }
    return $coursesarr;
}
