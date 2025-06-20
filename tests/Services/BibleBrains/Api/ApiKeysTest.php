<?php

namespace Tests\Services\BibleBrains\Api;

use CodeZone\Bible\Services\BibleBrains\Api\ApiKeys;
use Tests\TestCase;

class ApiKeysTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_correct_endpoint()
    {
        // Create an ApiKeys service
        $api_keys = new ApiKeys();

        // Use reflection to access protected properties
        $reflection_class = new \ReflectionClass( ApiKeys::class );

        $endpoint_property = $reflection_class->getProperty( 'endpoint' );
        $endpoint_property->setAccessible( true );
        $endpoint = $endpoint_property->getValue( $api_keys );

        // Check that the endpoint is correct
        $this->assertEquals( 'keys', $endpoint );
    }
}
