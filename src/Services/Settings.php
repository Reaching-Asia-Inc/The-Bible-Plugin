<?php

namespace CodeZone\Bible\Services;

use CodeZone\Bible\CodeZone\WPSupport\Router\RouteInterface;
use CodeZone\Bible\Psr\Http\Message\ServerRequestInterface;
use function CodeZone\Bible\container;
use function CodeZone\Bible\namespace_string;
use function CodeZone\Bible\routes_path;

/**
 * Class settings
 *
 * The Settings class is responsible for adding the
 * settings page to the WordPress admin area.
 * @see https://developer.wordpress.org/reference/functions/add_submenu_page/
 */
class Settings {

    /**
     * Register the admin menu.
     *
     * @return void
     */
    public function __construct()
    {
        add_action( 'admin_menu', [ $this, 'register_menu' ], 99 );
    }

    /**
     * Register the admin menu
     *
     * @return void
     * @see https://developer.wordpress.org/reference/functions/add_submenu_page/
     */
    public function register_menu(): void {
        $menu = add_menu_page( 'dt_extensions',
            __( 'The Bible Plugin', 'bible-plugin' ),
            __( 'The Bible Plugin', 'bible-plugin' ),
            'manage_options',
            'bible-plugin',
            '',
            'dashicons-book-alt',
        );

        add_submenu_page(
            'bible-plugin',
            __( 'The Bible Plugin', 'bible-plugin' ),
            __( 'The Bible Plugin', 'bible-plugin' ),
            'manage_options',
            'bible-plugin',
            [ $this, 'dispatch_routes' ]
        );

        add_filter( namespace_string( 'settings_tabs' ), function ( $menu ) {
            $menu[] = [
                'label' => __( 'Biblical Text Setup', 'bible-plugin' ),
                'tab'   => 'bible'
            ];
            $menu[] = [
                'label' => __( 'Customization', 'bible-plugin' ),
                'tab'   => 'customization'
            ];
            $menu[] = [
                'label' => __( 'Support', 'bible-plugin' ),
                'tab'   => 'support',
            ];

            return $menu;
        }, 10, 1 );

        add_action( 'load-' . $menu, [ $this, 'load' ] );
    }

    /**
     * Loads the necessary scripts and styles for the admin area.
     *
     * This method adds an action hook to enqueue the necessary JavaScript when on the admin area.
     * The JavaScript files are enqueued using the `admin_enqueue_scripts` action hook.
     *
     * @return void
     */
    public function load(): void
    {
        container()->get( Assets::class )->enqueue();
    }

    /**
     * Register the admin router.
     *
     * @return void
     */
    public function dispatch_routes(): void {
        $routes = routes_path( 'settings.php' );

        $route = container()->get( RouteInterface::class );
        $route->file( routes_path( 'settings.php' ) )
            ->resolve();
    }
}
