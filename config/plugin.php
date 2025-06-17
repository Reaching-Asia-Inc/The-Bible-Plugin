<?php

/**
 * @var $config CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface
 */
$config->merge( [
    'plugin' => [
        'text_domain' => 'bible-plugin',
        'nonce_name' => 'bible_plugin_nonce',
        'dt_version' => 1.19,
        'paths' => [
            'src' => 'src',
            'resources' => 'resources',
            'routes' => 'routes',
            'views' => 'resources/views',
        ]
    ]
]);
