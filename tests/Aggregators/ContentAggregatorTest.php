<?php

namespace Tests\Aggregators;

use CodeZone\Bible\Sources\Aggregators\ContentAggregator;
use CodeZone\Bible\Sources\USX\USXBibles;
use Tests\TestCase;
use function CodeZone\Bible\container;
use function CodeZone\Bible\namespace_string;

/**
 * @group aggregators
 * @group content
 */
class ContentAggregatorTest extends TestCase {
    protected function setup(): void {
        parent::setup();
        $this->load_usx_fixture( 'KJV' );
    }

    /**
     * @test
     */
    public function it_can_retrieve_content() {
        $aggregator = container()->get( ContentAggregator::class );
        $result = $aggregator->content( 'ENGESV', 'JHN', 3, 16, 16 );

        // Verify we get an array of content
        $this->assertIsArray( $result );

        // If results are found, check their structure
        if ( !empty( $result ) ) {
            $verse = $result[0];
            $this->assertIsArray( $verse );
            $this->assertArrayHasKey( 'book_id', $verse );
            $this->assertArrayHasKey( 'chapter', $verse );
            $this->assertArrayHasKey( 'verse_start', $verse );

            // Check for either 'verse_text' or 'content' field depending on the source
            $this->assertTrue(
                isset( $verse['verse_text'] ) || isset( $verse['content'] ),
                "Either 'verse_text' or 'content' field should be present"
            );

            $this->assertArrayHasKey( 'source', $verse );

            // Verify source field is set
            $this->assertContains( $verse['source'], [ 'bible-brains', 'usx' ] );
        }
    }

    /**
     * @test
     */
    public function it_can_retrieve_content_for_verse_range() {
        $aggregator = container()->get( ContentAggregator::class );
        $result = $aggregator->content( 'ENGESV', 'JHN', 3, 16, 18 );

        // Verify we get an array of content
        $this->assertIsArray( $result );

        // If results are found, check their structure and count
        if ( !empty( $result ) ) {
            $this->assertCount( 3, $result ); // Should have 3 verses (16, 17, 18)

            foreach ( $result as $verse ) {
                $this->assertIsArray( $verse );
                $this->assertArrayHasKey( 'book_id', $verse );
                $this->assertArrayHasKey( 'chapter', $verse );
                $this->assertArrayHasKey( 'verse_start', $verse );

                // Check for either 'verse_text' or 'content' field depending on the source
                $this->assertTrue(
                    isset( $verse['verse_text'] ) || isset( $verse['content'] ),
                    "Either 'verse_text' or 'content' field should be present"
                );

                $this->assertArrayHasKey( 'source', $verse );

                // Verify source field is set
                $this->assertContains( $verse['source'], [ 'bible-brains', 'usx' ] );
            }
        }
    }

    /**
     * @test
     */
    public function it_returns_empty_array_for_nonexistent_content() {
        $aggregator = container()->get( ContentAggregator::class );

        // Test with a nonexistent chapter
        $result = $aggregator->content( 'ENGESV', 'JHN', 999, 1, 1 );
        $this->assertIsArray( $result );
        $this->assertEmpty( $result );

        // Test with a nonexistent verse
        $result = $aggregator->content( 'ENGESV', 'JHN', 3, 999, 999 );
        $this->assertIsArray( $result );
        $this->assertEmpty( $result );
    }

    /**
     * @test
     */
    public function it_can_return_data_with_usx_source() {
        $aggregator = container()->get( ContentAggregator::class );

        // Get content from a USX Bible (using a Bible ID that exists in the USX fixtures)
        // Note: This test assumes that the USX fixtures contain a Bible with content
        $result = $aggregator->content( 'KJV', 'GEN', 1, 1, 3 );

        // Verify we get an array of content
        $this->assertIsArray( $result );

        // Find content with 'usx' source
        $usx_content = array_filter($result, function ( $verse ) {
            return $verse['source'] === 'usx';
        });

        // Verify we found at least one verse with 'usx' source
        $this->assertNotEmpty( $usx_content, "No content with 'usx' source found" );

        // Check the structure of a usx verse
        $verse = reset( $usx_content );
        $this->assertIsArray( $verse );
        $this->assertArrayHasKey( 'book_id', $verse );
        $this->assertArrayHasKey( 'chapter', $verse );
        $this->assertArrayHasKey( 'verse_start', $verse );

        // Check for either 'content' or 'verse_text' field
        $this->assertTrue(
            isset( $verse['content'] ) || isset( $verse['verse_text'] ),
            "Either 'content' or 'verse_text' field should be present"
        );

        $this->assertEquals( 'usx', $verse['source'] );
    }

    /**
     * @test
     */
    public function it_can_return_data_with_bible_brains_source() {
        $aggregator = container()->get( ContentAggregator::class );

        // Get content from a Bible Brains Bible
        $result = $aggregator->content( 'ENGESV', 'JHN', 3, 16, 16 );

        // Verify we get an array of content
        $this->assertIsArray( $result );

        // Find content with 'bible-brains' source
        $bb_content = array_filter($result, function ( $verse ) {
            return $verse['source'] === 'bible-brains';
        });

        // Verify we found at least one verse with 'bible-brains' source
        $this->assertNotEmpty( $bb_content, "No content with 'bible-brains' source found" );

        // Check the structure of a bible-brains verse
        $verse = reset( $bb_content );
        $this->assertIsArray( $verse );
        $this->assertArrayHasKey( 'book_id', $verse );
        $this->assertArrayHasKey( 'chapter', $verse );
        $this->assertArrayHasKey( 'verse_start', $verse );

        // Check for 'verse_text' field
        $this->assertTrue(
            isset( $verse['verse_text'] ),
            "'verse_text' field should be present"
        );

        $this->assertEquals( 'bible-brains', $verse['source'] );
    }
}
