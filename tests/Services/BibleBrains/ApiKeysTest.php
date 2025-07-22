<?php

namespace Services\BibleBrains;

use CodeZone\Bible\ApiServices\BibleBrains\BibleBrainsApiKeys;
use Tests\TestCase;

/**
 * @group biblebrains
 * @group apikeys
 */
class ApiKeysTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_correct_endpoint()
    {
        // Create an BibleBrainsApiKeys service
        $api_keys = new BibleBrainsApiKeys();

        // Use reflection to access protected properties
        $reflection_class = new \ReflectionClass( BibleBrainsApiKeys::class );

        $endpoint_property = $reflection_class->getProperty( 'endpoint' );
        $endpoint_property->setAccessible( true );
        $endpoint = $endpoint_property->getValue( $api_keys );

        // Check that the endpoint is correct
        $this->assertEquals( 'keys', $endpoint );
    }
}
