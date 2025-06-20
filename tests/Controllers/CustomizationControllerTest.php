<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\Settings\CustomizationFomController;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;
use function CodeZone\Bible\get_plugin_option;

/**
 * Class CustomizationControllerTest
 *
 * This class is responsible for testing the Customization settings controller.
 *
 * @test
 */
class CustomizationControllerTest extends TestCase
{
    /**
     * Test that the CustomizationFomController show method returns the expected view.
     * @test
     */
    public function it_shows() {
        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Create the controller
        $controller = new CustomizationFomController();

        // Call the show method
        $response = $controller->show( $request );

        // Assert that the response is a string (view content)
        $this->assertIsString( $response );

        // Assert that the response contains expected content
        $this->assertStringContainsString( 'customization', $response );
    }

    /**
     * Test that the CustomizationFomController validates the submission.
     * @test
     */
    public function it_validates_and_returns_error_if_validation_fails() {
        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return empty values
        $request->method( 'get' )
            ->willReturnMap([
                [ 'color_scheme', null, '' ],
                [ 'colors', null, [] ],
                [ 'translations', null, [] ]
            ]);

        // Add magic property access
        $request->color_scheme = '';
        $request->colors = [];
        $request->translations = [];

        // Create the controller
        $controller = new CustomizationFomController();

        // Call the submit method
        $response = $controller->submit( $request );

        // Assert that the response contains errors
        $this->assertArrayHasKey( 'code', $response );
        $this->assertArrayHasKey( 'errors', $response );
        $this->assertArrayHasKey( 'message', $response );
        $this->assertEquals( 400, $response['code'] );
    }

    /**
     * Test that the CustomizationFomController saves settings and returns success.
     * @test
     */
    public function it_saves_customization_settings_and_returns_success() {
        // Create test data
        $color_scheme = 'light';
        $colors = [
            'accent' => '#3490dc',
            'accent_steps' => 5
        ];
        $translations = [
            'en' => 'English',
            'es' => 'Spanish'
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test data
        $request->method( 'get' )
            ->willReturnMap([
                [ 'color_scheme', null, $color_scheme ],
                [ 'colors', null, $colors ],
                [ 'translations', null, $translations ]
            ]);

        // Add magic property access
        $request->color_scheme = $color_scheme;
        $request->colors = $colors;
        $request->translations = $translations;

        // Create the controller
        $controller = new CustomizationFomController();

        // Call the submit method
        $response = $controller->submit( $request );

        // Assert that the response is an array with success=true
        $this->assertIsArray( $response );
        $this->assertArrayHasKey( 'success', $response );
        $this->assertTrue( $response['success'] );

        // Assert that the settings were saved to the plugin options
        $saved_color_scheme = get_plugin_option( 'color_scheme' );
        $saved_colors = get_plugin_option( 'colors' );
        $saved_translations = get_plugin_option( 'translations' );

        $this->assertEquals( $color_scheme, $saved_color_scheme );
        $this->assertEquals( $colors, $saved_colors );
        $this->assertEquals( $translations, $saved_translations );
    }

    /**
     * Test that the controller formats colors correctly.
     * @test
     */
    public function it_formats_colors_correctly() {
        $controller = new CustomizationFomController();

        // Test with empty input
        $result = $controller->format_colors( null );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'accent', $result );
        $this->assertArrayHasKey( 'accent_steps', $result );

        // Test with legacy format (indexed array)
        $legacy_colors = [ '#3490dc', 5 ];
        $result = $controller->format_colors( $legacy_colors );
        $this->assertEquals( '#3490dc', $result['accent'] );
        $this->assertEquals( 5, $result['accent_steps'] );

        // Test with complete input
        $complete_colors = [
            'accent' => '#ff0000',
            'accent_steps' => 3
        ];
        $result = $controller->format_colors( $complete_colors );
        $this->assertEquals( '#ff0000', $result['accent'] );
        $this->assertEquals( 3, $result['accent_steps'] );
    }
}
