<?php


$functions = array(
    'block_advnotifications_get_zone' => array(
        'classname'   => 'block_advnotifications_external',
        'methodname'  => 'get_zone',
        'classpath'   => 'blocks/advnotifications/externallib.php',
        'description' => 'Get all zone related to specific district',
        'type'        => 'read',
        'ajax' => true,
        'capabilities' => '',
    ),
    'block_advnotifications_get_diet' => array(
        'classname'   => 'block_advnotifications_external',
        'methodname'  => 'get_diet',
        'classpath'   => 'blocks/advnotifications/externallib.php',
        'description' => 'Get all diet related to specific zone',
        'type'        => 'read',
        'ajax' => true,
        'capabilities' => '',
    ),
    'block_advnotifications_get_school' => array(
        'classname'   => 'block_advnotifications_external',
        'methodname'  => 'get_school',
        'classpath'   => 'blocks/advnotifications/externallib.php',
        'description' => 'Get all schools related to specific diet',
        'type'        => 'read',
        'ajax' => true,
        'capabilities' => '',
    ),
);
