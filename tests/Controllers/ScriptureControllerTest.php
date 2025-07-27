<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\ScriptureController;
use CodeZone\Bible\Requests\Request;
use Tests\TestCase;

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

        $this->assertIsArray( $response['media'] );
        $this->assertGreaterThan( 0, count( $response['media'] ) );

        foreach ( $response['media'] as $content ) {
            $this->assertArrayNotHasKey( 'files', $content, 'Unexpected "files" key found in video content.' );
        }
    }

    /**
     * @test
     */
    public function it_handles_exceptions_gracefully()
    {
        // Create test data
        $reference = 'Invalid References';

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
