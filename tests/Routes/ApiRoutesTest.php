<?php

namespace Tests\Routes;

use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    /**
     * @test
     */
    public function it_registers_all_required_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/api.php';

        // Define the controllers we expect to have routes
        $expected_controllers = [
            'CodeZone\Bible\Controllers\LanguageController',
            'CodeZone\Bible\Controllers\BibleController',
            'CodeZone\Bible\Controllers\BibleMediaTypesController',
            'CodeZone\Bible\Controllers\ScriptureController',
            'CodeZone\Bible\Controllers\Settings\AdvancedController',
            'CodeZone\Bible\Controllers\Settings\BibleBrainsFormController',
            'CodeZone\Bible\Controllers\Settings\CustomizationFomController'
        ];

        // Extract the controller classes from the routes
        $registered_controllers = [];
        foreach ( $routes as $route ) {
            if ( isset( $route['callback'] ) && is_array( $route['callback'] ) ) {
                $registered_controllers[] = $route['callback'][0];
            }
        }

        // Assert that all expected controllers have routes
        foreach ( $expected_controllers as $controller ) {
            $this->assertContains(
                $controller,
                $registered_controllers,
                "Controller {$controller} should have at least one route registered in api.php"
            );
        }
    }

    /**
     * @test
     */
    public function it_registers_language_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/api.php';

        // Define the expected routes for LanguageController
        $expected_routes = [
            [
                'method' => 'GET',
                'route' => '/languages',
                'callback' => [ 'CodeZone\Bible\Controllers\LanguageController', 'index' ]
            ],
            [
                'method' => 'GET',
                'route' => '/languages/options',
                'callback' => [ 'CodeZone\Bible\Controllers\LanguageController', 'options' ]
            ],
            [
                'method' => 'GET',
                'route' => '/languages/(?P<id>[\d]+)',
                'callback' => [ 'CodeZone\Bible\Controllers\LanguageController', 'show' ]
            ]
        ];

        // Check each expected route
        foreach ( $expected_routes as $expected ) {
            $found = false;
            foreach ( $routes as $route ) {
                if (
                    $route['method'] === $expected['method'] &&
                    $route['route'] === $expected['route'] &&
                    $route['callback'][0] === $expected['callback'][0] &&
                    $route['callback'][1] === $expected['callback'][1]
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                "Route {$expected['method']} {$expected['route']} to {$expected['callback'][0]}::{$expected['callback'][1]} not found"
            );
        }
    }

    /**
     * @test
     */
    public function it_registers_bible_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/api.php';

        // Define the expected routes for BibleController
        $expected_routes = [
            [
                'method' => 'GET',
                'route' => '/bibles',
                'callback' => [ 'CodeZone\Bible\Controllers\BibleController', 'index' ]
            ],
            [
                'method' => 'GET',
                'route' => '/bibles/options',
                'callback' => [ 'CodeZone\Bible\Controllers\BibleController', 'options' ]
            ],
            [
                'method' => 'GET',
                'route' => '/bibles/(?P<id>[\w-]+)',
                'callback' => [ 'CodeZone\Bible\Controllers\BibleController', 'show' ]
            ]
        ];

        // Check each expected route
        foreach ( $expected_routes as $expected ) {
            $found = false;
            foreach ( $routes as $route ) {
                if (
                    $route['method'] === $expected['method'] &&
                    $route['route'] === $expected['route'] &&
                    $route['callback'][0] === $expected['callback'][0] &&
                    $route['callback'][1] === $expected['callback'][1]
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                "Route {$expected['method']} {$expected['route']} to {$expected['callback'][0]}::{$expected['callback'][1]} not found"
            );
        }
    }

    /**
     * @test
     */
    public function it_registers_bible_media_types_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/api.php';

        // Define the expected routes for BibleMediaTypesController
        $expected_routes = [
            [
                'method' => 'GET',
                'route' => '/bibles/media-types',
                'callback' => [ 'CodeZone\Bible\Controllers\BibleMediaTypesController', 'index' ]
            ],
            [
                'method' => 'GET',
                'route' => '/bibles/media-types/options',
                'callback' => [ 'CodeZone\Bible\Controllers\BibleMediaTypesController', 'options' ]
            ]
        ];

        // Check each expected route
        foreach ( $expected_routes as $expected ) {
            $found = false;
            foreach ( $routes as $route ) {
                if (
                    $route['method'] === $expected['method'] &&
                    $route['route'] === $expected['route'] &&
                    $route['callback'][0] === $expected['callback'][0] &&
                    $route['callback'][1] === $expected['callback'][1]
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                "Route {$expected['method']} {$expected['route']} to {$expected['callback'][0]}::{$expected['callback'][1]} not found"
            );
        }
    }

    /**
     * @test
     */
    public function it_registers_scripture_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/api.php';

        // Define the expected routes for ScriptureController
        $expected_routes = [
            [
                'method' => 'GET',
                'route' => '/scripture',
                'callback' => [ 'CodeZone\Bible\Controllers\ScriptureController', 'index' ]
            ]
        ];

        // Check each expected route
        foreach ( $expected_routes as $expected ) {
            $found = false;
            foreach ( $routes as $route ) {
                if (
                    $route['method'] === $expected['method'] &&
                    $route['route'] === $expected['route'] &&
                    $route['callback'][0] === $expected['callback'][0] &&
                    $route['callback'][1] === $expected['callback'][1]
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                "Route {$expected['method']} {$expected['route']} to {$expected['callback'][0]}::{$expected['callback'][1]} not found"
            );
        }
    }

    /**
     * @test
     */
    public function it_registers_settings_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/api.php';

        // Define the expected routes for settings controllers
        $expected_routes = [
            [
                'method' => 'POST',
                'route' => '/bible-brains/key',
                'callback' => [ 'CodeZone\Bible\Controllers\Settings\AdvancedController', 'submit' ]
            ],
            [
                'method' => 'POST',
                'route' => '/bible-brains',
                'callback' => [ 'CodeZone\Bible\Controllers\Settings\BibleBrainsFormController', 'submit' ]
            ],
            [
                'method' => 'POST',
                'route' => '/customization',
                'callback' => [ 'CodeZone\Bible\Controllers\Settings\CustomizationFomController', 'submit' ]
            ]
        ];

        // Check each expected route
        foreach ( $expected_routes as $expected ) {
            $found = false;
            foreach ( $routes as $route ) {
                if (
                    $route['method'] === $expected['method'] &&
                    $route['route'] === $expected['route'] &&
                    $route['callback'][0] === $expected['callback'][0] &&
                    $route['callback'][1] === $expected['callback'][1]
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                "Route {$expected['method']} {$expected['route']} to {$expected['callback'][0]}::{$expected['callback'][1]} not found"
            );
        }
    }
}
