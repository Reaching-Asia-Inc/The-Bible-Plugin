<?php

/**
 * @var $config CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface
 */

use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use function CodeZone\Bible\api_url;
use function CodeZone\Bible\config;
use function CodeZone\Bible\container;
use function CodeZone\Bible\plugin_path;

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
        'javascript_globals' => function () {
           return [
               'translations' => [
                   // Reader
                   "Books"                                                                        => _x( "Books", 'reader', 'bible-plugin' ),
                   'Copy'                                                                         => _x( 'Copy', 'reader', 'bible-plugin' ),
                   "Copied successfully."                                                         => _x( "Copied successfully.", 'reader', 'bible-plugin' ),
                   'Language'                                                                     => _x( 'Language', 'reader', 'bible-plugin' ),
                   "Languages"                                                                    => _x( "Languages", 'reader', 'bible-plugin' ),
                   'Link'                                                                         => _x( 'Link', 'reader', 'bible-plugin' ),
                   'Old Testament'                                                                => _x( 'Old Testament', 'reader', 'bible-plugin' ),
                   'Media Types'                                                                  => _x( 'Media Types', 'reader', 'bible-plugin' ),
                   'New Testament'                                                                => _x( 'New Testament', 'reader', 'bible-plugin' ),
                   "Search"                                                                       => _x( "Search", 'reader', 'bible-plugin' ),
                   'Selection'                                                                    => _x( 'Selection', 'reader', 'bible-plugin' ),
                   'Translation'                                                                  => _x( 'Translation', 'reader', 'bible-plugin' ),
                   "Loading"                                                                      => _x( "Loading", 'reader', 'bible-plugin' ),
                   'Text'                                                                         => _x( 'Text', 'reader', 'bible-plugin' ),
                   'Selected'                                                                     => _x( 'Selected', 'reader', 'bible-plugin' ),
                   'Share'                                                                        => _x( 'Share', 'reader', 'bible-plugin' ),

                   //Admin
                   'Note that some bible versions do not support all media types.'                => __( 'Note that some bible versions do not support all media types.', 'bible-plugin' ),
                   'Select the bible language you would like to make available.'                  => __( 'Select the bible language you would like to make available.', 'bible-plugin' ),
                   'Select the bible version you would like to make available for this language.' => __( 'Select the bible version you would like to make available for this language.', 'bible-plugin' ),
                   'Add Language'                                                                 => __( 'Add Language', 'bible-plugin' ),
                   'Default Language?'                                                            => __( 'Default Language?', 'bible-plugin' ),
                   'Make this the default language.'                                              => __( 'Make this the default language.', 'bible-plugin' ),
                   'Bible Version'                                                                => __( 'Bible Version', 'bible-plugin' ),
               ],
               'apiUrl' => api_url(),
               'nonce' => wp_create_nonce( 'wp_rest' ),
               'mediaTypes' => container()->get( MediaTypes::class )->all(),
           ];
        },
        'manifest_dir' => plugin_path( '/dist' )
    ]
] );
