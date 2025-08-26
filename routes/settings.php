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
use CodeZone\Bible\Controllers\Settings\BibleFormController;
use CodeZone\Bible\Controllers\Settings\CustomizationFomController;
use CodeZone\Bible\Controllers\Settings\SupportController;
use CodeZone\Bible\Controllers\Settings\USXBiblesController;

return [
    'general'        => [ BibleFormController::class, 'show' ],
    'advanced'       => [ AdvancedController::class, 'show' ],
    'support'        => [ SupportController::class, 'show' ],
    'bible_brains_key' => [ BibleFormController::class, 'add_key' ],
    'bible'          => [ BibleFormController::class, 'show' ],
    'customization'  => [ CustomizationFomController::class, 'show' ],

    // Custom Bibles settings tab and ajax-like handlers
    'usx_bibles'        => [ USXBiblesController::class, 'show' ],
    'usx_bibles_upload' => [ USXBiblesController::class, 'upload' ],
    'usx_bibles_delete' => [ USXBiblesController::class, 'delete' ],
    'usx_bibles_update' => [ USXBiblesController::class, 'update' ],
    'usx_language_search' => [ USXBiblesController::class, 'language_search' ],
];
