<?php

namespace Tests\BibleBrains;

use CodeZone\Bible\Sources\BibleBrains\BibleBrainsBibles;
use Tests\TestCase;

/**
 * @group bible_brains
 * @group bibles
 */
class BiblesTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_correct_endpoint_and_default_options()
    {
        // Create a BibleBrainsBibles service
        $bibles = new BibleBrainsBibles();

        // Use reflection to access protected properties
        $reflection_class = new \ReflectionClass( BibleBrainsBibles::class );

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
        // Create a mock BibleBrainsBibles service
        $bibles = $this->getMockBuilder( BibleBrainsBibles::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get' ] )
            ->getMock();

        // Test mapping an option
        $record = [
            'id' => '123',
            'abbr' => 'KJV',
            'name' => 'King James Version'
        ];

        $expected = [
            'value' => 'KJV',
            'itemText' => 'King James Version'
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
        // Create a mock BibleBrainsBibles service
        $bibles = $this->getMockBuilder( BibleBrainsBibles::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get' ] )
            ->getMock();

        // Test mapping an option without abbr
        $record = [
            'id' => '123',
            'name' => 'King James Version'
        ];

        $expected = [
            'value' => '123',
            'itemText' => 'King James Version'
        ];

        // Call the map_option method
        $result = $bibles->map_option( $record );

        // Check that the result is as expected
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_can_search()
    {
        $bibles = $this->getMockBuilder( BibleBrainsBibles::class )
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
        $bibles = $this->getMockBuilder( BibleBrainsBibles::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'get' ] )
            ->getMock();

        // Mock the get method to return different results based on parameters
        $bibles->method( 'get' )
            ->will($this->returnCallback(function ( $endpoint, $params = [] ) {
                if ( strpos( $endpoint, 'JHN/3' ) !== false ) {
                    if ( isset( $params['verse_start'] ) && $params['verse_start'] == 16 && isset( $params['verse_end'] ) && $params['verse_end'] == 17 ) {
                        return [
                            [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 16 ],
                            [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 17 ]
                        ];
                    } elseif ( isset( $params['verse_start'] ) && $params['verse_start'] == 16 && ( !isset( $params['verse_end'] ) || $params['verse_end'] == 16 ) ) {
                        return [
                            [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 16 ]
                        ];
                    } else {
                        return [
                            [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 1 ],
                            [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 2 ],
                            [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 3 ],
                            [ 'book_id' => 'JHN', 'chapter' => 3, 'verse_start' => 4 ]
                        ];
                    }
                }
                return [];
            }));

        // Test getting a whole chapter
        $scripture = $bibles->reference( 'JHN 3', 'ENGESV' );
        $this->assertGreaterThan( 3, count( $scripture ) );
        foreach ( $scripture as $verse ) {
            $this->assertEquals( 'JHN', $verse['book_id'] );
        }

        // Test getting specific verses
        $scripture = $bibles->reference( 'JHN 3:16-17', 'ENGKJV' );
        $this->assertEquals( 2, count( $scripture ) );
        foreach ( $scripture as $verse ) {
            $this->assertEquals( 'JHN', $verse['book_id'] );
        }

        // Test getting a single verse
        $scripture = $bibles->reference( 'JHN 3:16', 'ENGKJV' );
        $this->assertEquals( 1, count( $scripture ) );
        foreach ( $scripture as $verse ) {
            $this->assertEquals( 'JHN', $verse['book_id'] );
        }
    }
}
