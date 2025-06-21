<?php

/**
 * @var $config CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface
 */

$config->merge( [
    'options' => [
        'prefix' => 'bible_plugin',
        'defaults' => [
            'bible_brains_key' => '',
            'languages'        => [
                [
                    'bibles'      => 'ENGESV',
                    'media_types' => 'text,audio-video,video',
                    'itemText'    => 'English',
                    'value'       => 'eng'
                ]
            ],
            'language'         => '6414',
            'color_scheme'     => 'light',
            'translations'     => [],
            'colors'           => [
                'accent'       => '#29ac9d',
                'accent_steps' => [
                    100  => 'rgb(10, 41, 38)',
                    200  => 'rgb(14, 60, 55)',
                    300  => 'rgb(19, 79, 72)',
                    400  => 'rgb(23, 97, 89)',
                    500  => 'rgb(28, 116, 106)',
                    600  => 'rgb(32, 135, 123)',
                    700  => 'rgb(37, 153, 140)',
                    800  => 'rgb(41, 172, 157)',
                    900  => 'rgb(49, 204, 187)',
                    1000 => 'rgb(80, 213, 198)',
                    1100 => 'rgb(113, 221, 209)',
                    1200 => 'rgb(145, 229, 219)',
                    1300 => 'rgb(178, 237, 230)',
                    1400 => 'rgb(210, 244, 240)',
                    1500 => 'rgb(243, 252, 251)'
                ]
            ]
        ],
    ]
] );
