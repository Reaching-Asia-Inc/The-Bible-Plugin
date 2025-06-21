<?php

namespace Tests\Services;

use CodeZone\Bible\Services\RestRequest;
use Tests\TestCase;
use WP_REST_Request;
use function Patchwork\redefine;

/**
 * @group services
 * @group requests
 */
class RestRequestTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_method_from_wp_rest_request()
    {
        // Create a mock WP_REST_Request
        $wp_request = $this->createMock( WP_REST_Request::class );
        $wp_request->method( 'get_method' )
            ->willReturn( 'POST' );

        // Create RestRequest with the mock
        $request = new RestRequest( $wp_request );

        // Test method
        $this->assertEquals( 'POST', $request->method() );
    }

    /**
     * @test
     */
    public function it_gets_url_params_from_wp_rest_request()
    {
        // Create a mock WP_REST_Request
        $wp_request = $this->createMock( WP_REST_Request::class );
        $wp_request->method( 'get_url_params' )
            ->willReturn( [ 'id' => '123', 'slug' => 'test-slug' ] );

        // Create RestRequest with the mock
        $request = new RestRequest( $wp_request );

        // Test all_url_params method
        $this->assertEquals( [ 'id' => '123', 'slug' => 'test-slug' ], $request->all_url_params() );
    }

    /**
     * @test
     */
    public function it_gets_post_params_from_wp_rest_request_for_json_request()
    {
        // Create a mock WP_REST_Request
        $wp_request = $this->createMock( WP_REST_Request::class );
        $wp_request->method( 'get_json_params' )
            ->willReturn( [ 'name' => 'John', 'email' => 'john@example.com' ] );

        // Create RestRequest with the mock
        $request = new RestRequest( $wp_request );

        // Mock wp_is_json_request to return true
        redefine('wp_is_json_request', function () {
            return true;
        });

        // Test all_post method
        $this->assertEquals( [ 'name' => 'John', 'email' => 'john@example.com' ], $request->all_post() );
    }

    /**
     * @test
     */
    public function it_gets_post_params_from_wp_rest_request_for_non_json_request()
    {
        // Create a mock WP_REST_Request
        $wp_request = $this->createMock( WP_REST_Request::class );
        $wp_request->method( 'get_body_params' )
            ->willReturn( [ 'name' => 'John', 'email' => 'john@example.com' ] );

        // Create RestRequest with the mock
        $request = new RestRequest( $wp_request );

        // Mock wp_is_json_request to return false
        redefine('wp_is_json_request', function () {
            return false;
        });

        // Test all_post method
        $this->assertEquals( [ 'name' => 'John', 'email' => 'john@example.com' ], $request->all_post() );
    }

    /**
     * @test
     */
    public function it_gets_query_params_from_wp_rest_request()
    {
        // Create a mock WP_REST_Request
        $wp_request = $this->createMock( WP_REST_Request::class );
        $wp_request->method( 'get_query_params' )
            ->willReturn( [ 'search' => 'test', 'page' => '1' ] );

        // Create RestRequest with the mock
        $request = new RestRequest( $wp_request );

        // Test all_get method
        $this->assertEquals( [ 'search' => 'test', 'page' => '1' ], $request->all_get() );
    }
}
