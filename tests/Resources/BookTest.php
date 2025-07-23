<?php

namespace Tests\Resources;

use CodeZone\Bible\Resources\Book;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group resources
 * @group books
 */
class BookTest extends TestCase {
    /**
     * @test
     */
    public function it_can_retrieve_all_books() {
        $books = container()->get(Book::class);
        $result = $books->all();

        // Verify we get an array of books
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check the structure of a book
        $book = $result[0];
        $this->assertIsArray($book);
        $this->assertArrayHasKey('id', $book);
        $this->assertArrayHasKey('name', $book);
        $this->assertArrayHasKey('source', $book);

        // Verify source field is set
        $this->assertContains($book['source'], ['bible-brains', 'usx']);
    }

    /**
     * @test
     */
    public function it_can_find_a_specific_book() {
        $books = container()->get(Book::class);
        $result = $books->find('John');

        // Verify we get a book
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('source', $result);

        // Verify it's the correct book
        $this->assertEquals('JHN', $result['id']);
        $this->assertContains($result['source'], ['bible-brains', 'usx']);
    }

    /**
     * @test
     */
    public function it_returns_null_for_nonexistent_book() {
        $books = container()->get(Book::class);
        $result = $books->find('NonexistentBook');

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_can_retrieve_books_from_specific_bible() {
        $books = container()->get(Book::class);
        $result = $books->all('ENGESV');

        // Verify we get an array of books
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check the structure of a book
        $book = $result[0];
        $this->assertIsArray($book);
        $this->assertArrayHasKey('id', $book);
        $this->assertArrayHasKey('name', $book);
        $this->assertArrayHasKey('source', $book);

        // Verify source field is set
        $this->assertContains($book['source'], ['bible-brains', 'usx']);
    }
}
