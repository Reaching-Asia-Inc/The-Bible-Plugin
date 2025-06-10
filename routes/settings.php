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

$r->group( admin_path('admin.php'), function ( Routes $r ) {
    $r->get('?page=bible-plugin', [
        BibleBrainsFormController::class,
        'show',
    ]);
    $r->get( '?page=bible-plugin&tab=bible_brains_key', [ BibleBrainsFormController::class, 'add_key' ] );
    $r->get( '?page=bible-plugin&tab=support', [ SupportController::class, 'show' ] );
    $r->get('?page=bible-plugin&tab=bible', [
        BibleBrainsFormController::class,
        'show',
    ]);
    $r->get( '?page=bible-plugin&tab=customization', [ CustomizationFomController::class, 'show' ] );
});
