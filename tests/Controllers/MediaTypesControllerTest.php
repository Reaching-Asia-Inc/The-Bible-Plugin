<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\MediaTypesController;
use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use CodeZone\Bible\Services\Request;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group controllers
 * @group settings
 * @group biblebrains
 * @group mediatypes
 */
class MediaTypesControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_media_types()
    {
        // Create the controller
        $controller = new MediaTypesController();

        // Call the index method
        $response = $controller->index( new Request() );

        $media_types = container()->get( MediaTypes::class );
        // Assert that the response contains the expected data
        $this->assertEquals( $media_types->all(), $response );
        $this->assertGreaterThan( 0, count( $response ) );
    }

    /**
     * @test
     */
    public function it_returns_media_types_as_options()
    {
        // Create test data
        // Create the controller
        $controller = new MediaTypesController();

        // Call the index method
        $response = $controller->options( new Request() );

        $media_types = container()->get( MediaTypes::class );
        // Assert that the response contains the expected data
        $this->assertEquals( $media_types->options(), $response );
        $this->assertGreaterThan( 0, count( $response ) );
    }
}
