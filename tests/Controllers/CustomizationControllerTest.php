<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\Settings\CustomizationFomController;
use CodeZone\Bible\Services\Request;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;
use function CodeZone\Bible\get_plugin_option;

/**
 * @group controllers
 * @group settings
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
        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();

        // Configure the mock to return empty values
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'color_scheme' => '',
                'colors' => [],
                'translations' => []
            ]);

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
            'accent_steps' => [
                100  => 'rgb(10, 41, 1)',
                200  => 'rgb(14, 60, 2)',
                300  => 'rgb(19, 79, 72)',
                400  => 'rgb(23, 97, 89)',
                500  => 'rgb(28, 116, 106)',
                600  => 'rgb(32, 135, 123)',
                700  => 'rgb(37, 153, 140)',
                800  => 'rgb(41, 172, 157)',
                900  => 'rgb(49, 204, 187)',
                1000 => 'rgb(80, 213, 198)',
                1100 => 'rgb(113, 221, 209)',
                1200 => 'rgb(145, 229, 219)',
                1300 => 'rgb(178, 237, 230)',
                1400 => 'rgb(210, 244, 240)',
                1500 => 'rgb(243, 252, 251)'
            ]
        ];
        $translations = [
            'en' => 'English',
            'es' => 'Spanish'
        ];

        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();

        // Configure the mock to return empty values
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'color_scheme' => $color_scheme,
                'colors' => $colors,
                'translations' => $translations
            ]);

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
