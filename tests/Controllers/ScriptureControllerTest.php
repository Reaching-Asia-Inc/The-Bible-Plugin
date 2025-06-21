<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\ScriptureController;
use Tests\TestCase;
use CodeZone\Bible\Services\Request;

/**
 * @group controllers
 * @group biblebrains
 * @group scripture
 */
class ScriptureControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_validates_and_returns_error_if_validation_fails()
    {
        // Create a mock Request object
        $request = $this->createMock( Request::class );

        // Configure the mock to return empty reference
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'reference' => null,
                'video' => false
            ]);

        // Create the controller
        $controller = new ScriptureController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains error information
        $this->assertArrayHasKey( 'code', $response );
        $this->assertArrayHasKey( 'message', $response );
        $this->assertArrayHasKey( 'errors', $response );
        $this->assertEquals( 400, $response['code'] );
    }

    /**
     * @test
     */
    public function it_returns_scripture_content()
    {
        // Create test data
        $reference = 'John 3:16';

        // Create a mock Request object
        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();


        // Configure the mock to return the test reference
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'reference' => $reference,
                'video' => false
            ]);

        $controller = new ScriptureController();

        // Call the index method
        $response = $controller->index( $request );

        $this->assertIsArray( $response['media']['video']['content']['data'] );
        $this->assertGreaterThan( 0, count( $response['media']['video']['content']['data'] ) );

        foreach ( $response['media']['video']['content']['data'] as $content ) {
            $this->assertArrayNotHasKey( 'files', $content, 'Unexpected "files" key found in video content.' );
        }
    }

    /**
     * @test
     */
    public function it_hydrates_content_with_video_when_requested()
    {
        // Create test data
        $reference = 'John 3:16';

        // Create a mock Request object
        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();

        // Configure the mock to return the test reference with video=true
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'reference' => $reference,
                'video' => true
            ]);

        // Create the controller
        $controller = new ScriptureController();

        // Call the index method
        $response = $controller->index( $request );

        $this->assertIsArray( $response['media']['video']['content']['data'] );
        $this->assertGreaterThan( 0, count( $response['media']['video']['content']['data'] ) );

        foreach ( $response['media']['video']['content']['data'] as $content ) {
            $this->assertArrayHasKey( 'files', $content );
            $this->assertIsArray( $content['files'] );
            $this->assertGreaterThan( 0, count( $content['files'] ) ); // Note: count here, not just the array itself

            foreach ( $content['files'] as $video ) {
                // You can add more assertions here if needed
                // Check that required keys exist
                $this->assertArrayHasKey( 'bandwidth', $video );
                $this->assertArrayHasKey( 'resolution', $video );
                $this->assertArrayHasKey( 'codecs', $video );
                $this->assertArrayHasKey( 'url', $video );

                // Check that URL is a valid URL
                $this->assertNotEmpty( $video['url'], 'Video URL is empty' );
                $this->assertTrue(
                    filter_var( $video['url'], FILTER_VALIDATE_URL ) !== false,
                    'Video URL is not a valid URL: ' . $video['url']
                );
            }
        }
    }

    /**
     * @test
     */
    public function it_handles_exceptions_gracefully()
    {
        // Create test data
        $reference = 'Invalid Reference';

        // Create a mock Request object
        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();

        // Configure the mock to return the test reference
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'reference' => $reference,
                'video' => true
            ]);

        // Create the controller
        $controller = new ScriptureController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains error information
        $this->assertArrayHasKey( 'code', $response );
        $this->assertArrayHasKey( 'error', $response );
        $this->assertEquals( 500, $response['code'] );
    }
}
