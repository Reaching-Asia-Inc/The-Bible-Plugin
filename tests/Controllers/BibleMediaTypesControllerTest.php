<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\BibleMediaTypesController;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;
use function CodeZone\Bible\container;

class BibleMediaTypesControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_media_types()
    {
        // Create test data
        $expected_data = [
            'data' => [
                'text',
                'audio',
                'video'
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Mock the Bibles service and media_types method
        $media_types = $this->createMock( \stdClass::class );
        $media_types->method( 'json' )
            ->willReturn( $expected_data );

        $bibles = $this->createMock( Bibles::class );
        $bibles->method( 'media_types' )
            ->willReturn( $media_types );

        // Mock the container to return our mock Bibles service
        $container = container();
        $container->singleton(Bibles::class, function () use ( $bibles ) {
            return $bibles;
        });

        // Create the controller
        $controller = new BibleMediaTypesController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains the expected data
        $this->assertEquals( $expected_data, $response );
    }

    /**
     * @test
     */
    public function it_returns_media_types_as_options()
    {
        // Create test data
        $expected_options = [
            'data' => [
                [
                    'value' => 'text',
                    'itemText' => 'Text'
                ],
                [
                    'value' => 'audio',
                    'itemText' => 'Audio'
                ],
                [
                    'value' => 'video',
                    'itemText' => 'Video'
                ]
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Mock the Bibles service
        $bibles = $this->createMock( Bibles::class );
        $bibles->method( 'media_type_options' )
            ->willReturn( $expected_options );

        // Mock the container to return our mock Bibles service
        $container = container();
        $container->singleton(Bibles::class, function () use ( $bibles ) {
            return $bibles;
        });

        // Create the controller
        $controller = new BibleMediaTypesController();

        // Call the options method
        $response = $controller->options( $request );

        // Assert that the response contains the expected options
        $this->assertEquals( $expected_options, $response );
    }
}
