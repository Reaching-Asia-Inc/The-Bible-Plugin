<?php

namespace Tests\Services\BibleBrains\Api;

use CodeZone\Bible\Services\BibleBrains\Api\Languages;
use Tests\TestCase;

class LanguagesTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_map_option()
    {
        // Create a mock Languages service
        $languages_mock = $this->getMockBuilder( Languages::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get', 'post' ] )
            ->getMock();

        // Test mapping an option
        $record_data = [
            'id' => '6414',
            'iso' => 'eng',
            'name' => 'English'
        ];

        $expected_result = [
            'value' => '6414',
            'language_code' => 'eng',
            'itemText' => 'English'
        ];

        // Call the map_option method using reflection to access the protected method
        $reflection_method = new \ReflectionMethod( Languages::class, 'map_option' );
        $reflection_method->setAccessible( true );
        $result = $reflection_method->invoke( $languages_mock, $record_data );

        // Check that the result is as expected
        $this->assertEquals( $expected_result, $result );
    }

    /**
     * @test
     */
    public function it_can_convert_languages_to_options()
    {
        // Create a mock Languages service
        $languages_mock = $this->getMockBuilder( Languages::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get', 'post' ] )
            ->getMock();

        // Test data
        $record_list = [
            [
                'id' => '6414',
                'iso' => 'eng',
                'name' => 'English'
            ],
            [
                'id' => '6415',
                'iso' => 'spa',
                'name' => 'Spanish'
            ],
            // Add a duplicate to test deduplication
            [
                'id' => '6414',
                'iso' => 'eng',
                'name' => 'English'
            ]
        ];

        // Expected result after deduplication
        $expected_result = [
            [
                'value' => '6414',
                'language_code' => 'eng',
                'itemText' => 'English'
            ],
            [
                'value' => '6415',
                'language_code' => 'spa',
                'itemText' => 'Spanish'
            ]
        ];

        // Call the as_options method
        $result = $languages_mock->as_options( $record_list );

        // Check that the result is as expected
        $this->assertEquals( $expected_result, $result );
    }

    /**
     * @test
     */
    public function it_has_correct_endpoint_and_default_options()
    {
        // Create a Languages service
        $languages_service = new Languages();

        // Use reflection to access protected properties
        $reflection_class = new \ReflectionClass( Languages::class );

        $endpoint_property = $reflection_class->getProperty( 'endpoint' );
        $endpoint_property->setAccessible( true );
        $endpoint = $endpoint_property->getValue( $languages_service );

        $default_options_property = $reflection_class->getProperty( 'default_options' );
        $default_options_property->setAccessible( true );
        $default_options = $default_options_property->getValue( $languages_service );

        // Check that the endpoint is correct
        $this->assertEquals( 'languages', $endpoint );

        // Check that the default options are correct
        $this->assertEquals([
            'include_translations' => false,
            'include_all_names' => false,
            'limit' => 500,
        ], $default_options);
    }
}
