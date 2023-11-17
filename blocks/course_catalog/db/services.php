<?php

$functions = array(
    'block_course_catalog_get_course_by_category' => array(
        'classname'   => 'block_course_catalog_external',
        'methodname'  => 'get_courses_by_category_id',
        'classpath'   => 'blocks/course_catalog/externallib.php',
        'description' => 'Get courses by category id',
        'type'        => 'read',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => false
    ),
);