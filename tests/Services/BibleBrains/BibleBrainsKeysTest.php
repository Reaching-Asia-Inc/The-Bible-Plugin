<?php

namespace Tests\Services\BibleBrains;

use CodeZone\Bible\GuzzleHttp\Psr7\Response;
use CodeZone\Bible\Services\BibleBrains\Api\ApiKeys;
use CodeZone\Bible\Services\BibleBrains\BibleBrainsKeys;
use Tests\TestCase;
use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface as Options;
use function CodeZone\Bible\config;
use function CodeZone\Bible\container;

/**
 * Class BibleBrainsKeysTest
 *
 * This class is responsible for testing the BibleBrainsKeys service.
 *
 * @group biblebrains
 * @group apikeys
 */
class BibleBrainsKeysTest extends TestCase {
    /**
     * @test
     */
    public function it_can_fetch_keys()
    {
        // Mock the API response
        $this->mock_api_response('keys', new Response(200, [], json_encode([
            'data' => [ 'key1', 'key2', 'key3' ]
        ])));

        $keys = container()->get( ApiKeys::class );
        $keys_service = container()->get( BibleBrainsKeys::class );
        $response = $keys->all();
        $this->assertIsArray( $response );
        $this->assertNotEmpty( $response );
        foreach ( $response['data'] as $key ) {
            $this->assertIsString( $key );
        }

        $this->assertEquals( $response, $keys_service->fetch_remote() );
    }

    /**
     * @test
     */
    public function it_can_fetch_override_keys()
    {
        if ( !defined( 'TBP_BIBLE_BRAINS_KEYS' ) ) {
            define( 'TBP_BIBLE_BRAINS_KEYS', 'key1,key2,key3' );
        }
        $override = explode( ',', TBP_BIBLE_BRAINS_KEYS );
        $keys_service = container()->get( BibleBrainsKeys::class );


        $this->assertTrue( $keys_service->has_override() );

        $response = $keys_service->all();
        $this->assertIsArray( $response );
        $this->assertNotEmpty( $response );
        $this->assertEquals( $override, $response );
    }

    /**
     * @test
     */
    public function it_can_fetch_options() {
        $options = container()->get( Options::class );
        $keys_service = container()->get( BibleBrainsKeys::class );

        $options->set( BibleBrainsKeys::OPTION_KEY, 'key1' );
        $this->assertTrue( $keys_service->has_option() );

        $response = $keys_service->all( false );
        $this->assertIsArray( $response );
        $this->assertNotEmpty( $response );
        $this->assertEquals( [ 'key1' ], $response );
    }

    /**
     * @test
     */
    public function it_can_fetch_random_key()
    {
        // Mock the API response
        $this->mock_api_response('keys', new Response(200, [], json_encode([
            'data' => [ 'key1', 'key2', 'key3' ]
        ])));

        $keys_service = container()->get( BibleBrainsKeys::class );
        delete_option( config( 'options.prefix' ) . '_' . BibleBrainsKeys::OPTION_KEY );
        $random = $keys_service->random( false );
        $this->assertContains( $random, $keys_service->fetch_remote() );
    }
}
