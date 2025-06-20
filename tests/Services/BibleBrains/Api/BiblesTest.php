<?php

namespace Tests\Services\BibleBrains\Api;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use Tests\TestCase;

class BiblesTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_correct_endpoint_and_default_options()
    {
        // Create a Bibles service
        $bibles = new Bibles();

        // Use reflection to access protected properties
        $reflection_class = new \ReflectionClass( Bibles::class );

        $endpoint_property = $reflection_class->getProperty( 'endpoint' );
        $endpoint_property->setAccessible( true );
        $endpoint = $endpoint_property->getValue( $bibles );

        $default_options_property = $reflection_class->getProperty( 'default_options' );
        $default_options_property->setAccessible( true );
        $default_options = $default_options_property->getValue( $bibles );

        // Check that the endpoint is correct
        $this->assertEquals( 'bibles', $endpoint );

        // Check that the default options are correct
        $this->assertEquals([
            'limit' => 500,
        ], $default_options);
    }

    /**
     * @test
     */
    public function it_can_map_option()
    {
        // Create a mock Bibles service
        $bibles = $this->getMockBuilder( Bibles::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get', 'post' ] )
            ->getMock();

        // Test mapping an option
        $record = [
            'id' => '123',
            'abbr' => 'KJV',
            'name' => 'King James Version'
        ];

        $expected = [
            'value' => 'KJV',
            'item_text' => 'King James Version'
        ];

        // Call the map_option method
        $result = $bibles->map_option( $record );

        // Check that the result is as expected
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_can_map_option_with_id_fallback()
    {
        // Create a mock Bibles service
        $bibles = $this->getMockBuilder( Bibles::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get', 'post' ] )
            ->getMock();

        // Test mapping an option without abbr
        $record = [
            'id' => '123',
            'name' => 'King James Version'
        ];

        $expected = [
            'value' => '123',
            'item_text' => 'King James Version'
        ];

        // Call the map_option method
        $result = $bibles->map_option( $record );

        // Check that the result is as expected
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_neither_code_nor_language_id_provided()
    {
        // Create a mock Bibles service
        $bibles = $this->getMockBuilder( Bibles::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get', 'post' ] )
            ->getMock();

        // Expect an exception
        $this->expectException( BibleBrainsException::class );

        // Call the find_or_default method without code or language_id
        $bibles->find_or_default();
    }

    /**
     * @test
     */
    public function it_can_search()
    {
        $bibles = $this->getMockBuilder( Bibles::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get' ] )
            ->getMock();

        // Mock the get method to return search results
        $bibles->method( 'get' )
            ->willReturn([
                'data' => [
                    [ 'id' => '1', 'name' => 'New King James Version' ],
                    [ 'id' => '2', 'name' => 'King James Version' ]
                ]
            ]);

        // Call the search method
        $result = $bibles->search( 'New King James Version' );

        // Check that the result contains data
        $this->assertArrayHasKey( 'data', $result );
        $this->assertGreaterThan( 0, count( $result['data'] ) );
    }

    /**
     * @test
     */
    public function it_can_get_bible_content()
    {
        $bibles = $this->getMockBuilder( Bibles::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get' ] )
            ->getMock();

        // Mock the get method to return different results based on parameters
        $bibles->method( 'get' )
            ->will($this->returnCallback(function ( $endpoint, $params = [] ) {
                if ( strpos( $endpoint, 'JHN/3' ) !== false ) {
                    if ( isset( $params['verse_start'] ) && $params['verse_start'] == 16 && isset( $params['verse_end'] ) && $params['verse_end'] == 17 ) {
                        return [
                            'data' => [
                                [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 16 ],
                                [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 17 ]
                            ]
                        ];
                    } elseif ( isset( $params['verse_start'] ) && $params['verse_start'] == 16 && ( !isset( $params['verse_end'] ) || $params['verse_end'] == 16 ) ) {
                        return [
                            'data' => [
                                [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 16 ]
                            ]
                        ];
                    } else {
                        return [
                            'data' => [
                                [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 1 ],
                                [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 2 ],
                                [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 3 ],
                                [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 4 ]
                            ]
                        ];
                    }
                }
                return [ 'data' => [] ];
            }));

        // Test getting a whole chapter
        $scripture = $bibles->reference( 'John 3', 'ENGESV' );
        $this->assertGreaterThan( 3, count( $scripture['data'] ) );
        foreach ( $scripture['data'] as $verse ) {
            $this->assertEquals( 'JHN', $verse['book_id'] );
        }

        // Test getting specific verses
        $scripture = $bibles->reference( 'JHN 3:16-17', 'ENGKJV' );
        $this->assertEquals( 2, count( $scripture['data'] ) );
        foreach ( $scripture['data'] as $verse ) {
            $this->assertEquals( 'JHN', $verse['book_id'] );
        }

        // Test getting a single verse
        $scripture = $bibles->reference( 'john 3:16', 'ENGKJV' );
        $this->assertEquals( 1, count( $scripture['data'] ) );
        foreach ( $scripture['data'] as $verse ) {
            $this->assertEquals( 'JHN', $verse['book_id'] );
        }
    }
}
