<?php

namespace Tests\Aggregators;

use CodeZone\Bible\Sources\Aggregators\BibleAggregator;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group aggregators
 * @group bibles
 */
class BibleAggregatorTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        $this->load_paratext_fixture( 'KJV' );
    }

    /**
     * @test
     */
    public function it_can_retrieve_all_bibles() {
        $aggregator = container()->get( BibleAggregator::class );
        $result = $aggregator->all();

        // Verify we get an array of bibles
        $this->assertIsArray( $result );
        $this->assertNotEmpty( $result );

        // Check the structure of a bible
        $bible = $result[0];
        $this->assertIsArray( $bible );
        $this->assertArrayHasKey( 'code', $bible );
        $this->assertArrayHasKey( 'name', $bible );
        $this->assertArrayHasKey( 'source', $bible );

        // Verify source field is set
        $this->assertContains( $bible['source'], [ 'bible-brains', 'paratext' ] );
    }

    /**
     * @test
     */
    public function it_can_retrieve_bibles_for_language() {
        $aggregator = container()->get( BibleAggregator::class );
        $result = $aggregator->for_language( 6414 ); // English

        // Verify we get an array of bibles
        $this->assertIsArray( $result );

        // If results are found, check their structure
        if ( !empty( $result ) ) {
            $bible = $result[0];
            $this->assertIsArray( $bible );
            $this->assertArrayHasKey( 'code', $bible );
            $this->assertArrayHasKey( 'name', $bible );
            $this->assertArrayHasKey( 'source', $bible );

            // Verify source field is set
            $this->assertContains( $bible['source'], [ 'bible-brains', 'paratext' ] );
        }
    }

    /**
     * @test
     */
    public function it_can_search_for_bibles() {
        $aggregator = container()->get( BibleAggregator::class );
        $result = $aggregator->search( 'English', [] );

        // Verify we get an array of bibles
        $this->assertIsArray( $result );

        // If results are found, check their structure
        if ( !empty( $result ) ) {
            $bible = $result[0];
            $this->assertIsArray( $bible );
            $this->assertArrayHasKey( 'code', $bible );
            $this->assertArrayHasKey( 'name', $bible );
            $this->assertArrayHasKey( 'source', $bible );

            // Verify source field is set
            $this->assertContains( $bible['source'], [ 'bible-brains', 'paratext' ] );
        }
    }

    /**
     * @test
     */
    public function it_can_find_a_bible_by_id() {
        $aggregator = container()->get( BibleAggregator::class );
        $result = $aggregator->find( 'ENGESV', [] );

        // Verify we get a bible
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'code', $result );
        $this->assertArrayHasKey( 'name', $result );
        $this->assertArrayHasKey( 'source', $result );
        $this->assertEquals( 'ENGESV', $result['code'] );

        $this->assertContains( $result['source'], [ 'bible-brains', 'paratext' ] );
    }

    /**
     * @test
     */
    public function it_returns_null_for_nonexistent_bible() {
        $aggregator = container()->get( BibleAggregator::class );
        $result = $aggregator->find( 'NonexistentBible', [] );

        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_can_get_language_default_bible() {
        $aggregator = container()->get( BibleAggregator::class );
        $result = $aggregator->language_default( 6414 ); // English

        // Verify we get a bible
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'code', $result );
        $this->assertArrayHasKey( 'name', $result );
        $this->assertArrayHasKey( 'source', $result );

        // Verify source field is set
        $this->assertContains( $result['source'], [ 'bible-brains', 'paratext' ] );
    }

    /**
     * @test
     */
    public function it_can_return_data_with_paratext_source() {
        $aggregator = container()->get( BibleAggregator::class );

        // Get all bibles
        $result = $aggregator->all();

        // Verify we get an array of bibles
        $this->assertIsArray( $result );

        // Find a bible with 'paratext' source
        $paratext_bibles = array_filter($result, function ( $bible ) {
            return $bible['source'] === 'paratext';
        });

        // Verify we found at least one bible with 'paratext' source
        $this->assertNotEmpty( $paratext_bibles, "No bibles with 'paratext' source found" );

        // Check the structure of a paratext bible
        $bible = reset( $paratext_bibles );
        $this->assertIsArray( $bible );
        $this->assertArrayHasKey( 'code', $bible );
        $this->assertArrayHasKey( 'name', $bible );
        $this->assertEquals( 'paratext', $bible['source'] );
    }

    /**
     * @test
     */
    public function it_can_return_data_with_bible_brains_source() {
        $aggregator = container()->get( BibleAggregator::class );

        // Get all bibles
        $result = $aggregator->all();

        // Verify we get an array of bibles
        $this->assertIsArray( $result );

        // Find a bible with 'bible-brains' source
        $bb_bibles = array_filter($result, function ( $bible ) {
            return $bible['source'] === 'bible-brains';
        });

        // Verify we found at least one bible with 'bible-brains' source
        $this->assertNotEmpty( $bb_bibles, "No bibles with 'bible-brains' source found" );

        // Check the structure of a bible-brains bible
        $bible = reset( $bb_bibles );
        $this->assertIsArray( $bible );
        $this->assertArrayHasKey( 'code', $bible ); // Bible Brains bibles use 'code'
        $this->assertArrayHasKey( 'name', $bible );
        $this->assertEquals( 'bible-brains', $bible['source'] );
    }
}
