<?php

namespace Tests\Services;

use CodeZone\Bible\Services\FileSets;
use Tests\TestCase;

/**
 * @group services
 * @group filesets
 */
class FileSetsTest extends TestCase {
    /**
     * @var FileSets
     */
    protected $filesets;

    /**
     * @var array
     */
    protected $bible;

    /**
     * @var array
     */
    protected $book;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();

        // Create the FileSets service
        $this->filesets = new FileSets();

        // Set up test data
        $this->bible = [
            'id' => 'ENGESV',
            'name' => 'English Standard Version',
            'source' => 'bible-brains',
            'filesets' => [
                [
                    'id' => 'ENGESVN1DA',
                    'type' => 'text_plain',
                    'size' => 'C', // Complete Bible
                ],
                [
                    'id' => 'ENGESVN2DA',
                    'type' => 'text_format',
                    'size' => 'C', // Complete Bible
                ],
                [
                    'id' => 'ENGESVP1DA',
                    'type' => 'audio',
                    'size' => 'NT', // New Testament only
                ],
                [
                    'id' => 'ENGESVP2DA',
                    'type' => 'audio_drama',
                    'size' => 'OT', // Old Testament only
                ],
                [
                    'id' => 'ENGESVS1DA',
                    'type' => 'video_stream',
                    'size' => 'NT,OT', // Both Testaments
                ],
            ],
        ];

        $this->book = [
            'id' => 'JHN',
            'name' => 'John',
            'testament' => 'NT',
            'source' => 'bible-brains',
        ];
    }

    /**
     * @test
     */
    public function it_can_be_instantiated() {
        $this->assertInstanceOf( FileSets::class, $this->filesets );
    }

    /**
     * @test
     */
    public function it_can_resolve_fileset_with_complete_size() {
        // Test resolving a fileset with size 'C' (complete)
        $result = $this->filesets->resolve( $this->bible, $this->book, 'text_plain' );

        // Verify the result is the expected fileset
        $this->assertNotNull( $result );
        $this->assertEquals( 'ENGESVN1DA', $result['id'] );
        $this->assertEquals( 'text_plain', $result['type'] );
        $this->assertEquals( 'C', $result['size'] );
    }

    /**
     * @test
     */
    public function it_can_resolve_fileset_with_matching_testament() {
        // Test resolving a fileset with size matching the book's testament (NT)
        $result = $this->filesets->resolve( $this->bible, $this->book, 'audio' );

        // Verify the result is the expected fileset
        $this->assertNotNull( $result );
        $this->assertEquals( 'ENGESVP1DA', $result['id'] );
        $this->assertEquals( 'audio', $result['type'] );
        $this->assertEquals( 'NT', $result['size'] );
    }

    /**
     * @test
     */
    public function it_can_resolve_fileset_with_multiple_testaments() {
        // Test resolving a fileset with size containing multiple testaments including the book's testament
        $result = $this->filesets->resolve( $this->bible, $this->book, 'video_stream' );

        // Verify the result is the expected fileset
        $this->assertNotNull( $result );
        $this->assertEquals( 'ENGESVS1DA', $result['id'] );
        $this->assertEquals( 'video_stream', $result['type'] );
        $this->assertEquals( 'NT,OT', $result['size'] );
    }

    /**
     * @test
     */
    public function it_returns_null_for_non_matching_testament() {
        // Create a book from the Old Testament
        $ot_book = [
            'id' => 'GEN',
            'name' => 'Genesis',
            'testament' => 'OT',
            'source' => 'bible-brains',
        ];

        // Test resolving a fileset with size that doesn't match the book's testament
        $result = $this->filesets->resolve( $this->bible, $ot_book, 'audio' );

        // Verify the result is null (no matching fileset)
        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_returns_null_for_non_matching_type() {
        // Test resolving a fileset with a type that doesn't exist
        $result = $this->filesets->resolve( $this->bible, $this->book, 'non_existent_type' );

        // Verify the result is null (no matching fileset)
        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_can_pluck_first_matching_fileset() {
        // Test plucking the first matching fileset from a list of types
        $result = $this->filesets->pluck(
            $this->bible,
            $this->book,
            [ 'non_existent_type', 'audio', 'text_plain' ]
        );

        // Verify the result is the first matching fileset (audio)
        $this->assertNotNull( $result );
        $this->assertEquals( 'ENGESVP1DA', $result['id'] );
        $this->assertEquals( 'audio', $result['type'] );
    }

    /**
     * @test
     */
    public function it_returns_null_when_plucking_with_no_matches() {
        // Test plucking with no matching filesets
        $result = $this->filesets->pluck(
            $this->bible,
            $this->book,
            [ 'non_existent_type1', 'non_existent_type2' ]
        );

        // Verify the result is null (no matching filesets)
        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_returns_null_when_plucking_with_empty_types() {
        // Test plucking with an empty list of types
        $result = $this->filesets->pluck( $this->bible, $this->book, [] );

        // Verify the result is null (no types to match)
        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_returns_null_when_bible_has_no_filesets() {
        // Create a bible with no filesets
        $bible_without_filesets = [
            'id' => 'ENGESV',
            'name' => 'English Standard Version',
            'source' => 'bible-brains',
            'filesets' => [],
        ];

        // Test resolving a fileset
        $result = $this->filesets->resolve( $bible_without_filesets, $this->book, 'text_plain' );

        // Verify the result is null (no filesets to match)
        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_handles_old_testament_books() {
        // Create a book from the Old Testament
        $ot_book = [
            'id' => 'GEN',
            'name' => 'Genesis',
            'testament' => 'OT',
            'source' => 'bible-brains',
        ];

        // Test resolving a fileset for an Old Testament book
        $result = $this->filesets->resolve( $this->bible, $ot_book, 'audio_drama' );

        // Verify the result is the expected fileset
        $this->assertNotNull( $result );
        $this->assertEquals( 'ENGESVP2DA', $result['id'] );
        $this->assertEquals( 'audio_drama', $result['type'] );
        $this->assertEquals( 'OT', $result['size'] );
    }

    /**
     * @test
     */
    public function it_prioritizes_filesets_in_order() {
        // Test plucking with multiple matching filesets
        $result = $this->filesets->pluck(
            $this->bible,
            $this->book,
            [ 'text_format', 'text_plain', 'audio' ]
        );

        // Verify the result is the first matching fileset in the list (text_format)
        $this->assertNotNull( $result );
        $this->assertEquals( 'ENGESVN2DA', $result['id'] );
        $this->assertEquals( 'text_format', $result['type'] );
    }
}
