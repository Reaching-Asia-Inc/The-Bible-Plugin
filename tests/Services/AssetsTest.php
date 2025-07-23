<?php

namespace Tests\Services;

use CodeZone\Bible\Services\Assets;
use CodeZone\Bible\Services\MediaTypes;
use Tests\TestCase;

/**
 * @group services
 * @group assets
 */
class AssetsTest extends TestCase {
    /**
     * @var Assets
     */
    protected $assets;

    /**
     * @var MediaTypes|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $media_types;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();

        // Mock the MediaTypes dependency
        $this->media_types = $this->createMock( MediaTypes::class );

        // Create the Assets service
        $this->assets = new Assets( $this->media_types );

        // Reset the static enqueued property
        $reflection = new \ReflectionClass( Assets::class );
        $property = $reflection->getProperty( 'enqueued' );
        $property->setAccessible( true );
        $property->setValue( null, false );
    }

    /**
     * @test
     */
    public function it_can_be_instantiated() {
        $this->assertInstanceOf( Assets::class, $this->assets );
    }

    /**
     * @test
     */
    public function it_registers_admin_hooks_when_in_admin() {
        // Mock is_admin() to return true
        \Patchwork\redefine('is_admin', function () {
            return true;
        });

        // Mock add_action to verify it's called with the right parameters
        $add_action_calls = [];
        \Patchwork\redefine('add_action', function ( $hook, $callback ) use ( &$add_action_calls ) {
            $add_action_calls[] = [
                'hook' => $hook,
                'callback' => $callback
            ];
            return true;
        });

        // Call the method
        $this->assets->enqueue();

        // Verify add_action was called with the right parameters
        $this->assertCount( 2, $add_action_calls );
        $this->assertEquals( 'admin_enqueue_scripts', $add_action_calls[0]['hook'] );
        $this->assertEquals( [ $this->assets, 'admin_enqueue_scripts' ], $add_action_calls[0]['callback'] );
        $this->assertEquals( 'admin_head', $add_action_calls[1]['hook'] );
        $this->assertEquals( [ $this->assets, 'cloak_style' ], $add_action_calls[1]['callback'] );
    }

    /**
     * @test
     */
    public function it_registers_frontend_hooks_when_not_in_admin() {
        // Mock is_admin() to return false
        \Patchwork\redefine('is_admin', function () {
            return false;
        });

        // Mock add_action to verify it's called with the right parameters
        $add_action_calls = [];
        \Patchwork\redefine('add_action', function ( $hook, $callback ) use ( &$add_action_calls ) {
            $add_action_calls[] = [
                'hook' => $hook,
                'callback' => $callback
            ];
            return true;
        });

        // Call the method
        $this->assets->enqueue();

        // Verify add_action was called with the right parameters
        $this->assertCount( 2, $add_action_calls );
        $this->assertEquals( 'wp_enqueue_scripts', $add_action_calls[0]['hook'] );
        $this->assertEquals( [ $this->assets, 'wp_enqueue_scripts' ], $add_action_calls[0]['callback'] );
        $this->assertEquals( 'wp_head', $add_action_calls[1]['hook'] );
        $this->assertEquals( [ $this->assets, 'cloak_style' ], $add_action_calls[1]['callback'] );
    }

    /**
     * @test
     */
    public function it_only_enqueues_once() {
        // Mock is_admin() to return true
        \Patchwork\redefine('is_admin', function () {
            return true;
        });

        // Mock add_action to count calls
        $add_action_call_count = 0;
        \Patchwork\redefine('add_action', function () use ( &$add_action_call_count ) {
            $add_action_call_count++;
            return true;
        });

        // Call the method twice
        $this->assets->enqueue();
        $this->assets->enqueue();

        // Verify add_action was only called for the first enqueue
        $this->assertEquals( 2, $add_action_call_count );
    }

    /**
     * @test
     */
    public function it_enqueues_frontend_scripts() {
        // Mock wp_script_is to return false (script not already enqueued)
        \Patchwork\redefine('wp_script_is', function () {
            return false;
        });

        // Mock enqueue_asset to verify it's called with the right parameters
        $enqueue_asset_called = false;
        \Patchwork\redefine('CodeZone\Bible\Kucrut\Vite\enqueue_asset', function ( $path, $entry, $options ) use ( &$enqueue_asset_called ) {
            $enqueue_asset_called = true;
            return true;
        });

        // Mock wp_localize_script
        $wp_localize_script_called = false;
        \Patchwork\redefine('wp_localize_script', function () use ( &$wp_localize_script_called ) {
            $wp_localize_script_called = true;
            return true;
        });

        // Mock wp_enqueue_style
        $wp_enqueue_style_called = false;
        \Patchwork\redefine('wp_enqueue_style', function () use ( &$wp_enqueue_style_called ) {
            $wp_enqueue_style_called = true;
            return true;
        });

        // Call the method
        $this->assets->wp_enqueue_scripts();

        // Verify the functions were called
        $this->assertTrue( $enqueue_asset_called );
        $this->assertTrue( $wp_localize_script_called );
        $this->assertTrue( $wp_enqueue_style_called );
    }

    /**
     * @test
     */
    public function it_skips_enqueuing_if_script_already_enqueued() {
        // Mock wp_script_is to return true (script already enqueued)
        \Patchwork\redefine('wp_script_is', function () {
            return true;
        });

        // Mock enqueue_asset to verify it's NOT called
        $enqueue_asset_called = false;
        \Patchwork\redefine('CodeZone\Bible\Kucrut\Vite\enqueue_asset', function () use ( &$enqueue_asset_called ) {
            $enqueue_asset_called = true;
            return true;
        });

        // Call the method
        $this->assets->wp_enqueue_scripts();

        // Verify enqueue_asset was not called
        $this->assertFalse( $enqueue_asset_called );
    }

    /**
     * @test
     */
    public function it_enqueues_admin_scripts() {
        // Mock enqueue_asset to verify it's called with the right parameters
        $enqueue_asset_called = false;
        \Patchwork\redefine('CodeZone\Bible\Kucrut\Vite\enqueue_asset', function ( $path, $entry, $options ) use ( &$enqueue_asset_called ) {
            $enqueue_asset_called = true;
            return true;
        });

        // Mock wp_localize_script
        $wp_localize_script_called = false;
        \Patchwork\redefine('wp_localize_script', function () use ( &$wp_localize_script_called ) {
            $wp_localize_script_called = true;
            return true;
        });

        // Call the method
        $this->assets->admin_enqueue_scripts();

        // Verify the functions were called
        $this->assertTrue( $enqueue_asset_called );
        $this->assertTrue( $wp_localize_script_called );
    }

    /**
     * @test
     */
    public function it_outputs_cloak_style() {
        // Capture the output
        ob_start();
        $this->assets->cloak_style();
        $output = ob_get_clean();

        // Verify the output contains the expected CSS
        $this->assertStringContainsString( '<style>', $output );
        $this->assertStringContainsString( '.tbp-cloak', $output );
        $this->assertStringContainsString( 'visibility: hidden', $output );
        $this->assertStringContainsString( '</style>', $output );
    }
}
