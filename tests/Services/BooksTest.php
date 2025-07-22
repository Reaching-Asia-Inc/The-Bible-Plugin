<?php

namespace Services;

use CodeZone\Bible\Aggregators\BookAggregator;
use CodeZone\Bible\ApiServices\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Normalizers\BibleBrains\BibleBrainsBookNormalizer;
use CodeZone\Bible\Normalizers\Usx\USXBookNormalizer;
use CodeZone\Bible\Resources\Books;
use CodeZone\Bible\Services\USXBibles;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group biblebrains
 * @group books
 */
class BooksTest extends TestCase
{
    /**
     * @test
     */
    public function it_guesses_testament_correctly()
    {
        $books = new \CodeZone\Bible\Services\Books();

        // Test Old Testament books
        $this->assertEquals( 'OT', $books->guess_testament( 'GEN' ) );
        $this->assertEquals( 'OT', $books->guess_testament( 'PSA' ) );
        $this->assertEquals( 'OT', $books->guess_testament( 'MAL' ) );

        // Test New Testament books
        $this->assertEquals( 'NT', $books->guess_testament( 'MAT' ) );
        $this->assertEquals( 'NT', $books->guess_testament( 'JHN' ) );
        $this->assertEquals( 'NT', $books->guess_testament( 'REV' ) );
    }

    /**
     * @test
     */
    public function it_gets_all_books_from_bible_id()
    {
        // Create mock dependencies
        $bibles = $this->createMock( BibleBrainsBibles::class );
        $aggregator = new BookAggregator( $bibles, container()->get(USXBibles::class), container()->get(BibleBrainsBookNormalizer::class), container()->get(USXBookNormalizer::class) );
        $books = new Books( $aggregator );

        // Configure the bibles mock
        $expected_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $expected_books );

        // Test getting all books
        $result = $books->all( 'ENGESV' );

        // Assert that the result matches the expected books
        $this->assertEquals( $expected_books, $result );
    }

    /**
     * @test
     */
    public function it_finds_book_by_id()
    {
        $bibles = $this->createPartialMock(BibleBrainsBibles::class, ['books']);
        $aggregator = new BookAggregator( $bibles, container()->get(USXBibles::class), container()->get(BibleBrainsBookNormalizer::class), container()->get(USXBookNormalizer::class) );
        $books = new Books( $aggregator );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ],
            [ 'book_id' => 'JHN', 'name' => 'John', 'name_short' => 'Jn' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Test finding a book by Code
        $result = $books->find( 'JHN', 'ENGESV' );

        // Assert that the result matches the expected book
        $this->assertEquals( $test_books[2], $result );
    }

    /**
     * @test
     */
    public function it_finds_book_by_name()
    {
        $bibles = $this->createPartialMock(BibleBrainsBibles::class, ['books']);
        $aggregator = new BookAggregator( $bibles, container()->get(USXBibles::class), container()->get(BibleBrainsBookNormalizer::class), container()->get(USXBookNormalizer::class) );
        $books = new Books( $aggregator );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ],
            [ 'book_id' => 'JHN', 'name' => 'John', 'name_short' => 'Jn' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Test finding a book by name
        $result = $books->find( 'John', 'ENGESV' );

        // Assert that the result matches the expected book
        $this->assertEquals( $test_books[2], $result );
    }

    /**
     * @test
     */
    public function it_finds_book_by_short_name()
    {
        $bibles = $this->createPartialMock(BibleBrainsBibles::class, ['books']);
        $aggregator = new BookAggregator( $bibles, container()->get(USXBibles::class), container()->get(BibleBrainsBookNormalizer::class), container()->get(USXBookNormalizer::class) );
        $books = new Books( $aggregator );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ],
            [ 'book_id' => 'JHN', 'name' => 'John', 'name_short' => 'Jn' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Test finding a book by short name
        $result = $books->find( 'Jn', 'ENGESV' );

        // Assert that the result matches the expected book
        $this->assertEquals( $test_books[2], $result );
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_book_not_found()
    {
        $bibles = $this->createPartialMock(BibleBrainsBibles::class, ['books']);
        $aggregator = new BookAggregator( $bibles, container()->get(USXBibles::class), container()->get(BibleBrainsBookNormalizer::class), container()->get(USXBookNormalizer::class) );
        $books = new Books( $aggregator );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Test finding a non-existent book
        $result = $books->find( 'NonExistentBook', 'ENGESV' );

        // Assert that the result is an empty array
        $this->assertNull( $result );
    }
}
