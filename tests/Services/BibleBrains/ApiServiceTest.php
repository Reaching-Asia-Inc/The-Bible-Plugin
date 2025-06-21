<?php

namespace Services\BibleBrains\Api;

use CodeZone\Bible\Services\BibleBrains\Api\ApiService;
use Tests\TestCase;

/**
 * phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound
 *
 * @group biblebrains
 * @group apikeys
 */
class ApiServiceTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_map_option()
    {
        // Create a mock ApiService
        $api_service = $this->getMockBuilder( ConcreteApiService::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get' ] )
            ->getMock();

        // Test mapping an option
        $record = [
            'id' => '123',
            'name' => 'Test Name'
        ];

        $expected = [
            'value' => '123',
            'itemText' => 'Test Name'
        ];

        // Call the map_option method
        $result = $api_service->map_option( $record );

        // Check that the result is as expected
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_can_convert_records_to_options()
    {
        // Create a mock ApiService
        $api_service = $this->getMockBuilder( ConcreteApiService::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get' ] )
            ->getMock();

        // Test data
        $records = [
            [
                'id' => '123',
                'name' => 'Test Name 1'
            ],
            [
                'id' => '456',
                'name' => 'Test Name 2'
            ],
            // Add a duplicate to test deduplication
            [
                'id' => '123',
                'name' => 'Test Name 1'
            ]
        ];

        // Expected result after filtering
        $expected = [
            [
                'value' => '123',
                'itemText' => 'Test Name 1'
            ],
            [
                'value' => '456',
                'itemText' => 'Test Name 2'
            ]
        ];

        // Call the as_options method
        $result = $api_service->as_options( $records );

        // Check that the result is as expected
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_has_correct_endpoint_and_default_options()
    {
        // Create a ConcreteApiService
        $api_service = new ConcreteApiService();

        // Use reflection to access protected properties
        $reflection_class = new \ReflectionClass( ConcreteApiService::class );

        $endpoint_property = $reflection_class->getProperty( 'endpoint' );
        $endpoint_property->setAccessible( true );
        $endpoint = $endpoint_property->getValue( $api_service );

        $default_options_property = $reflection_class->getProperty( 'default_options' );
        $default_options_property->setAccessible( true );
        $default_options = $default_options_property->getValue( $api_service );

        // Check that the endpoint is correct
        $this->assertEquals( 'test-endpoint', $endpoint );

        // Check that the default options are correct
        $this->assertEquals([
            'test_option' => 'test_value',
        ], $default_options);
    }
}

class ConcreteApiService extends ApiService
{
    protected string $endpoint = 'test-endpoint';
    protected array $default_options = [
        'test_option' => 'test_value',
    ];
}
