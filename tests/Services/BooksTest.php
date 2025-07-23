<?php

namespace Services;

use CodeZone\Bible\Services\Books;
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
        $books = container()->get( Books::class );

        // Test getting all books
        $result = $books->all( 'ENGESV' );

        $this->assertEquals( $result['GEN'], 'Genesis' );
    }
}
