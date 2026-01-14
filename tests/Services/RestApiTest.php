<?php

namespace Tests\Services;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\League\Container\Container;
use CodeZone\Bible\Requests\RestRequest;
use CodeZone\Bible\Services\RestApi;
use Tests\TestCase;
use function CodeZone\Bible\container;
use function CodeZone\Bible\routes_path;

/**
 * @group services
 * @group rest-api
 */
class RestApiTest extends TestCase {
    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();

        // Mock add_action to avoid actually hooking into WordPress
        \Patchwork\redefine('add_action', function () {
            return true;
        });
    }

    /**
     * @test
     */
    public function it_can_be_instantiated() {
        $rest_api = container()->get( RestApi::class );
        $this->assertInstanceOf( RestApi::class, $rest_api );
    }

    /**
     * @test
     */
    public function it_hooks_into_rest_api_init() {
        // Create a flag to track if add_action was called with the right parameters
        $add_action_called_correctly = false;

        // Mock add_action to verify it's called with the right parameters
        \Patchwork\redefine('add_action', function ( $hook, $callback ) use ( &$add_action_called_correctly ) {
            if ( $hook === 'rest_api_init' && $callback[0] instanceof RestApi && $callback[1] === 'register_routes' ) {
                $add_action_called_correctly = true;
            }
            return true;
        });

        // Create a new RestApi instance to trigger the constructor
        $rest_api = new RestApi();

        // Verify add_action was called correctly
        $this->assertTrue( $add_action_called_correctly );
    }

    /**
     * @test
     */
    public function it_registers_routes_from_api_file()
    {
        // Mock routes_path to return a known path
        \Patchwork\redefine('CodeZone\Bible\routes_path', function ( $file ) {
            // Return a mock path that doesn't exist
            return __DIR__ . '/mock_routes/' . $file;
        });

        // Create mock routes
        $mock_routes = [
            [
                'route' => '/test',
                'method' => 'GET',
                'callback' => [ 'TestController', 'index' ],
                'permission_callback' => '__return_true',
            ],
        ];

        // Mock the routes() method
        $rest_api = $this->getMockBuilder( RestApi::class )
            ->setMethods( [ 'routes' ] )
            ->getMock();
        $rest_api->expects( $this->once() )
            ->method( 'routes' )
            ->willReturn( $mock_routes );

        // Create a flag to track if register_rest_route was called
        $register_rest_route_called = false;

        // Mock register_rest_route to verify it's called
        \Patchwork\redefine('register_rest_route', function ( $namespace, $route, $args ) use ( &$register_rest_route_called ) {
            $register_rest_route_called = true;
            return true;
        });

        // Call the method
        $rest_api->register_routes();

        // Verify register_rest_route was called
        $this->assertTrue( $register_rest_route_called );
    }

    /**
     * @test
     */
    public function it_registers_routes_with_correct_parameters() {
        // Create mock routes
        $mock_routes = [
            [
                'route' => '/test',
                'method' => 'GET',
                'callback' => [ 'TestController', 'index' ],
                'permission_callback' => '__return_true',
            ],
        ];

        // Mock the routes() method
        $rest_api = $this->getMockBuilder( RestApi::class )
            ->setMethods( [ 'routes' ] )
            ->getMock();
        $rest_api->expects( $this->once() )
            ->method( 'routes' )
            ->willReturn( $mock_routes );

        // Create variables to track the parameters passed to register_rest_route
        $namespace = null;
        $route = null;
        $args = null;

        // Mock register_rest_route to capture the parameters
        \Patchwork\redefine('register_rest_route', function ( $ns, $rt, $ar ) use ( &$namespace, &$route, &$args ) {
            $namespace = $ns;
            $route = $rt;
            $args = $ar;
            return true;
        });

        // Call the method
        $rest_api->register_routes();

        // Verify the parameters
        $this->assertEquals( RestApi::PATH, $namespace );
        $this->assertEquals( '/test', $route );
        $this->assertEquals( 'GET', $args['methods'] );
        $this->assertIsCallable( $args['callback'] );
        $this->assertEquals( '__return_true', $args['permission_callback'] );
    }

    /**
     * @test
     */
    public function it_calls_controller_method_in_route_callback() {
        // Create a mock from the interface instead of stdClass
        $controller = $this->createMock( TestControllerInterface::class );
        $controller->expects( $this->once() )
            ->method( 'index' )
            ->willReturn( [ 'success' => true ] );

        // Rest of your test remains the same...
        \Patchwork\redefine('CodeZone\Bible\container', function () use ( $controller ) {
            $container = $this->createMock( Container::class );
            $container->method( 'get' )->with( 'TestController' )->willReturn( $controller );
            return $container;
        });


        // Mock routes_path and include as before
        \Patchwork\redefine('CodeZone\Bible\routes_path', function ( $file ) {
            return __DIR__ . '/mock_routes/' . $file;
        });

        $rest_api = $this->getMockBuilder( RestApi::class )
            ->setMethods( [ 'routes' ] )
            ->getMock();
        $rest_api->expects( $this->once() )
            ->method( 'routes' )
            ->willReturn([
                [
                    'route' => '/test',
                    'method' => 'GET',
                    'callback' => [ 'TestController', 'index' ],
                    'permission_callback' => '__return_true',
                ],
            ]);

        // Capture the callback function
        $callback = null;
        \Patchwork\redefine('register_rest_route', function ( $namespace, $route, $args ) use ( &$callback ) {
            $callback = $args['callback'];
            return true;
        });

        // Register the routes to get the callback
        $rest_api->register_routes();

        // Create a mock WP_REST_Request
        $wp_request = $this->createMock( \WP_REST_Request::class );

        // Mock rest_ensure_response to return the response unchanged
        \Patchwork\redefine('rest_ensure_response', function ( $response ) {
            return $response;
        });

        // Call the callback
        $response = $callback( $wp_request );

        // Verify the response
        $this->assertEquals( [ 'success' => true ], $response );
    }

    /**
     * @test
     */
    /**
     * @test
     */
    /**
     * @test
     */
    public function it_handles_exceptions_in_route_callback() {
        // Define a paratext exception for wp_die
        // Mock wp_die to throw our paratext exception
        \Patchwork\redefine('wp_die', function ( $message ) {
            throw new \Exception( esc_attr( $message ) );
        });

        // Create a mock controller that throws an exception
        $controller = $this->createMock( TestControllerInterface::class );
        $controller->method( 'index' )
            ->willThrowException( new BiblePluginException( 'Test exception', 400 ) );

        // Mock container to return the mock controller
        \Patchwork\redefine('CodeZone\Bible\container', function () use ( $controller ) {
            $container = $this->createMock( Container::class );
            $container->method( 'get' )->with( 'TestController' )->willReturn( $controller );
            return $container;
        });

        // Mock routes_path and include as before
        \Patchwork\redefine('CodeZone\Bible\routes_path', function ( $file ) {
            return __DIR__ . '/mock_routes/' . $file;
        });

        $rest_api = $this->getMockBuilder( RestApi::class )
            ->setMethods( [ 'routes' ] )
            ->getMock();
        $rest_api->expects( $this->once() )
            ->method( 'routes' )
            ->willReturn([
                [
                    'route' => '/test',
                    'method' => 'GET',
                    'callback' => [ 'TestController', 'index' ],
                    'permission_callback' => '__return_true',
                ],
            ]);

        // Capture the callback function
        $callback = null;
        \Patchwork\redefine('register_rest_route', function ( $namespace, $route, $args ) use ( &$callback ) {
            $callback = $args['callback'];
            return true;
        });

        // Register the routes to get the callback
        $rest_api->register_routes();

        // Create a mock WP_REST_Request
        $wp_request = $this->createMock( \WP_REST_Request::class );

        // Mock wp_is_json_request to return false to force wp_die path
        \Patchwork\redefine('wp_is_json_request', function () {
            return false;
        });

        // Mock the escaping functions to return the input unchanged
        \Patchwork\redefine( 'esc_html', function ( $text ) {
            return $text;
        } );
        \Patchwork\redefine( 'esc_attr', function ( $text ) {
            return $text;
        } );

        // Expect our paratext exception
        $this->expectException( \Exception::class );
        $this->expectExceptionMessage( 'Test exception' );

        // Call the callback
        $callback( $wp_request );
    }

    /**
     * @test
     */
    public function it_sends_json_error_for_error_responses() {
        // Create a mock controller that returns an error response
        $controller = $this->createMock( TestControllerInterface::class );
        $controller->method( 'index' )->willReturn([
            'error' => 'Test error',
            'code' => 400,
        ]);

        // Mock container to return the mock controller
        \Patchwork\redefine('CodeZone\Bible\container', function () use ( $controller ) {
            $container = $this->createMock( Container::class );
            $container->method( 'get' )->with( 'TestController' )->willReturn( $controller );
            return $container;
        });

        // Mock routes_path and include as before
        \Patchwork\redefine('CodeZone\Bible\routes_path', function ( $file ) {
            return __DIR__ . '/mock_routes/' . $file;
        });

        $rest_api = $this->getMockBuilder( RestApi::class )
            ->setMethods( [ 'routes' ] )
            ->getMock();
        $rest_api->expects( $this->once() )
            ->method( 'routes' )
            ->willReturn([
                [
                    'route' => '/test',
                    'method' => 'GET',
                    'callback' => [ 'TestController', 'index' ],
                    'permission_callback' => '__return_true',
                ],
            ]);

        // Capture the callback function
        $callback = null;
        \Patchwork\redefine('register_rest_route', function ( $namespace, $route, $args ) use ( &$callback ) {
            $callback = $args['callback'];
            return true;
        });

        // Register the routes to get the callback
        $rest_api->register_routes();

        // Create a mock WP_REST_Request
        $wp_request = $this->createMock( \WP_REST_Request::class );

        // Create a flag to track if wp_send_json_error was called
        $wp_send_json_error_called = false;
        $wp_send_json_error_data = null;
        $wp_send_json_error_code = null;

        // Mock wp_send_json_error to verify it's called with the right parameters
        \Patchwork\redefine('wp_send_json_error', function ( $data, $code ) use ( &$wp_send_json_error_called, &$wp_send_json_error_data, &$wp_send_json_error_code ) {
            $wp_send_json_error_called = true;
            $wp_send_json_error_data = $data;
            $wp_send_json_error_code = $code;
            return true;
        });

        // Call the callback
        $callback( $wp_request );

        // Verify wp_send_json_error was called with the right parameters
        $this->assertTrue( $wp_send_json_error_called );
        $this->assertEquals( [ 'error' => 'Test error' ], $wp_send_json_error_data );
        $this->assertEquals( 400, $wp_send_json_error_code );
    }
}

//phpcs:ignore
interface TestControllerInterface {
    public function index();
}
