<?php
/**
 * Conditions are used to determine if a group of routes should be registered.
 *
 * Groups are used to register a group of routes with a common URL prefix.
 *
 * Middleware is used to modify requests before they are handled by a controller, or to modify responses before they are returned to the client.
 *
 * Routes are used to bind a URL to a controller.
 *
 * @see https://github.com/thecodezone/wp-router
 */

use CodeZone\Bible\Controllers\Settings\AdvancedController;
use CodeZone\Bible\Controllers\Settings\BibleBrainsFormController;
use CodeZone\Bible\Controllers\Settings\CustomizationFomController;
use CodeZone\Bible\Controllers\Settings\SupportController;

return [
    'general'        => [BibleBrainsFormController::class, 'show'],
    'advanced'       => [AdvancedController::class, 'show'],
    'support'        => [SupportController::class, 'show'],
    'bible_brains_key' => [BibleBrainsFormController::class, 'add_key'],
    'bible'          => [BibleBrainsFormController::class, 'show'],
    'customization'  => [CustomizationFomController::class, 'show'],
];
