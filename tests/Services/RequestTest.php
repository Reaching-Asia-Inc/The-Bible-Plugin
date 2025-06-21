<?php

namespace Tests\Services;

use CodeZone\Bible\Services\Request;
use Tests\TestCase;

/**
 * @group services
 * @group requests
 */
class RequestTest extends TestCase
{
    /**
     * @test
     */
    public function it_initializes_with_superglobals()
    {
        // Save original superglobals
        $original_get = $_GET;
        $original_post = $_POST;
        $original_server = $_SERVER;

        // Set test data
        $_GET = [ 'name' => 'John', 'age' => '30' ];
        $_POST = [ 'email' => 'john@example.com' ];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Create request
        $request = new Request();

        // Test all_get method
        $this->assertEquals( [ 'name' => 'John', 'age' => '30' ], $request->all_get() );

        // Test all_post method
        $this->assertEquals( [ 'email' => 'john@example.com' ], $request->all_post() );

        // Test method
        $this->assertEquals( 'get', $request->method() );

        // Restore original superglobals
        $_GET = $original_get;
        $_POST = $original_post;
        $_SERVER = $original_server;
    }

    /**
     * @test
     */
    public function it_returns_empty_array_for_url_params()
    {
        $request = new Request();
        $this->assertEquals( [], $request->all_url_params() );
    }

    /**
     * @test
     */
    public function it_handles_post_method()
    {
        // Save original superglobals
        $original_get = $_GET;
        $original_post = $_POST;
        $original_server = $_SERVER;

        // Set test data
        $_GET = [ 'name' => 'John' ];
        $_POST = [ 'email' => 'john@example.com' ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Create request
        $request = new Request();

        // Test method
        $this->assertEquals( 'post', $request->method() );

        // Test is_post method
        $this->assertTrue( $request->is_post() );
        $this->assertFalse( $request->is_get() );

        // Test get method with POST data
        $this->assertEquals( 'john@example.com', $request->get( 'email' ) );

        // Restore original superglobals
        $_GET = $original_get;
        $_POST = $original_post;
        $_SERVER = $original_server;
    }

    /**
     * @test
     */
    public function it_handles_get_method()
    {
        // Save original superglobals
        $original_get = $_GET;
        $original_post = $_POST;
        $original_server = $_SERVER;

        // Set test data
        $_GET = [ 'name' => 'John' ];
        $_POST = [ 'email' => 'john@example.com' ];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Create request
        $request = new Request();

        // Test method
        $this->assertEquals( 'get', $request->method() );

        // Test is_get method
        $this->assertTrue( $request->is_get() );
        $this->assertFalse( $request->is_post() );

        // Test get method with GET data
        $this->assertEquals( 'John', $request->get( 'name' ) );

        // Restore original superglobals
        $_GET = $original_get;
        $_POST = $original_post;
        $_SERVER = $original_server;
    }

    /**
     * @test
     */
    public function it_defaults_to_get_method_if_request_method_not_set()
    {
        // Save original superglobals
        $original_server = $_SERVER;

        // Unset REQUEST_METHOD
        unset( $_SERVER['REQUEST_METHOD'] );

        // Create request
        $request = new Request();

        // Test method
        $this->assertEquals( 'get', $request->method() );

        // Restore original superglobals
        $_SERVER = $original_server;
    }
}
