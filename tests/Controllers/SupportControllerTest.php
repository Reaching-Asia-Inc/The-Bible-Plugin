<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\Settings\SupportController;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;

class SupportControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_support_view()
    {
        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Create the controller
        $controller = new SupportController();

        // Call the show method
        $response = $controller->show( $request );

        // Assert that the response is a string (view content)
        $this->assertIsString( $response );

        // Assert that the response contains expected content
        $this->assertStringContainsString( 'support', $response );
    }
}
