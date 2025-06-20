<?php

namespace Tests\Services\BibleBrains;

use CodeZone\Bible\Services\BibleBrains\Api\ApiKeys;
use CodeZone\Bible\Services\BibleBrains\BibleBrainsKeys;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * Class BibleBrainsKeysTest
 *
 * This class is responsible for testing the BibleBrainsKeys service.
 *
 * @test
 */
class BibleBrainsKeysTest extends TestCase {
    /**
     * @test
     */
    public function it_can_fetch_keys()
    {
        $keys = container()->make( ApiKeys::class );
        $keys_service = container()->make( BibleBrainsKeys::class );
        $response = $keys->all();
        $this->assertIsArray( $response );
        $this->assertNotEmpty( $response );
        foreach ( $response as $key ) {
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
        $keys_service = container()->make( BibleBrainsKeys::class );


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
        $options = container()->make( 'CodeZone\Bible\Services\Options' );
        $keys_service = container()->make( BibleBrainsKeys::class );

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
        $keys_service = container()->make( BibleBrainsKeys::class );
        $options = container()->make( 'CodeZone\Bible\Services\Options' );
        $options->delete( BibleBrainsKeys::OPTION_KEY );
        $random = $keys_service->random( false );
        $this->assertContains( $random, $keys_service->fetch_remote() );
    }
}
