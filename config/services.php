<?php

/**
 * @var $config CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface
 */

use CodeZone\Bible\Providers\AdminProvider;
use CodeZone\Bible\Providers\BibleBrainsProvider;
use CodeZone\Bible\Providers\ConfigProvider;
use CodeZone\Bible\Providers\OptionsProvider;
use CodeZone\Bible\Providers\RestApiProvider;
use CodeZone\Bible\Providers\ShortcodeProvider;
use CodeZone\Bible\Providers\TranslationsProvider;
use CodeZone\Bible\Providers\ViewProvider;
use CodeZone\Bible\Providers\AssetProvider;


$config->merge( [
    'services' => [
        'providers' => [
            TranslationsProvider::class,
            OptionsProvider::class,
            ConfigProvider::class,
            BibleBrainsProvider::class,
            AssetProvider::class,
            ShortcodeProvider::class,
            ViewProvider::class,
            RestApiProvider::class,
            AdminProvider::class
        ],
        'tgmpa' => [
            'plugins' => [
                [
                    'name'     => 'Disciple.Tools Dashboard',
                    'slug'     => 'disciple-tools-dashboard',
                    'source'   => 'https://github.com/DiscipleTools/disciple-tools-dashboard/releases/latest/download/disciple-tools-dashboard.zip',
                    'required' => false,
                ],
                [
                    'name'     => 'Disciple.Tools Genmapper',
                    'slug'     => 'disciple-tools-genmapper',
                    'source'   => 'https://github.com/DiscipleTools/disciple-tools-genmapper/releases/latest/download/disciple-tools-genmapper.zip',
                    'required' => true,
                ],
                [
                    'name'     => 'Disciple.Tools Autolink',
                    'slug'     => 'disciple-tools-autolink',
                    'source'   => 'https://github.com/DiscipleTools/disciple-tools-genmapper/releases/latest/download/disciple-tools-autolink.zip',
                    'required' => true,
                ]
            ],
            'config' => [
                'id'           => 'disciple_tools',
                'default_path' => '/partials/plugins/',
                'menu'         => 'tgmpa-install-plugins',
                'parent_slug'  => 'plugins.php',
                'capability'   => 'manage_options',
                'has_notices'  => true,
                'dismissible'  => true,
                'dismiss_msg'  => 'These are recommended plugins to complement your Disciple.Tools system.',
                'is_automatic' => true,
                'message'      => '',
            ],
        ]
    ]
]);
