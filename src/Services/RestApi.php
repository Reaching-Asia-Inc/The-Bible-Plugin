<?php

namespace CodeZone\Bible\Services;

use function CodeZone\Bible\routes_path;
use function CodeZone\Bible\container;
use WP_REST_Request;

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
                    'callback'            => function(\WP_REST_Request $request) use ($route) {
                        $controller = container()->get($route['callback'][0]);
                        $method = $route['callback'][1];

                        return rest_ensure_response(
                            $controller->$method(new RestRequest($request))
                        );
                    },
                    'permission_callback' => $route['permission_callback'],
                ]
            );
        }
    }
}
