<?php

namespace Tests\USX;

use CodeZone\Bible\Sources\USX\USX;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * @group services
 * @group usx
 */
class USXServiceTest extends TestCase
{
    /**
     * @var USX
     */
    protected $service;

    /**
     * @var string
     */
    protected $fixtures_dir;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up the fixtures directory
        $this->fixtures_dir = dirname( __DIR__ ) . '/fixtures/usx/KJV';

        // Create the service
        $this->service = new USX( $this->fixtures_dir );
    }

    protected function tearDown(): void
    {
        // Recursively delete the tmp upload dir
        $dir = sys_get_temp_dir() . '/bible-plugin-test';
        if ( is_dir( $dir ) ) {
            $it = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
            $files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );
            foreach ( $files as $file ) {
                $file->isDir() ? rmdir( $file->getRealPath() ) : unlink( $file->getRealPath() );
            }
            rmdir( $dir );
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_can_get_and_set_directory()
    {
        // Test get_directory
        $this->assertEquals( $this->fixtures_dir, $this->service->get_directory() );

        // Test set_directory
        $new_dir = '/path/to/new/dir';
        $this->service->set_directory( $new_dir );
        $this->assertEquals( $new_dir, $this->service->get_directory() );
    }

    /**
     * @test
     */
    public function it_can_parse_usx_files()
    {
        // Set the directory back to the fixtures directory
        $this->service->set_directory( $this->fixtures_dir );

        // Test the all method
        $books = $this->service->all();

        // Verify the book data structure
        $book = $books[0];
        $this->assertArrayHasKey( 'book_id', $book );
        $this->assertArrayHasKey( 'name', $book );
        $this->assertArrayHasKey( 'chapters', $book );

        // Verify we have chapters
        $this->assertNotEmpty( $book['chapters'] );

        // Verify the chapter data structure
        $chapter = reset( $book['chapters'] );
        $this->assertNotEmpty( $chapter );

        // Verify we have verses
        $verse = reset( $chapter );
        $this->assertNotEmpty( $verse );
    }

    /**
     * @test
     */
    public function it_can_get_content_from_passage()
    {
        // Set the directory back to the fixtures directory
        $this->service->set_directory( $this->fixtures_dir );

        // Test the get_content method
        $content = $this->service->content( 'GEN', 1, 1, 3 );

        foreach ( $content as $passage ) {
            // Verify we have data
            $this->assertArrayHasKey( 'verse_text', $passage );
            $this->assertTrue( is_string( $passage['verse_text'] ) );
        }
    }

    /**
     * @test
     */
    public function it_can_parse_metadata_files()
    {
        // Test metadata parsing
        $metadata = $this->service->get_metadata();
        $this->assertNotEmpty( $metadata );
        $this->assertEquals( 'King James Version', $this->service->get_translation_name() );
        $this->assertEquals( 'KJV', $this->service->get_translation_abbreviation() );
        $this->assertEquals( 'eng', $this->service->get_language_code() );
        $this->assertEquals( 'English', $this->service->get_language_name() );
        $this->assertEquals( 'Bible', $this->service->get_scope() );

        // Test styles parsing
        $styles = $this->service->get_styles();
        $this->assertNotEmpty( $styles );
        $this->assertArrayHasKey( 'p', $styles );
        $this->assertEquals( 'Paragraph', $styles['p']['name'] );

        // Test versification parsing
        $versification = $this->service->get_versification();
        $this->assertNotEmpty( $versification );
        $this->assertArrayHasKey( 'GEN', $versification );

        // Test license parsing
        $license = $this->service->get_license();
        $this->assertNotEmpty( $license );
        $this->assertEquals( 'Public Domain', $license['title'] );
    }

    /**
     * @test
     */
    public function it_can_extract_zip_files()
    {
        $zip_path = __DIR__ . '/../fixtures/usx.zip';
        $upload_dir = sys_get_temp_dir() . '/bible-plugin-test';

        // Simulate wp_upload_dir
        \Patchwork\redefine('wp_upload_dir', function () use ( $upload_dir ) {
            return [ 'basedir' => $upload_dir ];
        });

        // Call the method
        $result = $this->service->extract_zip( $zip_path, 'FixtureName' );

        // Assert the file(s) got extracted
        $this->assertDirectoryExists( $result );
        $this->assertFileExists( $result . '/metadata.xml' );
    }
}
