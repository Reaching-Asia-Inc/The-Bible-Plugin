<?php

/**
 * @var $config CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface
 */

use function CodeZone\Bible\config;
use function CodeZone\Bible\plugin_path;
use function CodeZone\Bible\route_url;

$config->merge( [
    'assets' => [
        'allowed_styles' => [
            'bible-plugin',
            'bible-plugin-admin',
        ],
        'allowed_scripts' =>[
            'bible-plugin',
            'bible-plugin-admin',
        ],
        'javascript_global_scope' => '$tbp',
        'manifest_dir' => plugin_path( '/dist' )
    ]
] );
