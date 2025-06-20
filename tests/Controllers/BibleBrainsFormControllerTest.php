<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\Settings\BibleBrainsFormController;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;
use function CodeZone\Bible\container;

class BibleBrainsFormControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_validates_and_returns_error_if_validation_fails()
    {
        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return empty array for languages
        $request->method( 'get' )
            ->willReturnMap([
                [ 'languages', [], [] ]
            ]);

        // Create the controller
        $controller = new BibleBrainsFormController();

        // Call the submit method
        $response = $controller->submit( $request );

        // Assert that the response is a WP_Error
        $this->assertInstanceOf( \WP_Error::class, $response );

        // Assert that the error code is 'validation_error'
        $this->assertEquals( 'validation_error', $response->get_error_code() );

        // Assert that the error data contains 'languages'
        $this->assertArrayHasKey( 'languages', $response->get_error_data() );
    }

    /**
     * @test
     */
    public function it_saves_languages_and_returns_success()
    {
        // Create test data
        $languages = [
            [
                'itemText'    => 'English',
                'value'       => 'eng',
                'bibles'      => 'ENGKJV',
                'media_types' => 'audio,video,text',
                'is_default'  => true
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test languages
        $request->method( 'get' )
            ->willReturnMap([
                [ 'languages', [], $languages ]
            ]);

        // Create the controller
        $controller = new BibleBrainsFormController();

        // Call the submit method
        $response = $controller->submit( $request );

        // Assert that the response is an array with success=true
        $this->assertIsArray( $response );
        $this->assertArrayHasKey( 'success', $response );
        $this->assertTrue( $response['success'] );

        // Assert that the languages were saved to the plugin options
        $saved_languages = \CodeZone\Bible\get_plugin_option( 'languages' );
        $this->assertEquals( $languages, $saved_languages );
    }
}
