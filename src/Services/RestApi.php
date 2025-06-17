<?php

namespace CodeZone\Bible\Services;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use function CodeZone\Bible\routes_path;
use function CodeZone\Bible\container;

/**
 * Class RestApi
 *
 * This class handles registration and processing of REST API routes for the Bible plugin.
 */
class RestApi
{
    const PATH = 'bible-plugin/v1';

    /**
     * Constructor to hook into WordPress REST API initialization.
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Registers REST API routes defined in routes/api.php
     *
     * Loads route definitions from api.php and registers each route with WordPress REST API.
     * Each route is registered with the namespace 'codezone/v1' and includes methods, callback
     * and permission settings.
     *
     * @return void
     */
    public function register_routes(): void
    {
        $routes = include routes_path('api.php');

        foreach ($routes as $route) {
            register_rest_route(
                self::PATH,
                $route['route'],
                [
                    'methods' => $route['method'],
                    'callback' => function (\WP_REST_Request $request) use ($route) {
                        $controller = container()->get($route['callback'][0]);
                        $method = $route['callback'][1];
                        $request = new RestRequest($request);
                        try {
                            $response = $controller->$method($request);
                        } catch (BibleBrainsException $e) {
                            $this->handle_exception($e);
                        }

                        return rest_ensure_response(
                            $response
                        );
                    },
                    'permission_callback' => $route['permission_callback'],
                ]
            );
        }
    }

    /**
     * Handles exceptions thrown during API request processing
     *
     * @param \Exception $e The exception to handle
     * @return array|void Returns error array for JSON requests, calls wp_die() otherwise
     */
    public function handle_exception(\Exception $e)
    {
        if (wp_is_json_request()) {
            wp_send_json([
                'message' => $e->getMessage(),
            ], $e->getCode());
            exit;
        }

        wp_die(
            esc_html($e->getMessage()),
            esc_attr($e->getCode())
        );
    }
}
