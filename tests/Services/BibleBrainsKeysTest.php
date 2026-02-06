<?php

namespace Tests\Services;

use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface;
use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\Psr\Http\Message\ResponseInterface;
use CodeZone\Bible\Psr\Http\Message\StreamInterface;
use CodeZone\Bible\Services\BibleBrainsKeys;
use CodeZone\Bible\Services\Cache;
use Tests\TestCase;
use function CodeZone\Bible\plugin_path;

/**
 * @group services
 * @group apikeys
 * @group biblebrains
 */
class BibleBrainsKeysTest extends TestCase {
    /**
     * @var BibleBrainsKeys
     */
    protected $service;

    /**
     * @var OptionsInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $options;

    /**
     * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $client;

    /**
     * @var Cache|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cache;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();

        // Mock the dependencies
        $this->options = $this->createMock( OptionsInterface::class );
        $this->client = $this->createMock( Client::class );
        $this->cache = $this->createMock( Cache::class );

        // Create the service
        $this->service = new BibleBrainsKeys( $this->options, $this->client, $this->cache );
    }

    /**
     * @test
     */
    public function it_can_be_instantiated() {
        $this->assertInstanceOf( BibleBrainsKeys::class, $this->service );
    }

    /**
     * @test
     */
    public function it_generates_key_from_plugin_file() {
        // Mock file_get_contents to return a known value
        \Patchwork\redefine('file_get_contents', function ( $path ) {
            if ( $path === plugin_path( 'bible-plugin.php' ) ) {
                return 'test-plugin-content';
            }
            return file_get_contents( $path );
        });

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass( BibleBrainsKeys::class );
        $method = $reflection->getMethod( 'generate_key' );
        $method->setAccessible( true );

        // Call the method
        $result = $method->invoke( $this->service );

        // Verify the result is the base64 encoded plugin content
        $this->assertEquals( base64_encode( 'test-plugin-content' ), $result );
    }

    /**
     * @test
     */
    public function it_fetches_remote_keys_and_caches_them() {
        // Mock the cache to return null (cache miss)
        $this->cache->method( 'get' )->willReturn( null );

        // Mock the client response
        $response = $this->createMock( ResponseInterface::class );
        $body = $this->createMock( StreamInterface::class );
        $body->method( 'getContents' )->willReturn( json_encode( [ 'key1', 'key2' ] ) );
        $response->method( 'getBody' )->willReturn( $body );

        // Expect the client to be called with correct Authorization header
        $this->client->expects( $this->once() )
            ->method( 'get' )
            ->with(
                $this->equalTo( 'https://thebibleplugin.com/wp-json/bible-plugin/v1/keys' ),
                $this->callback( function ( $options ) {
                    return isset( $options['headers']['Authorization'] ) &&
                           $options['headers']['Authorization'] === base64_encode( file_get_contents( plugin_path( 'bible-plugin.php' ) ) );
                } )
            )
            ->willReturn( $response );

        // Expect the cache to be set with the result
        $this->cache->expects( $this->once() )
            ->method( 'set' )
            ->with(
                $this->anything(),
                $this->equalTo( [ 'key1', 'key2' ] )
            );

        // Call the method
        $result = $this->service->fetch_remote();

        // Verify the result
        $this->assertEquals( [ 'key1', 'key2' ], $result );
    }

    /**
     * @test
     */
    public function it_returns_cached_keys_if_available() {
        // Mock the cache to return a cached value
        $this->cache->method( 'get' )->willReturn( [ 'cached-key1', 'cached-key2' ] );

        // The client should not be called
        $this->client->expects( $this->never() )->method( 'get' );

        // Call the method
        $result = $this->service->fetch_remote();

        // Verify the result is the cached value
        $this->assertEquals( [ 'cached-key1', 'cached-key2' ], $result );
    }

    /**
     * @test
     */
    public function it_throws_exception_for_invalid_response_format() {
        // Mock the cache to return null (cache miss)
        $this->cache->method( 'get' )->willReturn( null );

        // Mock the client response with invalid JSON
        $response = $this->createMock( ResponseInterface::class );
        $body = $this->createMock( StreamInterface::class );
        $body->method( 'getContents' )->willReturn( 'not-json' );
        $response->method( 'getBody' )->willReturn( $body );

        $this->client->method( 'get' )->willReturn( $response );

        // Expect an exception
        $this->expectException( BiblePluginException::class );
        $this->expectExceptionMessage( 'Invalid response format.' );

        // Call the method
        $this->service->fetch_remote();
    }

    /**
     * @test
     */
    public function it_checks_if_remote_keys_exist() {
        // Create a partial mock to mock fetch_remote
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'fetch_remote' ] )
            ->getMock();

        // Test with empty remote keys
        $service->method( 'fetch_remote' )->willReturn( [] );
        $this->assertFalse( $service->has_remote_keys() );

        // Test with remote keys
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'fetch_remote' ] )
            ->getMock();
        $service->method( 'fetch_remote' )->willReturn( [ 'key1' ] );
        $this->assertTrue( $service->has_remote_keys() );
    }

    /**
     * @test
     */
    public function it_returns_override_keys_if_available() {
        // Create a partial mock to mock has_override and get_override
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'has_override', 'get_override' ] )
            ->getMock();

        // Mock has_override to return true
        $service->method( 'has_override' )->willReturn( true );

        // Mock get_override to return override keys
        $service->method( 'get_override' )->willReturn( [ 'override-key1', 'override-key2' ] );

        // Call the method
        $result = $service->all();

        // Verify the result is the override keys
        $this->assertEquals( [ 'override-key1', 'override-key2' ], $result );
    }

    /**
     * @test
     */
    public function it_returns_option_key_if_available_and_no_override() {
        // Create a partial mock to mock has_override, has_option, and get_option
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'has_override', 'has_option', 'get_option' ] )
            ->getMock();

        // Mock has_override to return false
        $service->method( 'has_override' )->willReturn( false );

        // Mock has_option to return true
        $service->method( 'has_option' )->willReturn( true );

        // Mock get_option to return an option key
        $service->method( 'get_option' )->willReturn( 'option-key' );

        // Call the method
        $result = $service->all();

        // Verify the result is an array containing the option key
        $this->assertEquals( [ 'option-key' ], $result );
    }

    /**
     * @test
     */
    public function it_returns_remote_keys_if_no_override_or_option() {
        // Create a partial mock to mock has_override, has_option, and fetch_remote
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'has_override', 'has_option', 'fetch_remote' ] )
            ->getMock();

        // Mock has_override to return false
        $service->method( 'has_override' )->willReturn( false );

        // Mock has_option to return false
        $service->method( 'has_option' )->willReturn( false );

        // Mock fetch_remote to return remote keys
        $service->method( 'fetch_remote' )->willReturn( [ 'remote-key1', 'remote-key2' ] );

        // Call the method
        $result = $service->all();

        // Verify the result is the remote keys
        $this->assertEquals( [ 'remote-key1', 'remote-key2' ], $result );
    }

    /**
     * @test
     */
    public function it_returns_empty_array_if_fetch_remote_throws_exception() {
        // Create a partial mock to mock has_override, has_option, and fetch_remote
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'has_override', 'has_option', 'fetch_remote' ] )
            ->getMock();

        // Mock has_override to return false
        $service->method( 'has_override' )->willReturn( false );

        // Mock has_option to return false
        $service->method( 'has_option' )->willReturn( false );

        // Mock fetch_remote to throw an exception
        $service->method( 'fetch_remote' )->willThrowException( new BiblePluginException( 'Test exception' ) );

        // Call the method
        $result = $service->all();

        // Verify the result is an empty array
        $this->assertEquals( [], $result );
    }

    /**
     * @test
     */
    public function it_returns_random_key() {
        // Create a partial mock to mock all
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'all' ] )
            ->getMock();

        // Mock all to return keys
        $service->method( 'all' )->willReturn( [ 'key1', 'key2', 'key3' ] );

        // Call the method
        $result = $service->random();

        // Verify the result is one of the keys
        $this->assertContains( $result, [ 'key1', 'key2', 'key3' ] );
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_keys_available_for_random() {
        // Create a partial mock to mock all
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'all' ] )
            ->getMock();

        // Mock all to return empty array
        $service->method( 'all' )->willReturn( [] );

        // Call the method
        $result = $service->random();

        // Verify the result is null
        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_checks_if_option_exists() {
        // Mock the options service
        $this->options->method( 'get' )
            ->with( BibleBrainsKeys::OPTION_KEY, false )
            ->willReturn( 'option-value' );

        // Call the method
        $result = $this->service->has_option();

        // Verify the result
        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_returns_option_value() {
        // Mock the options service
        $this->options->method( 'get' )
            ->with( BibleBrainsKeys::OPTION_KEY )
            ->willReturn( 'option-value' );

        // Call the method
        $result = $this->service->get_option();

        // Verify the result
        $this->assertEquals( 'option-value', $result );
    }

    /**
     * @test
     */
    public function it_returns_override_keys_from_constant() {
        // Mock defined and constant value
        \Patchwork\redefine('defined', function ( $name ) {
            return $name === 'TBP_BIBLE_BRAINS_KEYS';
        });

        \Patchwork\redefine('constant', function ( $name ) {
            if ( $name === 'TBP_BIBLE_BRAINS_KEYS' ) {
                return 'override-key1,override-key2';
            }
            return constant( $name );
        });

        // Call the method
        $result = $this->service->get_override();

        // Verify the result
        $this->assertEquals( [ 'override-key1', 'override-key2' ], $result );
    }

    /**
     * @test
     */
    public function it_returns_empty_array_if_constant_not_defined() {
        // Mock defined to return false
        \Patchwork\redefine('defined', function ( $name ) {
            return false;
        });

        // Call the method
        $result = $this->service->get_override();

        // Verify the result
        $this->assertEquals( [], $result );
    }

    /**
     * @test
     */
    public function it_checks_if_override_exists() {
        // Create a partial mock to mock get_override
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'get_override' ] )
            ->getMock();

        // Test with empty override
        $service->method( 'get_override' )->willReturn( [] );
        $this->assertFalse( $service->has_override() );

        // Test with override
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'get_override' ] )
            ->getMock();
        $service->method( 'get_override' )->willReturn( [ 'override-key' ] );
        $this->assertTrue( $service->has_override() );
    }

    /**
     * @test
     */
    public function it_checks_if_field_is_readonly() {
        // Create a partial mock to mock has_override
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'has_override' ] )
            ->getMock();

        // Test with override (field should be readonly)
        $service->method( 'has_override' )->willReturn( true );
        $this->assertTrue( $service->is_field_readonly() );

        // Test without override (field should not be readonly)
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'has_override' ] )
            ->getMock();
        $service->method( 'has_override' )->willReturn( false );
        $this->assertFalse( $service->is_field_readonly() );
    }

    /**
     * @test
     */
    public function it_returns_field_instructions() {
        // Create a partial mock to mock is_field_readonly
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'is_field_readonly' ] )
            ->getMock();

        // Mock __ function
        \Patchwork\redefine('__', function ( $text, $domain ) {
            return $text;
        });

        // Test with readonly field
        $service->method( 'is_field_readonly' )->willReturn( true );
        $this->assertEquals(
            'This field is read-only because the key is set via the TBP_BIBLE_BRAINS_KEYS constant in your wp-config.php file.',
            $service->field_instructions()
        );

        // Test with non-readonly field
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'is_field_readonly' ] )
            ->getMock();
        $service->method( 'is_field_readonly' )->willReturn( false );
        $this->assertEquals( '', $service->field_instructions() );
    }

    /**
     * @test
     */
    public function it_returns_field_value() {
        // Create a partial mock to mock has_override and get_option
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'has_override', 'get_option' ] )
            ->getMock();

        // Test with override (should return masked value)
        $service->method( 'has_override' )->willReturn( true );
        $this->assertEquals(
            "********-****-****-****-************",
            $service->field_value()
        );

        // Test without override (should return option value)
        $service = $this->getMockBuilder( BibleBrainsKeys::class )
            ->setConstructorArgs( [ $this->options, $this->client, $this->cache ] )
            ->onlyMethods( [ 'has_override', 'get_option' ] )
            ->getMock();
        $service->method( 'has_override' )->willReturn( false );
        $service->method( 'get_option' )->willReturn( 'option-key' );
        $this->assertEquals( 'option-key', $service->field_value() );
    }
}
