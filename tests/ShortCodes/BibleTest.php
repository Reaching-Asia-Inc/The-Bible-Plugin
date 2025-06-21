<?php

namespace Tests\ShortCodes;

use Tests\TestCase;
use CodeZone\Bible\Services\Assets;
use CodeZone\Bible\Services\Request;
use CodeZone\Bible\ShortCodes\Bible;
use function Patchwork\redefine;

/**
 * @group shortcodes
 * @group bibles
 */
class BibleTest extends TestCase
{
    /**
     * @test
     */
    public function it_renders_bible_shortcode()
    {
        // Create mock dependencies
        $assets = $this->createMock( Assets::class );
        $request = $this->createMock( Request::class );

        // Use Patchwork to redefine the Request class methods
        redefine('CodeZone\Bible\Services\Request::has', function ( $param ) {
            if ( $param === 'reference' ) {
                return false;
            }
            return false;
        });

        // Create the Bible shortcode
        $bible = new Bible( $assets, $request );

        // Call the render method with default attributes
        $result = $bible->render( [] );

        // Assert that the result is a string
        $this->assertIsString( $result );

        // Assert that the result contains expected content
        $this->assertStringContainsString( 'tbp-bible', $result );
        $this->assertStringContainsString( 'John 1', $result ); // Default reference
    }

    /**
     * @test
     */
    public function it_renders_bible_shortcode_with_custom_reference()
    {
        // Create mock dependencies
        $assets = $this->createMock( Assets::class );
        $request = $this->createMock( Request::class );

        // Use Patchwork to redefine the Request class methods
        redefine('CodeZone\Bible\Services\Request::has', function ( $param ) {
            if ( $param === 'reference' ) {
                return false;
            }
            return false;
        });

        // Create the Bible shortcode
        $bible = new Bible( $assets, $request );

        // Call the render method with custom attributes
        $result = $bible->render([
            'reference' => 'Romans 8:28'
        ]);

        // Assert that the result is a string
        $this->assertIsString( $result );

        // Assert that the result contains expected content
        $this->assertStringContainsString( 'tbp-bible', $result );
        $this->assertStringContainsString( 'Romans 8:28', $result );
    }

    /**
     * @test
     */
    public function it_uses_reference_from_request_if_available()
    {
        // Create mock dependencies
        $assets = $this->createMock( Assets::class );

        // Create a custom Request mock that actually works
        $request = new class() extends Request {
            public function has( string $key ): bool {
                if ( $key === 'reference' ) {
                    return true;
                }
                return false;
            }

            public function get( ?string $key = null, $default = null ) {
                if ( $key === 'reference' ) {
                    return 'Psalm 23';
                }
                return $default;
            }
        };

        // Create the Bible shortcode
        $bible = new Bible( $assets, $request );

        // Call the render method
        $result = $bible->render( [] );

        // Assert that the result is a string
        $this->assertIsString( $result );

        // Assert that the result contains expected content
        $this->assertStringContainsString( 'tbp-bible', $result );
        $this->assertStringContainsString( 'Psalm 23', $result );
    }

    /**
     * @test
     */
    public function it_enqueues_scripts_when_shortcode_is_used()
    {
        // Create mock dependencies
        $assets = $this->createMock( Assets::class );
        $request = $this->createMock( Request::class );

        // Configure the assets mock to expect wp_enqueue_scripts call
        $assets->expects( $this->once() )
            ->method( 'wp_enqueue_scripts' );

        // Create the Bible shortcode
        $bible = new Bible( $assets, $request );

        // Mock has_shortcode to return true
        redefine('has_shortcode', function () {
            return true;
        });

        // Mock get_the_content to return content with shortcode
        redefine('get_the_content', function () {
            return 'Some content with [tbp-bible] shortcode';
        });

        // Call the enqueue_scripts method
        $bible->enqueue_scripts();
    }

    /**
     * @test
     */
    public function it_does_not_enqueue_scripts_when_shortcode_is_not_used()
    {
        // Create mock dependencies
        $assets = $this->createMock( Assets::class );
        $request = $this->createMock( Request::class );

        // Configure the assets mock to expect wp_enqueue_scripts NOT to be called
        $assets->expects( $this->never() )
            ->method( 'wp_enqueue_scripts' );

        // Create the Bible shortcode
        $bible = new Bible( $assets, $request );

        // Mock has_shortcode to return false
        redefine('has_shortcode', function () {
            return false;
        });

        // Mock get_the_content to return content without shortcode
        redefine('get_the_content', function () {
            return 'Some content without shortcode';
        });

        // Call the enqueue_scripts method
        $bible->enqueue_scripts();
    }

    /**
     * @test
     */
    public function it_registers_shortcode_on_init()
    {
        // Create mock dependencies
        $assets = $this->createMock( Assets::class );
        $request = $this->createMock( Request::class );

        // Create the Bible shortcode
        $bible = new Bible( $assets, $request );

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
        $bible->init();

        // Assert that add_shortcode was called with the right parameters
        $this->assertTrue( $add_shortcode_called );
        $this->assertEquals( 'tbp-bible', $shortcode_tag );
        $this->assertIsArray( $callback );
        $this->assertSame( $bible, $callback[0] );
        $this->assertEquals( 'render', $callback[1] );
    }
}
