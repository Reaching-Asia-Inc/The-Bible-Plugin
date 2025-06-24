<?php

namespace Tests\Services\BibleBrains;

use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\GuzzleHttp\Psr7\Response;
use CodeZone\Bible\Services\BibleBrains\Api\ApiKeys;
use CodeZone\Bible\Services\BibleBrains\BibleBrainsKeys;
use CodeZone\Bible\Services\Cache;
use CodeZone\Bible\Support\CacheInterface;
use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface as Options;
use Tests\TestCase;

/**
 * @group biblebrains
 * @group apikeys
 */
class BibleBrainsKeysTest extends TestCase
{
    protected $options;
    protected $client;
    protected $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->options = $this->createMock( Options::class );
        $this->client = $this->createMock( Client::class );
        $this->cache = $this->createMock( Cache::class );
    }

    /**
     * @test
     */
    public function it_can_fetch_keys()
    {
        $expected = [ 'key1', 'key2', 'key3' ];
        $response = new Response( 200, [], json_encode( $expected ) );

        $this->cache->method( 'get' )->willReturn( null );
        $this->cache->expects( $this->any() )
            ->method( 'set' )
            ->with( $this->anything(), $expected );

        $this->client->method( 'get' )
            ->willReturn( $response );

        $keys_service = new BibleBrainsKeys( $this->options, $this->client, $this->cache );
        $result = $keys_service->fetch_remote();

        $this->assertIsArray( $result );
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_can_fetch_override_keys()
    {
        if ( !defined( 'TBP_BIBLE_BRAINS_KEYS' ) ) {
            $keys = [ 'keys1', 'keys2', 'keys3' ];
            define( 'TBP_BIBLE_BRAINS_KEYS', 'key1,key2,key3' );
        } else {
            $keys = explode( ',', TBP_BIBLE_BRAINS_KEYS );
        }

        $keys_service = new BibleBrainsKeys( $this->options, $this->client, $this->cache );
        $this->assertTrue( $keys_service->has_override() );

        $response = $keys_service->all();
        $this->assertEquals( $keys, $response );
    }

    /**
     * @test
     */
    public function it_can_fetch_options()
    {
        $this->options->expects( $this->any() )
            ->method( 'get' )
            ->with( BibleBrainsKeys::OPTION_KEY )
            ->willReturn( 'key1' );

        $keys_service = new BibleBrainsKeys( $this->options, $this->client, $this->cache );

        $this->assertTrue( $keys_service->has_option() );

        $response = $keys_service->all( false );
        $this->assertEquals( [ 'key1' ], $response );
    }

    /**
     * @test
     */
    public function it_can_fetch_random_key()
    {
        $expected = [ 'key1', 'key2', 'key3' ];
        $response = new Response( 200, [], json_encode( $expected ) );

        $this->cache->method( 'get' )->willReturn( null );
        $this->client->method( 'get' )->willReturn( $response );

        $keys_service = new BibleBrainsKeys( $this->options, $this->client, $this->cache );

        $random = $keys_service->random( false );

        $this->assertContains( $random, $expected );
    }
}
