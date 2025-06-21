<?php

namespace Tests\Routes;

use Tests\TestCase;

/**
 * @group routes
 * @group settings
 */
class SettingsRoutesTest extends TestCase
{
    /**
     * @test
     */
    public function it_registers_all_required_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/settings.php';

        // Define the controllers we expect to have routes
        $expected_controllers = [
            'CodeZone\Bible\Controllers\Settings\AdvancedController',
            'CodeZone\Bible\Controllers\Settings\BibleBrainsFormController',
            'CodeZone\Bible\Controllers\Settings\CustomizationFomController',
            'CodeZone\Bible\Controllers\Settings\SupportController'
        ];

        // Extract the controller classes from the routes
        $registered_controllers = [];
        foreach ( $routes as $key => $callback ) {
            if ( is_array( $callback ) && isset( $callback[0] ) ) {
                $registered_controllers[] = $callback[0];
            }
        }

        // Assert that all expected controllers have routes
        foreach ( $expected_controllers as $controller ) {
            $this->assertContains(
                $controller,
                $registered_controllers,
                "Controller {$controller} should have at least one route registered in settings.php"
            );
        }
    }

    /**
     * @test
     */
    public function it_registers_bible_brains_form_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/settings.php';

        // Define the expected routes for BibleBrainsFormController
        $expected_routes = [
            'general' => [ 'CodeZone\Bible\Controllers\Settings\BibleBrainsFormController', 'show' ],
            'bible' => [ 'CodeZone\Bible\Controllers\Settings\BibleBrainsFormController', 'show' ],
            'bible_brains_key' => [ 'CodeZone\Bible\Controllers\Settings\BibleBrainsFormController', 'add_key' ]
        ];

        // Check each expected route
        foreach ( $expected_routes as $key => $callback ) {
            $this->assertArrayHasKey(
                $key,
                $routes,
                "Route key '{$key}' should exist in settings.php"
            );

            $this->assertEquals(
                $callback[0],
                $routes[$key][0],
                "Route key '{$key}' should point to controller {$callback[0]}"
            );

            $this->assertEquals(
                $callback[1],
                $routes[$key][1],
                "Route key '{$key}' should call method {$callback[1]} on controller {$callback[0]}"
            );
        }
    }

    /**
     * @test
     */
    public function it_registers_advanced_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/settings.php';

        // Define the expected routes for AdvancedController
        $expected_routes = [
            'advanced' => [ 'CodeZone\Bible\Controllers\Settings\AdvancedController', 'show' ]
        ];

        // Check each expected route
        foreach ( $expected_routes as $key => $callback ) {
            $this->assertArrayHasKey(
                $key,
                $routes,
                "Route key '{$key}' should exist in settings.php"
            );

            $this->assertEquals(
                $callback[0],
                $routes[$key][0],
                "Route key '{$key}' should point to controller {$callback[0]}"
            );

            $this->assertEquals(
                $callback[1],
                $routes[$key][1],
                "Route key '{$key}' should call method {$callback[1]} on controller {$callback[0]}"
            );
        }
    }

    /**
     * @test
     */
    public function it_registers_customization_form_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/settings.php';

        // Define the expected routes for CustomizationFomController
        $expected_routes = [
            'customization' => [ 'CodeZone\Bible\Controllers\Settings\CustomizationFomController', 'show' ]
        ];

        // Check each expected route
        foreach ( $expected_routes as $key => $callback ) {
            $this->assertArrayHasKey(
                $key,
                $routes,
                "Route key '{$key}' should exist in settings.php"
            );

            $this->assertEquals(
                $callback[0],
                $routes[$key][0],
                "Route key '{$key}' should point to controller {$callback[0]}"
            );

            $this->assertEquals(
                $callback[1],
                $routes[$key][1],
                "Route key '{$key}' should call method {$callback[1]} on controller {$callback[0]}"
            );
        }
    }

    /**
     * @test
     */
    public function it_registers_support_controller_routes()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/settings.php';

        // Define the expected routes for SupportController
        $expected_routes = [
            'support' => [ 'CodeZone\Bible\Controllers\Settings\SupportController', 'show' ]
        ];

        // Check each expected route
        foreach ( $expected_routes as $key => $callback ) {
            $this->assertArrayHasKey(
                $key,
                $routes,
                "Route key '{$key}' should exist in settings.php"
            );

            $this->assertEquals(
                $callback[0],
                $routes[$key][0],
                "Route key '{$key}' should point to controller {$callback[0]}"
            );

            $this->assertEquals(
                $callback[1],
                $routes[$key][1],
                "Route key '{$key}' should call method {$callback[1]} on controller {$callback[0]}"
            );
        }
    }

    /**
     * @test
     */
    public function it_has_a_default_route()
    {
        // Load the routes file
        $routes = include __DIR__ . '/../../routes/settings.php';

        // Check that the 'general' route exists (default route)
        $this->assertArrayHasKey(
            'general',
            $routes,
            "Default route key 'general' should exist in settings.php"
        );
    }
}
