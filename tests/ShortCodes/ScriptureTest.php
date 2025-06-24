<?php

namespace Tests\ShortCodes;

use CodeZone\Bible\Services\BibleBrains\Video;
use Tests\TestCase;
use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\Assets;
use CodeZone\Bible\Services\BibleBrains\Language;
use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use CodeZone\Bible\Services\BibleBrains\Scripture as ScriptureService;
use CodeZone\Bible\ShortCodes\Scripture;
use function Patchwork\redefine;

/**
 * @group shortcodes
 * @group scriptures
 */
class ScriptureTest extends TestCase
{
    /**
     * @test
     */
    public function it_renders_scripture_shortcode()
    {
        // Create mock dependencies
        $scripture_service = $this->createMock( ScriptureService::class );
        $assets = $this->createMock( Assets::class );
        $media_types = $this->createMock( MediaTypes::class );
        $language = $this->createMock( Language::class );
        $video = $this->createMock( Video::class );

        // Configure the media_types mock
        $media_types->method( 'exists' )
            ->with( 'text' )
            ->willReturn( true );

        // Configure the scripture_service mock
        $scripture_service->method( 'by_reference' )
            ->with( 'John 3:16', [ 'language' => '', 'bible' => '' ] )
            ->willReturn([
                'media' => [
                    'text' => [
                        'content' => [
                            'data' => [
                                [
                                    'verse_text' => 'For God so loved the world...',
                                    'verse_number' => '16'
                                ]
                            ]
                        ],
                        'fileset' => [
                            'type' => 'text_plain'
                        ]
                    ]
                ],
                'bible' => [
                    'alphabet' => [
                        'direction' => 'ltr'
                    ]
                ],
                'verse_start' => '16',
                'verse_end' => '16',
                'chapter' => '3',
                'book' => [
                    'name' => 'John'
                ]
            ]);

        // Create the Scripture shortcode
        $scripture = new Scripture( $scripture_service, $assets, $media_types, $language, $video );

        // Call the render method with default attributes
        $result = $scripture->render([
            'reference' => 'John 3:16'
        ]);

        // Assert that the result is a string
        $this->assertIsString( $result );

        // Assert that the result contains expected content
        $this->assertStringContainsString( 'tbp-content', $result );
        $this->assertStringContainsString( 'tbp__shortcode', $result );
        $this->assertStringContainsString( 'For God so loved the world', $result );
    }

    /**
     * @test
     */
    public function it_handles_invalid_media_type()
    {
        // Create mock dependencies
        $scripture_service = $this->createMock( ScriptureService::class );
        $assets = $this->createMock( Assets::class );
        $media_types = $this->createMock( MediaTypes::class );
        $language = $this->createMock( Language::class );
        $video = $this->createMock( Video::class );


        // Configure the media_types mock to return false for invalid media type
        $media_types->method( 'exists' )
            ->with( 'invalid_media' )
            ->willReturn( false );

        // Create the Scripture shortcode
        $scripture = new Scripture( $scripture_service, $assets, $media_types, $language, $video );

        // Call the render method with invalid media type
        $result = $scripture->render([
            'reference' => 'John 3:16',
            'media' => 'invalid_media'
        ]);

        // Assert that the result is a string
        $this->assertIsString( $result );

        // Assert that the result contains error message
        $this->assertStringContainsString( 'Invalid media type', $result );
    }

    /**
     * @test
     */
    public function it_handles_scripture_service_exception()
    {
        // Create mock dependencies
        $scripture_service = $this->createMock( ScriptureService::class );
        $assets = $this->createMock( Assets::class );
        $media_types = $this->createMock( MediaTypes::class );
        $language = $this->createMock( Language::class );
        $video = $this->createMock( Video::class );

        // Configure the media_types mock
        $media_types->method( 'exists' )
            ->with( 'text' )
            ->willReturn( true );

        // Configure the scripture_service mock to throw an exception
        $scripture_service->method( 'by_reference' )
            ->willThrowException( new BibleBrainsException( 'Invalid reference' ) );

        // Create the Scripture shortcode
        $scripture = new Scripture( $scripture_service, $assets, $media_types, $language, $video );

        // Call the render method
        $result = $scripture->render([
            'reference' => 'Invalid Reference'
        ]);

        // Assert that the result is a string
        $this->assertIsString( $result );

        // Assert that the result contains error message
        $this->assertStringContainsString( 'Invalid reference', $result );
    }

    /**
     * @test
     */
    public function it_handles_empty_results()
    {
        // Create mock dependencies
        $scripture_service = $this->createMock( ScriptureService::class );
        $assets = $this->createMock( Assets::class );
        $media_types = $this->createMock( MediaTypes::class );
        $language = $this->createMock( Language::class );
        $video = $this->createMock( Video::class );

        // Configure the media_types mock
        $media_types->method( 'exists' )
            ->with( 'text' )
            ->willReturn( true );

        // Configure the scripture_service mock to return empty results
        $scripture_service->method( 'by_reference' )
            ->willReturn([
                'media' => []
            ]);

        // Create the Scripture shortcode
        $scripture = new Scripture( $scripture_service, $assets, $media_types, $language, $video );

        // Call the render method
        $result = $scripture->render([
            'reference' => 'John 3:16'
        ]);

        // Assert that the result is a string
        $this->assertIsString( $result );

        // Assert that the result contains error message
        $this->assertStringContainsString( 'No results found', $result );
    }

    /**
     * @test
     */
    public function it_enqueues_scripts_when_shortcode_is_used()
    {
        // Create mock dependencies
        $scripture_service = $this->createMock( ScriptureService::class );
        $assets = $this->createMock( Assets::class );
        $media_types = $this->createMock( MediaTypes::class );
        $language = $this->createMock( Language::class );
        $video = $this->createMock( Video::class );

        // Configure the assets mock to expect wp_enqueue_scripts call
        $assets->expects( $this->once() )
            ->method( 'wp_enqueue_scripts' );

        // Create the Scripture shortcode
        $scripture = new Scripture( $scripture_service, $assets, $media_types, $language, $video );

        // Mock has_shortcode to return true
        redefine('has_shortcode', function () {
            return true;
        });

        // Mock get_the_content to return content with shortcode
        redefine('get_the_content', function () {
            return 'Some content with [tbp-scripture] shortcode';
        });

        // Call the enqueue_scripts method
        $scripture->enqueue_scripts();
    }

    /**
     * @test
     */
    public function it_does_not_enqueue_scripts_when_shortcode_is_not_used()
    {
        // Create mock dependencies
        $scripture_service = $this->createMock( ScriptureService::class );
        $assets = $this->createMock( Assets::class );
        $media_types = $this->createMock( MediaTypes::class );
        $language = $this->createMock( Language::class );
        $video = $this->createMock( Video::class );

        // Configure the assets mock to expect wp_enqueue_scripts NOT to be called
        $assets->expects( $this->never() )
            ->method( 'wp_enqueue_scripts' );

        // Create the Scripture shortcode
        $scripture = new Scripture( $scripture_service, $assets, $media_types, $language, $video );

        // Mock has_shortcode to return false
        redefine('has_shortcode', function () {
            return false;
        });

        // Mock get_the_content to return content without shortcode
        redefine('get_the_content', function () {
            return 'Some content without shortcode';
        });

        // Call the enqueue_scripts method
        $scripture->enqueue_scripts();
    }

    /**
     * @test
     */
    public function it_registers_shortcode_on_init()
    {
        // Create mock dependencies
        $scripture_service = $this->createMock( ScriptureService::class );
        $assets = $this->createMock( Assets::class );
        $media_types = $this->createMock( MediaTypes::class );
        $language = $this->createMock( Language::class );
        $video = $this->createMock( Video::class );

        // Create the Scripture shortcode
        $scripture = new Scripture( $scripture_service, $assets, $media_types, $language, $video );

        // Mock add_shortcode to verify it's called with the right parameters
        $add_shortcode_called = false;
        $shortcode_tag = '';
        $callback = null;

        redefine('add_shortcode', function ( $tag, $func ) use ( &$add_shortcode_called, &$shortcode_tag, &$callback ) {
            $add_shortcode_called = true;
            $shortcode_tag = $tag;
            $callback = $func;
            return true;
        });

        // Call the init method
        $scripture->init();

        // Assert that add_shortcode was called with the right parameters
        $this->assertTrue( $add_shortcode_called );
        $this->assertEquals( 'tbp-scripture', $shortcode_tag );
        $this->assertIsArray( $callback );
        $this->assertSame( $scripture, $callback[0] );
        $this->assertEquals( 'render', $callback[1] );
    }
}
