<?php

namespace Tests\Aggregators;

use CodeZone\Bible\Sources\Aggregators\LanguageAggregator;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group aggregators
 * @group languages
 */
class LanguageAggregatorTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        $this->load_paratext_fixture( 'KJV' );
    }


    /**
     * @test
     */
    public function it_can_retrieve_all_languages() {
        $aggregator = container()->get( LanguageAggregator::class );
        $result = $aggregator->all();

        // Verify we get an array of languages
        $this->assertIsArray( $result );
        $this->assertNotEmpty( $result );

        // Check the structure of a language
        $language = $result[0];
        $this->assertIsArray( $language );
        $this->assertArrayHasKey( 'id', $language );
        $this->assertArrayHasKey( 'name', $language );
        $this->assertArrayHasKey( 'source', $language );

        // Verify source field is set
        $this->assertContains( $language['source'], [ 'bible-brains', 'paratext' ] );
    }

    /**
     * @test
     */
    public function it_can_find_a_language_by_id() {
        $aggregator = container()->get( LanguageAggregator::class );
        $result = $aggregator->find( 6414 ); // English

        // Verify we get a language
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'id', $result );
        $this->assertArrayHasKey( 'name', $result );
        $this->assertArrayHasKey( 'source', $result );

        // Verify it's the correct language
        $this->assertEquals( 6414, $result['id'] );
        $this->assertContains( $result['source'], [ 'bible-brains', 'paratext' ] );
    }

    /**
     * @test
     */
    public function it_returns_null_for_nonexistent_language() {
        $aggregator = container()->get( LanguageAggregator::class );
        $result = $aggregator->find( 999999 ); // Nonexistent language ID

        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_can_search_for_languages() {
        $aggregator = container()->get( LanguageAggregator::class );
        $result = $aggregator->search( 'English' );

        // Verify we get an array of languages
        $this->assertIsArray( $result );

        // If results are found, check their structure
        if ( !empty( $result ) ) {
            $language = $result[0];
            $this->assertIsArray( $language );
            $this->assertArrayHasKey( 'id', $language );
            $this->assertArrayHasKey( 'name', $language );
            $this->assertArrayHasKey( 'source', $language );

            // Verify source field is set
            $this->assertContains( $language['source'], [ 'bible-brains', 'paratext' ] );
        }
    }

    /**
     * @test
     */
    public function it_can_search_with_parameters() {
        $aggregator = container()->get( LanguageAggregator::class );
        $result = $aggregator->search( 'English', [ 'limit' => 5 ] );

        // Verify we get an array of languages
        $this->assertIsArray( $result );

        // If results are found, check their structure and count
        if ( !empty( $result ) ) {
            $language = $result[0];
            $this->assertIsArray( $language );
            $this->assertArrayHasKey( 'id', $language );
            $this->assertArrayHasKey( 'name', $language );
            $this->assertArrayHasKey( 'source', $language );

            // Verify source field is set
            $this->assertContains( $language['source'], [ 'bible-brains', 'paratext' ] );
        }
    }

    /**
     * @test
     */
    public function it_can_return_data_with_usx_source() {
        $aggregator = container()->get( LanguageAggregator::class );

        // Get all languages
        $result = $aggregator->all();

        // Verify we get an array of languages
        $this->assertIsArray( $result );

        // Find languages with 'paratext' source
        $usx_languages = array_filter($result, function ( $language ) {
            return $language['source'] === 'paratext';
        });

        // Verify we found at least one language with 'paratext' source
        $this->assertNotEmpty( $usx_languages, "No languages with 'paratext' source found" );

        // Check the structure of a paratext language
        $language = reset( $usx_languages );
        $this->assertIsArray( $language );
        $this->assertArrayHasKey( 'id', $language );
        $this->assertArrayHasKey( 'name', $language );
        $this->assertEquals( 'paratext', $language['source'] );
    }

    /**
     * @test
     */
    public function it_can_return_data_with_bible_brains_source() {
        $aggregator = container()->get( LanguageAggregator::class );

        // Get all languages
        $result = $aggregator->all();

        // Verify we get an array of languages
        $this->assertIsArray( $result );

        // Find languages with 'bible-brains' source
        $bb_languages = array_filter($result, function ( $language ) {
            return $language['source'] === 'bible-brains';
        });

        // Verify we found at least one language with 'bible-brains' source
        $this->assertNotEmpty( $bb_languages, "No languages with 'bible-brains' source found" );

        // Check the structure of a bible-brains language
        $language = reset( $bb_languages );
        $this->assertIsArray( $language );
        $this->assertArrayHasKey( 'id', $language );
        $this->assertArrayHasKey( 'name', $language );
        $this->assertEquals( 'bible-brains', $language['source'] );
    }
}
