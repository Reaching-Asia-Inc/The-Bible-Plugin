<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\Settings\AdvancedController;
use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Requests\RequestInterface;
use CodeZone\Bible\Services\BibleBrainsKeys;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group controllers
 * @group settings
 */
class AdvancedControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_validates_and_returns_error_if_validation_fails()
    {
        // Create a mock Request object
        $request = $this->getMockBuilder( RequestInterface::class )
            ->getMock();

        // Configure the mock to return values for bible_brains_key
        $request->expects( $this->any() )
            ->method( 'get' )
            ->willReturnMap([
                [ 'bible_brains_key', null, 12345 ],
            ]);


        // Create the controller
        $controller = new AdvancedController();

        // Call the submit method
        $response = $controller->submit( $request );

        // Assert that the response is a WP_Error
        $this->assertInstanceOf( \WP_Error::class, $response );

        // Assert that the error code is 'validation_error'
        $this->assertEquals( 'validation_error', $response->get_error_code() );

        // Assert that the error data contains 'bible_brains_key'
        $this->assertArrayHasKey( 'bible_brains_key', $response->get_error_data() );
    }

    /**
     * @test
     */
    public function it_saves_key_and_returns_success()
    {
        // Get a valid key from the BibleBrainsApiKeys service
        $keys_service = container()->get( BibleBrainsKeys::class );
        $valid_key = $keys_service->random();

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return values for bible_brains_key
        $request->expects( $this->any() )
            ->method( 'get' )
            ->willReturnMap([
                [ 'bible_brains_key', null, $valid_key ],
            ]);

        // Create the controller
        $controller = new AdvancedController();

        // Call the submit method
        $response = $controller->submit( $request );

        // Assert that the response is an array with success=true
        $this->assertIsArray( $response );
        $this->assertArrayHasKey( 'success', $response );
        $this->assertTrue( $response['success'] );

        // If there's no override, assert that the key was saved to the plugin options
        if ( !defined( 'TBP_BIBLE_BRAINS_KEYS' ) ) {
            $saved_key = \CodeZone\Bible\get_plugin_option( 'bible_brains_key' );
            $this->assertEquals( $valid_key, $saved_key );
        }
    }

}
