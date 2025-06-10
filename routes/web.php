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
 * @var Routes $r
 * @see https://github.com/thecodezone/wp-router
 */

use CodeZone\Bible\CodeZone\Router\FastRoute\Routes;
use CodeZone\Bible\Controllers\Admin\BibleBrainsFormController;
use CodeZone\Bible\Controllers\Admin\CustomizationFomController;
use CodeZone\Bible\Controllers\Admin\SupportController;
use CodeZone\Bible\Controllers\ScriptureController;
use CodeZone\Bible\Controllers\LanguageController;
use CodeZone\Bible\Controllers\BibleMediaTypesController;
use CodeZone\Bible\Controllers\BibleController;
use function CodeZone\Bible\admin_path;

$r->group('api', function ( Routes $r ) {
    $r->get( '/languages', [ LanguageController::class, 'index' ] );
    $r->get( '/languages/options', [ LanguageController::class, 'options' ] );
    $r->get( '/languages/{id}', [ LanguageController::class, 'show' ] );

    $r->get( '/bibles', [ BibleController::class, 'index' ] );
    $r->get( '/bibles/media-types', [ BibleMediaTypesController::class, 'index' ] );
    $r->get( '/bibles/media-types/options', [ BibleMediaTypesController::class, 'options' ] );
    $r->get( '/bibles/options', [ BibleController::class, 'options' ] );
    $r->get( '/bibles/{id}', [ BibleController::class, 'show' ] );

    $r->get( '/scripture', [ ScriptureController::class, 'index' ] );

    $r->middleware([ 'can:manage_options', 'nonce:bible_plugin_nonce' ], function ( Routes $r ) {
        $r->post( '/bible-brains/key', [ BibleBrainsFormController::class, 'validate' ] );
        $r->post( '/bible-brains', [ BibleBrainsFormController::class, 'submit' ] );
        $r->post( '/customization', [ CustomizationFomController::class, 'submit' ] );
    });
});
