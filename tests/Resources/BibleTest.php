<?php

namespace Tests\Resources;

use CodeZone\Bible\Resources\Bible;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group resources
 * @group bibles
 */
class BibleTest extends TestCase {
    /**
     * @test
     */
    public function it_can_find_a_bible_by_id() {
        $bibles = container()->get(Bible::class);
        $result = $bibles->find('ENGESV');

        // Verify we get a bible
        $this->assertIsArray($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('source', $result);

        // Verify source field is set
        $this->assertContains($result['source'], ['bible-brains', 'usx']);
    }

    /**
     * @test
     */
    public function it_returns_null_for_nonexistent_bible() {
        $bibles = container()->get(Bible::class);
        $result = $bibles->find('NonexistentBible');

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_can_resolve_a_bible_by_code() {
        $bibles = container()->get(Bible::class);
        $result = $bibles->resolve(['code' => 'ENGESV']);

        // Verify we get a bible
        $this->assertIsArray($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('source', $result);

        // Verify source field is set
        $this->assertContains($result['source'], ['bible-brains', 'usx']);
    }

    /**
     * @test
     */
    public function it_can_resolve_a_bible_by_language_id() {
        $bibles = container()->get(Bible::class);
        $result = $bibles->resolve(['language_id' => 6414]); // English

        // Verify we get a bible
        $this->assertIsArray($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('source', $result);

        // Verify source field is set
        $this->assertContains($result['source'], ['bible-brains', 'usx']);
    }

    /**
     * @test
     */
    public function it_can_find_multiple_bibles() {
        $bibles = container()->get(Bible::class);
        $result = $bibles->find_many(['ENGESV', 'ENGKJV']);

        // Verify we get an array of bibles
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check the structure of a bible
        $bible = $result[0];
        $this->assertIsArray($bible);
        $this->assertArrayHasKey('code', $bible);
        $this->assertArrayHasKey('name', $bible);
        $this->assertArrayHasKey('source', $bible);

        // Verify source field is set
        $this->assertContains($bible['source'], ['bible-brains', 'usx']);
    }

    /**
     * @test
     */
    public function it_can_search_for_bibles() {
        $bibles = container()->get(Bible::class);
        $result = $bibles->search('English');

        // Verify we get an array of bibles
        $this->assertIsArray($result);

        // If results are found, check their structure
        if (!empty($result)) {
            $bible = $result[0];
            $this->assertIsArray($bible);
            $this->assertArrayHasKey('code', $bible);
            $this->assertArrayHasKey('name', $bible);
            $this->assertArrayHasKey('source', $bible);

            // Verify source field is set
            $this->assertContains($bible['source'], ['bible-brains', 'usx']);
        }
    }

    /**
     * @test
     */
    public function it_can_retrieve_all_bibles() {
        $bibles = container()->get(Bible::class);
        $result = $bibles->all();

        // Verify we get an array of bibles
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check the structure of a bible
        $bible = $result[0];
        $this->assertIsArray($bible);
        $this->assertArrayHasKey('code', $bible);
        $this->assertArrayHasKey('name', $bible);
        $this->assertArrayHasKey('source', $bible);

        // Verify source field is set
        $this->assertContains($bible['source'], ['bible-brains', 'usx']);
    }

    /**
     * @test
     */
    public function it_can_retrieve_bibles_for_a_language() {
        $bibles = container()->get(Bible::class);
        $result = $bibles->all(['language_id' => 6414]); // English

        // Verify we get an array of bibles
        $this->assertIsArray($result);

        // If results are found, check their structure
        if (!empty($result)) {
            $bible = $result[0];
            $this->assertIsArray($bible);
            $this->assertArrayHasKey('code', $bible);
            $this->assertArrayHasKey('name', $bible);
            $this->assertArrayHasKey('source', $bible);

            // Verify source field is set
            $this->assertContains($bible['source'], ['bible-brains', 'usx']);
        }
    }

    /**
     * @test
     */
    public function it_can_retrieve_content() {
        $bibles = container()->get(Bible::class);
        $result = $bibles->content('ENGESV', 'JHN', 3, 16, 16);

        // Verify we get an array of content
        $this->assertIsArray($result);

        // If results are found, check their structure
        if (!empty($result)) {
            $verse = $result[0];
            $this->assertIsArray($verse);
            $this->assertArrayHasKey('book_id', $verse);
            $this->assertArrayHasKey('chapter', $verse);
            $this->assertArrayHasKey('verse_start', $verse);
            $this->assertArrayHasKey('verse_text', $verse);
        }
    }
}
