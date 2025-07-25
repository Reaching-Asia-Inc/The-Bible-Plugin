<?php

namespace Tests\Aggregators;

use CodeZone\Bible\Sources\Aggregators\BookAggregator;
use CodeZone\Bible\Sources\USX\USXBibles;
use Tests\TestCase;
use function CodeZone\Bible\container;
use function CodeZone\Bible\namespace_string;

/**
 * @group aggregators
 * @group books
 */
class BookAggregatorTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        $this->load_usx_fixture( 'KJV' );
    }

    /**
     * @test
     */
    public function it_can_retrieve_all_books() {
        $aggregator = container()->get( BookAggregator::class );
        $result = $aggregator->all( 'ENGESV' );

        // Verify we get an array of books
        $this->assertIsArray( $result );
        $this->assertNotEmpty( $result );

        // Check the structure of a book
        $book = $result[0];
        $this->assertIsArray( $book );
        $this->assertArrayHasKey( 'id', $book );
        $this->assertArrayHasKey( 'name', $book );
        $this->assertArrayHasKey( 'source', $book );

        // Verify source field is set
        $this->assertContains( $book['source'], [ 'bible-brains', 'usx' ] );
    }

    /**
     * @test
     */
    public function it_can_find_a_specific_book() {
        $aggregator = container()->get( BookAggregator::class );
        $result = $aggregator->find( 'JHN', 'ENGESV' );

        // Verify we get a book
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'id', $result );
        $this->assertArrayHasKey( 'name', $result );
        $this->assertArrayHasKey( 'source', $result );

        // Verify it's the correct book
        $this->assertEquals( 'JHN', $result['id'] );
        $this->assertContains( $result['source'], [ 'bible-brains', 'usx' ] );
    }

    /**
     * @test
     */
    public function it_returns_null_for_nonexistent_book() {
        $aggregator = container()->get( BookAggregator::class );
        $result = $aggregator->find( 'NonexistentBook', 'ENGESV' );

        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_can_retrieve_books_from_specific_bible() {
        $aggregator = container()->get( BookAggregator::class );
        $result = $aggregator->all( 'ENGESV', [ 'language_id' => 6414 ] );

        // Verify we get an array of books
        $this->assertIsArray( $result );
        $this->assertNotEmpty( $result );

        // Check the structure of a book
        $book = $result[0];
        $this->assertIsArray( $book );
        $this->assertArrayHasKey( 'id', $book );
        $this->assertArrayHasKey( 'name', $book );
        $this->assertArrayHasKey( 'source', $book );

        // Verify source field is set
        $this->assertContains( $book['source'], [ 'bible-brains', 'usx' ] );
    }

    /**
     * @test
     * @group debug
     */
    public function it_can_return_data_with_usx_source() {
        $aggregator = container()->get( BookAggregator::class );

        // Get books from a USX Bible (using a Bible ID that exists in the USX fixtures)
        // Note: This test assumes that the USX fixtures contain a Bible with books
        $result = $aggregator->all( 'KJV' );

        // Verify we get an array of books
        $this->assertIsArray( $result );

        // Find books with 'usx' source
        $usx_books = array_filter($result, function ( $book ) {
            return $book['source'] === 'usx';
        });

        // Verify we found at least one book with 'usx' source
        $this->assertNotEmpty( $usx_books, "No books with 'usx' source found" );

        // Check the structure of a usx book
        $book = reset( $usx_books );
        $this->assertIsArray( $book );
        $this->assertArrayHasKey( 'id', $book );
        $this->assertArrayHasKey( 'name', $book );
        $this->assertEquals( 'usx', $book['source'] );
    }

    /**
     * @test
     */
    public function it_can_return_data_with_bible_brains_source() {
        $aggregator = container()->get( BookAggregator::class );

        // Get books from a Bible Brains Bible
        $result = $aggregator->all( 'ENGESV' );

        // Verify we get an array of books
        $this->assertIsArray( $result );

        // Find books with 'bible-brains' source
        $bb_books = array_filter($result, function ( $book ) {
            return $book['source'] === 'bible-brains';
        });

        // Verify we found at least one book with 'bible-brains' source
        $this->assertNotEmpty( $bb_books, "No books with 'bible-brains' source found" );

        // Check the structure of a bible-brains book
        $book = reset( $bb_books );
        $this->assertIsArray( $book );
        $this->assertArrayHasKey( 'id', $book );
        $this->assertArrayHasKey( 'name', $book );
        $this->assertEquals( 'bible-brains', $book['source'] );
    }
}
