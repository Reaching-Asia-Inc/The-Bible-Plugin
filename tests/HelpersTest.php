<?php

namespace Tests;

use CodeZone\Bible\Plugin;
use CodeZone\Bible\League\Container\Container;
use CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface;
use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface;
use CodeZone\Bible\League\Plates\Engine;
use CodeZone\Bible\Psr\Http\Message\ResponseInterface;
use CodeZone\Bible\CodeZone\WPSupport\Router\ResponseFactory;
use CodeZone\Bible\Services\Validator;
use function CodeZone\Bible\transaction;
use function Patchwork\redefine;

/**
 * @group helpers
 */
class HelpersTest extends TestCase
{
    /**
     * @test
     */
    public function plugin_returns_plugin_instance()
    {
        $plugin = $this->getMockBuilder( Plugin::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( Plugin::class )
            ->willReturn( $plugin );

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the plugin function
        $result = \CodeZone\Bible\plugin();

        // Check that the result is the plugin instance
        $this->assertSame( $plugin, $result );
    }

    /**
     * @test
     */
    public function container_returns_container_instance()
    {
        // Call the container function
        $result = \CodeZone\Bible\container();

        // Check that the result is a Container instance
        $this->assertInstanceOf( Container::class, $result );
    }

    /**
     * @test
     */
    public function config_returns_config_interface_when_no_key_provided()
    {
        $config = $this->getMockBuilder( ConfigInterface::class )
            ->getMock();

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( ConfigInterface::class )
            ->willReturn( $config );

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the config function without a key
        $result = \CodeZone\Bible\config();

        // Check that the result is the config instance
        $this->assertSame( $config, $result );
    }

    /**
     * @test
     */
    public function config_returns_value_when_key_provided()
    {
        $config = $this->getMockBuilder( ConfigInterface::class )
            ->getMock();

        $config->expects( $this->once() )
            ->method( 'get' )
            ->with( 'test.key', null )
            ->willReturn( 'test_value' );

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( ConfigInterface::class )
            ->willReturn( $config );

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the config function with a key
        $result = \CodeZone\Bible\config( 'test.key' );

        // Check that the result is the expected value
        $this->assertEquals( 'test_value', $result );
    }

    /**
     * @test
     */
    public function set_config_sets_config_value()
    {
        $config = $this->getMockBuilder( ConfigInterface::class )
            ->getMock();

        $config->expects( $this->once() )
            ->method( 'set' )
            ->with( 'test.key', 'test_value' )
            ->willReturn( true );

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( ConfigInterface::class )
            ->willReturn( $config );

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the set_config function
        $result = \CodeZone\Bible\set_config( 'test.key', 'test_value' );

        // Check that the result is true
        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function plugin_url_returns_correct_url()
    {
        // Call the plugin_url function
        $result = \CodeZone\Bible\plugin_url( 'test/path' );

        // Check that the result is the expected URL
        $this->assertEquals( 'http://example.org/wp-content/plugins/bible-plugin/test/path', $result );
    }

    /**
     * @test
     */
    public function api_url_returns_base_url_when_no_path_provided()
    {
        // Mock the rest_url function
        redefine('rest_url', function ( $path ) {
            return "https://example.com/wp-json/{$path}";
        });

        // Call the api_url function without a path
        $result = \CodeZone\Bible\api_url();

        // Check that the result is the expected URL
        $this->assertEquals( 'https://example.com/wp-json/' . \CodeZone\Bible\Services\RestApi::PATH, $result );
    }

    /**
     * @test
     */
    public function api_url_returns_full_url_when_path_provided()
    {
        // Mock the rest_url function
        redefine('rest_url', function ( $path ) {
            return "https://example.com/wp-json/{$path}";
        });

        // Call the api_url function with a path
        $result = \CodeZone\Bible\api_url( 'test/path' );

        // Check that the result is the expected URL
        $this->assertEquals( 'https://example.com/wp-json/' . \CodeZone\Bible\Services\RestApi::PATH . '/test/path', $result );
    }

    /**
     * @test
     */
    public function rgb_returns_rgb_format_when_hex_provided()
    {
        // Test with 6-digit hex
        $result1 = \CodeZone\Bible\rgb( '#FFFFFF' );
        $this->assertEquals( 'rgb(255, 255, 255)', $result1 );

        // Test with 3-digit hex
        $result2 = \CodeZone\Bible\rgb( '#FFF' );
        $this->assertEquals( 'rgb(255, 255, 255)', $result2 );

        // Test without hash
        $result3 = \CodeZone\Bible\rgb( '000000' );
        $this->assertEquals( 'rgb(0, 0, 0)', $result3 );
    }

    /**
     * @test
     */
    public function rgb_returns_unchanged_when_rgb_provided()
    {
        $rgb = 'rgb(100, 150, 200)';
        $result = \CodeZone\Bible\rgb( $rgb );
        $this->assertEquals( $rgb, $result );
    }

    /**
     * @test
     */
    public function cast_bool_values_converts_string_booleans_to_actual_booleans()
    {
        $input = [
            'true_value' => 'true',
            'false_value' => 'false',
            'other_value' => 'something else'
        ];

        $expected = [
            'true_value' => true,
            'false_value' => false,
            'other_value' => 'something else'
        ];

        $result = \CodeZone\Bible\cast_bool_values( $input );
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function validate_calls_validator_service()
    {
        $validator = $this->getMockBuilder( Validator::class )
            ->disableOriginalConstructor()
            ->getMock();

        $validator->expects( $this->once() )
            ->method( 'validate' )
            ->with( [ 'test' => 'data' ], [ 'test' => 'required' ] )
            ->willReturn( true );

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( Validator::class )
            ->willReturn( $validator );

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the validate function
        $result = \CodeZone\Bible\validate( [ 'test' => 'data' ], [ 'test' => 'required' ] );

        // Check that the result is true
        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function plugin_path_returns_correct_path()
    {
        // Mock the Plugin::dir_path method
        redefine('CodeZone\Bible\Plugin::dir_path', function () {
            return '/path/to/plugin';
        });

        // Call the plugin_path function
        $result = \CodeZone\Bible\plugin_path( 'test/path' );

        // Check that the result is the expected path
        $this->assertEquals( '/path/to/plugin/test/path', $result );
    }

    /**
     * @test
     */
    public function src_path_returns_correct_path()
    {
        // For multiple return values, use a static variable to track calls
        redefine('function_name', function () {
            static $calls = 0;
            return $calls++ === 0 ? 'First time I run' : 'Second time I run';
        });

        // Mock the config function
        redefine('CodeZone\Bible\config', function ( $key ) {
            if ( $key === 'plugin.paths.src' ) {
                return 'src';
            }
            return null;
        });

        // Mock the plugin_path function
        redefine('CodeZone\Bible\plugin_path', function ( $path ) {
            return '/path/to/plugin/' . $path;
        });

        // Call the src_path function
        $result = \CodeZone\Bible\src_path( 'test/path' );

        // Check that the result is the expected path
        $this->assertEquals( '/path/to/plugin/src/test/path', $result );
    }

    /**
     * @test
     */
    public function resources_path_returns_correct_path()
    {
        // Mock the config function
        redefine('CodeZone\Bible\config', function ( $key ) {
            if ( $key === 'plugin.paths.resources' ) {
                return 'resources';
            }
            return null;
        });

        // Mock the plugin_path function
        redefine('CodeZone\Bible\plugin_path', function ( $path ) {
            return '/path/to/plugin/' . $path;
        });

        // Call the resources_path function
        $result = \CodeZone\Bible\resources_path( 'test/path' );

        // Check that the result is the expected path
        $this->assertEquals( '/path/to/plugin/resources/test/path', $result );
    }

    /**
     * @test
     */
    public function admin_path_returns_correct_path()
    {
        // Mock the get_admin_url function
        redefine('get_admin_url', function ( $blog_id, $path ) {
            return "https://example.com/wp-admin/{$path}";
        });

        // Call the admin_path function
        $result = \CodeZone\Bible\admin_path( 'test/path' );

        // Check that the result is the expected path
        $this->assertEquals( 'wp-admin/test/path', $result );
    }

    /**
     * @test
     */
    public function languages_path_returns_correct_path()
    {
        // Mock the plugin_path function
        redefine('CodeZone\Bible\plugin_path', function ( $path ) {
            return '/path/to/plugin/' . $path;
        });

        // Call the languages_path function
        $result = \CodeZone\Bible\languages_path( 'test/path' );

        // Check that the result is the expected path
        $this->assertEquals( '/path/to/plugin/languages/test/path', $result );
    }

    /**
     * @test
     */
    public function routes_path_returns_correct_path()
    {
        // Mock the config function
        redefine('CodeZone\Bible\config', function ( $key ) {
            if ( $key === 'plugin.paths.routes' ) {
                return 'routes';
            }
            return null;
        });

        // Mock the plugin_path function
        redefine('CodeZone\Bible\plugin_path', function ( $path ) {
            return '/path/to/plugin/' . $path;
        });

        // Call the routes_path function
        $result = \CodeZone\Bible\routes_path( 'test/path' );

        // Check that the result is the expected path
        $this->assertEquals( '/path/to/plugin/routes/test/path', $result );
    }

    /**
     * @test
     */
    public function views_path_returns_correct_path()
    {
        // Mock the config function
        redefine('CodeZone\Bible\config', function ( $key ) {
            if ( $key === 'plugin.paths.views' ) {
                return 'views';
            }
            return null;
        });

        // Mock the plugin_path function
        redefine('CodeZone\Bible\plugin_path', function ( $path ) {
            return '/path/to/plugin/' . $path;
        });

        // Call the views_path function
        $result = \CodeZone\Bible\views_path( 'test/path' );

        // Check that the result is the expected path
        $this->assertEquals( '/path/to/plugin/views/test/path', $result );
    }

    /**
     * @test
     */
    public function view_returns_engine_when_no_view_provided()
    {
        $engine = $this->getMockBuilder( Engine::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( Engine::class )
            ->willReturn( $engine );

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the view function without a view
        $result = \CodeZone\Bible\view();

        // Check that the result is the engine instance
        $this->assertSame( $engine, $result );
    }

    /**
     * @test
     */
    public function view_renders_view_when_view_provided()
    {
        $engine = $this->getMockBuilder( Engine::class )
            ->disableOriginalConstructor()
            ->getMock();

        $engine->expects( $this->once() )
            ->method( 'render' )
            ->with( 'test', [ 'test' => 'data' ] )
            ->willReturn( '<html>Test</html>' );

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( Engine::class )
            ->willReturn( $engine );

        // Mock the apply_filters function
        redefine('apply_filters', function ( $tag, ...$args ) {
            if ( $tag === 'bible-plugin.before_render_view' ) {
                return $args[0]; // Return the data unchanged
            }
            if ( $tag === 'bible-plugin.after_render_view' ) {
                return $args[0]; // Return the HTML unchanged
            }
            return $args[0];
        });

        // Mock the namespace_string function
        redefine('CodeZone\Bible\namespace_string', function ( $string ) {
            return 'bible-plugin.' . $string;
        });

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the view function with a view
        $result = \CodeZone\Bible\view( 'test', [ 'test' => 'data' ] );

        // Check that the result is the rendered HTML
        $this->assertEquals( '<html>Test</html>', $result );
    }

    /**
     * @test
     */
    public function redirect_returns_response_interface()
    {
        $response = $this->getMockBuilder( ResponseInterface::class )
            ->getMock();

        // Mock the ResponseFactory::redirect method
        redefine('CodeZone\Bible\CodeZone\WPSupport\Router\ResponseFactory::redirect', function ( $url, $status, $headers ) use ( $response ) {
            return $response;
        });

        // Call the redirect function
        $result = \CodeZone\Bible\redirect( 'https://example.com', 301, [ 'X-Test' => 'test' ] );

        // Check that the result is the response instance
        $this->assertSame( $response, $result );
    }

    /**
     * @test
     */
    public function set_option_adds_option_when_it_does_not_exist()
    {
        // Mock the get_option function
        redefine('get_option', function ( $option ) {
            if ( $option === 'blog_charset' ) {
                return 'UTF-8'; // Return a valid charset
            }
            return false; // Option doesn't exist
        });

        // Mock the add_option function
        redefine('add_option', function ( $option, $value ) {
            return true;
        });

        // Call the set_option function
        $result = \CodeZone\Bible\set_option( 'test_option', 'test_value' );

        // Check that the result is true
        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function set_option_updates_option_when_it_exists()
    {
        // Mock the get_option function
        redefine('get_option', function ( $option ) {
            if ( $option === 'blog_charset' ) {
                return 'UTF-8'; // Return a valid charset
            }
            return 'some_existing_value'; // Option exists
        });

        // Mock the update_option function
        redefine('update_option', function ( $option, $value ) {
            return true;
        });

        // Call the set_option function
        $result = \CodeZone\Bible\set_option( 'test_option', 'test_value' );

        // Check that the result is true
        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function get_plugin_option_returns_option_value()
    {
        $options = $this->getMockBuilder( OptionsInterface::class )
            ->getMock();

        $options->expects( $this->once() )
            ->method( 'get' )
            ->with( 'test_option', null, false )
            ->willReturn( 'test_value' );

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( OptionsInterface::class )
            ->willReturn( $options );

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the get_plugin_option function
        $result = \CodeZone\Bible\get_plugin_option( 'test_option' );

        // Check that the result is the expected value
        $this->assertEquals( 'test_value', $result );
    }

    /**
     * @test
     */
    public function set_plugin_option_sets_option_value()
    {
        $options = $this->getMockBuilder( OptionsInterface::class )
            ->getMock();

        $options->expects( $this->once() )
            ->method( 'set' )
            ->with( 'test_option', 'test_value' )
            ->willReturn( true );

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( OptionsInterface::class )
            ->willReturn( $options );

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the set_plugin_option function
        $result = \CodeZone\Bible\set_plugin_option( 'test_option', 'test_value' );

        // Check that the result is true
        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function transaction_executes_callback_and_commits()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'test_transactions';

        // Drop any existing test table
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

        // Create a transaction-supporting table
        $wpdb->query("CREATE TABLE {$table_name} (
        id INT AUTO_INCREMENT PRIMARY KEY,
        value VARCHAR(255)
    ) ENGINE=InnoDB");

        // Run a successful transaction
        $result = transaction(function () use ( $wpdb, $table_name ) {
            $wpdb->insert( $table_name, [ 'value' => 'test1' ], [ '%s' ] );
            $wpdb->insert( $table_name, [ 'value' => 'test2' ], [ '%s' ] );
            return true;
        });

        $this->assertTrue( $result );
        $this->assertEquals( 2, (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ) );

        // Capture initial count before testing rollback
        $initial_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

        // Run a failing transaction that should roll back
        try {
            transaction(function () use ( $wpdb, $table_name ) {
                $wpdb->insert( $table_name, [ 'value' => 'test3' ], [ '%s' ] );
                throw new \Exception( 'Test failure' );
            });
        // phpcs:ignore
        } catch ( \Exception $e ) {
            // Intentionally ignored
        }

        // Verify that the insert inside the failed transaction was rolled back
        $this->assertEquals( $initial_count, (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ) );

        // Clean up
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
    }

    /**
     * @test
     */
    public function transaction_rolls_back_on_error()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'test_transactions';

        // Drop the table first to ensure a clean environment
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

        // Create table with UNIQUE constraint and InnoDB engine
        $wpdb->query("CREATE TABLE {$table_name} (
        id INT AUTO_INCREMENT PRIMARY KEY,
        value VARCHAR(255) UNIQUE
    ) ENGINE=InnoDB");

        // Insert initial value
        $wpdb->insert(
            $table_name,
            [ 'value' => 'test_duplicate' ],
            [ '%s' ]
        );

        // Run transaction that attempts to insert a duplicate value
        $result = transaction(function () use ( $wpdb, $table_name ) {
            return $wpdb->insert(
                $table_name,
                [ 'value' => 'test_duplicate' ],
                [ '%s' ]
            );
        });

        // Assert that an error message was returned (rollback triggered)
        $this->assertIsString( $result );
        $this->assertNotEmpty( $result );

        // Assert that only the original row exists
        $row_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
        $this->assertEquals( 1, $row_count, 'Transaction did not roll back as expected.' );

        // Clean up
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
    }

    /**
     * @test
     */
    public function translate_calls_translations_service()
    {
        $translations = $this->getMockBuilder( \CodeZone\Bible\Services\Translations::class )
            ->disableOriginalConstructor()
            ->getMock();

        $translations->expects( $this->once() )
            ->method( 'translate' )
            ->with( 'Hello', [ 'context' => 'test' ] )
            ->willReturn( 'Translated Hello' );

        $container = $this->getMockBuilder( Container::class )
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( \CodeZone\Bible\Services\Translations::class )
            ->willReturn( $translations );

        // Replace the container with our mock
        redefine('CodeZone\Bible\container', function () use ( $container ) {
            return $container;
        });

        // Call the translate function
        $result = \CodeZone\Bible\translate( 'Hello', [ 'context' => 'test' ] );

        // Check that the result is the translated text
        $this->assertEquals( 'Translated Hello', $result );
    }

    /**
     * @test
     */
    public function namespace_string_returns_namespaced_string()
    {
        // Mock the config function
        redefine('CodeZone\Bible\config', function ( $key ) {
            if ( $key === 'plugin.text_domain' ) {
                return 'bible-plugin';
            }
            return null;
        });

        // Call the namespace_string function
        $result = \CodeZone\Bible\namespace_string( 'test' );

        // Check that the result is the expected namespaced string
        $this->assertEquals( 'bible-plugin.test', $result );
    }

    /**
     * Helper method to mock a static method
     */
    private function mockStaticMethod( $class, $method, $replacement )
    {
        // This is a placeholder - in a real test, you would need to find a way
        // to mock static methods
    }

    /**
     * Helper method to mock a function
     */
    private function mockFunction( $function, $replacement )
    {
        // This is a placeholder - in a real test, you would need to find a way
        // to mock functions
    }
}
