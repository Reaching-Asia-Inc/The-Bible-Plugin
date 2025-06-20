<?php

namespace Tests\Services\BibleBrains;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\BibleBrains\Books;
use Tests\TestCase;

class BooksTest extends TestCase
{
    /**
     * @test
     */
    public function it_guesses_testament_correctly()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );

        // Create the Books service
        $books = new Books( $bibles );

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
        $bibles = $this->createMock( Bibles::class );

        // Configure the bibles mock
        $expected_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $expected_books );

        // Create the Books service
        $books = new Books( $bibles );

        // Test getting all books
        $result = $books->all( 'ENGESV' );

        // Assert that the result matches the expected books
        $this->assertEquals( $expected_books, $result );
    }

    /**
     * @test
     */
    public function it_gets_all_books_from_array()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );

        // Create the Books service
        $books = new Books( $bibles );

        // Test data
        $bible_data = [
            'books' => [
                [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
                [ 'book_id' => 'EXO', 'name' => 'Exodus' ]
            ]
        ];

        // Test getting all books from array
        $result = $books->all( $bible_data );

        // Assert that the result matches the expected books
        $this->assertEquals( $bible_data['books'], $result );
    }

    /**
     * @test
     */
    public function it_finds_book_by_id()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ],
            [ 'book_id' => 'JHN', 'name' => 'John', 'name_short' => 'Jn' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Create the Books service
        $books = new Books( $bibles );

        // Test finding a book by ID
        $result = $books->find( 'JHN', 'ENGESV' );

        // Assert that the result matches the expected book
        $this->assertEquals( $test_books[2], $result );
    }

    /**
     * @test
     */
    public function it_finds_book_by_name()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ],
            [ 'book_id' => 'JHN', 'name' => 'John', 'name_short' => 'Jn' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Create the Books service
        $books = new Books( $bibles );

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
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ],
            [ 'book_id' => 'JHN', 'name' => 'John', 'name_short' => 'Jn' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Create the Books service
        $books = new Books( $bibles );

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
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Create the Books service
        $books = new Books( $bibles );

        // Test finding a non-existent book
        $result = $books->find( 'NonExistentBook', 'ENGESV' );

        // Assert that the result is an empty array
        $this->assertEquals( [], $result );
    }

    /**
     * @test
     */
    public function it_normalizes_book_id()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'JHN', 'name' => 'John', 'name_short' => 'Jn' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Create the Books service
        $books = new Books( $bibles );

        // Test normalizing a book ID
        $result = $books->normalize( 'John', 'ENGESV' );

        // Assert that the result is the normalized book ID
        $this->assertEquals( 'JHN', $result );
    }

    /**
     * @test
     */
    public function it_returns_original_book_when_normalization_fails()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );

        // Configure the bibles mock
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ]
        ];

        $bibles->method( 'books' )
            ->with( 'ENGESV' )
            ->willReturn( $test_books );

        // Create the Books service
        $books = new Books( $bibles );

        // Test normalizing a non-existent book
        $result = $books->normalize( 'NonExistentBook', 'ENGESV' );

        // Assert that the result is the original book name
        $this->assertEquals( 'NonExistentBook', $result );
    }

    /**
     * @test
     */
    public function it_plucks_book_from_array()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );

        // Create the Books service
        $books = new Books( $bibles );

        // Test data
        $test_books = [
            [ 'book_id' => 'GEN', 'name' => 'Genesis' ],
            [ 'book_id' => 'EXO', 'name' => 'Exodus' ],
            [ 'book_id' => 'JHN', 'name' => 'John', 'name_short' => 'Jn' ]
        ];

        // Test plucking a book by ID
        $result = $books->pluck( 'JHN', $test_books );

        // Assert that the result matches the expected book
        $this->assertEquals( $test_books[2], $result );

        // Test plucking a book by name
        $result = $books->pluck( 'Genesis', $test_books );

        // Assert that the result matches the expected book
        $this->assertEquals( $test_books[0], $result );

        // Test plucking a non-existent book
        $result = $books->pluck( 'NonExistentBook', $test_books );

        // Assert that the result is an empty array
        $this->assertEquals( [], $result );
    }
}
