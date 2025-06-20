<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\ScriptureController;
use CodeZone\Bible\Services\BibleBrains\Scripture;
use CodeZone\Bible\Services\BibleBrains\Video;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;
use function CodeZone\Bible\container;

class ScriptureControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_validates_and_returns_error_if_validation_fails()
    {
        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return empty reference
        $request->method( 'get' )
            ->willReturnMap([
                [ 'reference', null, '' ],
                [ 'video', null, false ]
            ]);

        // Add magic property access
        $request->reference = '';
        $request->video = false;

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
        $expected_content = [
            'reference' => 'John 3:16',
            'content' => 'For God so loved the world...'
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test reference
        $request->method( 'get' )
            ->willReturnMap([
                [ 'reference', null, $reference ],
                [ 'video', null, false ]
            ]);

        // Add magic property access
        $request->reference = $reference;
        $request->video = false;

        // Mock the Scripture service
        $scripture = $this->createMock( Scripture::class );
        $scripture->method( 'by_reference' )
            ->with( $reference )
            ->willReturn( $expected_content );

        // Mock the container to return our mock Scripture service
        $container = container();
        $container->singleton(Scripture::class, function () use ( $scripture ) {
            return $scripture;
        });

        // Create the controller
        $controller = new ScriptureController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains the expected content
        $this->assertEquals( $expected_content, $response );
    }

    /**
     * @test
     */
    public function it_hydrates_content_with_video_when_requested()
    {
        // Create test data
        $reference = 'John 3:16';
        $content = [
            'reference' => 'John 3:16',
            'content' => 'For God so loved the world...'
        ];
        $expected_content = [
            'reference' => 'John 3:16',
            'content' => 'For God so loved the world...',
            'video' => 'https://example.com/video.mp4'
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test reference with video=true
        $request->method( 'get' )
            ->willReturnMap([
                [ 'reference', null, $reference ],
                [ 'video', null, true ]
            ]);

        // Add magic property access
        $request->reference = $reference;
        $request->video = true;

        // Mock the Scripture service
        $scripture = $this->createMock( Scripture::class );
        $scripture->method( 'by_reference' )
            ->with( $reference )
            ->willReturn( $content );

        // Mock the Video service
        $video = $this->createMock( Video::class );
        $video->method( 'hydrate_content' )
            ->with( $content )
            ->willReturn( $expected_content );

        // Mock the container to return our mock services
        $container = container();
        $container->singleton(Scripture::class, function () use ( $scripture ) {
            return $scripture;
        });
        $container->singleton(Video::class, function () use ( $video ) {
            return $video;
        });

        // Create the controller
        $controller = new ScriptureController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains the expected content with video
        $this->assertEquals( $expected_content, $response );
    }

    /**
     * @test
     */
    public function it_handles_exceptions_gracefully()
    {
        // Create test data
        $reference = 'Invalid Reference';

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test reference
        $request->method( 'get' )
            ->willReturnMap([
                [ 'reference', null, $reference ],
                [ 'video', null, false ]
            ]);

        // Add magic property access
        $request->reference = $reference;
        $request->video = false;

        // Mock the Scripture service to throw an exception
        $scripture = $this->createMock( Scripture::class );
        $scripture->method( 'by_reference' )
            ->with( $reference )
            ->willThrowException( new \Exception( 'Invalid reference' ) );

        // Mock the container to return our mock Scripture service
        $container = container();
        $container->singleton(Scripture::class, function () use ( $scripture ) {
            return $scripture;
        });

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
