<?php

use CodeZone\Bible\Controllers\Settings\AdvancedController;
use CodeZone\Bible\Controllers\Settings\BibleBrainsFormController;
use CodeZone\Bible\Controllers\Settings\CustomizationFomController;
use CodeZone\Bible\Controllers\ScriptureController;
use CodeZone\Bible\Controllers\LanguageController;
use CodeZone\Bible\Controllers\BibleMediaTypesController;
use CodeZone\Bible\Controllers\BibleController;

return [
    // Public GET routes
    [
        'method'  => 'GET',
        'route'   => '/languages',
        'callback'=> [LanguageController::class, 'index'],
        'permission_callback' => '__return_true',
    ],
    [
        'method'  => 'GET',
        'route'   => '/languages/options',
        'callback'=> [LanguageController::class, 'options'],
        'permission_callback' => '__return_true',
    ],
    [
        'method'  => 'GET',
        'route'   => '/languages/(?P<id>[\d]+)',
        'callback'=> [LanguageController::class, 'show'],
        'permission_callback' => '__return_true',
    ],
    [
        'method'  => 'GET',
        'route'   => '/bibles',
        'callback'=> [BibleController::class, 'index'],
        'permission_callback' => '__return_true',
    ],
    [
        'method'  => 'GET',
        'route'   => '/bibles/options',
        'callback'=> [BibleController::class, 'options'],
        'permission_callback' => '__return_true',
    ],
    [
        'method'  => 'GET',
        'route'   => '/bibles/(?P<id>[\w-]+)',
        'callback'=> [BibleController::class, 'show'],
        'permission_callback' => '__return_true',
    ],
    [
        'method'  => 'GET',
        'route'   => '/bibles/media-types',
        'callback'=> [BibleMediaTypesController::class, 'index'],
        'permission_callback' => '__return_true',
    ],
    [
        'method'  => 'GET',
        'route'   => '/bibles/media-types/options',
        'callback'=> [BibleMediaTypesController::class, 'options'],
        'permission_callback' => '__return_true',
    ],
    [
        'method'  => 'GET',
        'route'   => '/scripture',
        'callback'=> [ScriptureController::class, 'index'],
        'permission_callback' => '__return_true',
    ],
    [
        'method'  => 'POST',
        'route'   => '/bible-brains/key',
        'callback'=> [AdvancedController::class, 'submit'],
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ],
    [
        'method'  => 'POST',
        'route'   => '/bible-brains',
        'callback'=> [BibleBrainsFormController::class, 'submit'],
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ],
    [
        'method'  => 'POST',
        'route'   => '/customization',
        'callback'=> [CustomizationFomController::class, 'submit'],
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ],
];
