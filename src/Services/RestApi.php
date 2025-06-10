<?php

namespace CodeZone\Bible\Services;

use function CodeZone\Bible\routes_path;
use function CodeZone\Bible\container;

class RestApi {
    public function __construct() {
        add_action('rest_api_init', [ $this, 'register_routes' ]);
    }

    /**
     * Registers REST API routes defined in routes/api.php
     *
     * @return void
     */
    public function register_routes(): void {
        $routes = include routes_path('api.php');

        foreach ($routes as $route) {
            register_rest_route(
                'codezone/v1',
                $route['route'],
                [
                    'methods'             => $route['method'],
                    'callback'            => [ container()->get($route['callback'][0]), $route['callback'][1] ],
                    'permission_callback' => $route['permission_callback'],
                ]
            );
        }
    }
}
