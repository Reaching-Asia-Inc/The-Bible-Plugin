<?php

namespace Tests;

use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\GuzzleHttp\Handler\MockHandler;
use CodeZone\Bible\GuzzleHttp\HandlerStack;
use CodeZone\Bible\GuzzleHttp\Psr7\Response;
use CodeZone\Bible\Sources\Paratext\ParatextBibles;
use Faker;
use WP_UnitTestCase;
use function CodeZone\Bible\container;
use function CodeZone\Bible\namespace_string;
use function Patchwork\restoreAll;

abstract class TestCase extends WP_UnitTestCase {
    protected $original_wpdb;
    protected Faker\Generator $faker;
    protected bool $paratext_filter_added = false;

    protected static array $paratext_data = [];
    protected static array $paratext_dirs = [];

    public function __construct( ?string $name = null, array $data = [], $data_name = '' ) {
        $this->faker = Faker\Factory::create();
        parent::__construct( $name, $data, $data_name );
    }

    protected function setUp(): void {
        global $wpdb;
        parent::setUp();

        $this->original_wpdb = $wpdb;

        $this->mock_api_response( 'keys', new Response( 200, [], json_encode( $this->fixture( 'keys' ) ) ) );
        $this->mock_api_response( 'bibles', new Response( 200, [], json_encode( $this->fixture( 'bibles' ) ) ) );
        $this->mock_api_response( 'bibles/ENGESV', new Response( 200, [], json_encode( $this->fixture( 'bibles/ENGESV' ) ) ) );
        $this->mock_api_response( 'bibles/ENGKJV', new Response( 200, [], json_encode( $this->fixture( 'bibles/ENGKJV' ) ) ) );
        $this->mock_api_response( 'bibles/filesets/ENGKJVN1DA-opus16/JHN/3', new Response( 200, [], json_encode( $this->fixture( 'bibles/filesets/ENGKJVN1DA-opus16/JHN/3' ) ) ) );
        $this->mock_api_response( 'bibles/filesets/ENGKJVP2DV/JHN/3', new Response( 200, [], json_encode( $this->fixture( 'bibles/filesets/ENGKJVP2DV/JHN/3' ) ) ) );
        $this->mock_api_response( 'bibles/filesets/ENGKJVN_ET/JHN/3', new Response( 200, [], json_encode( $this->fixture( 'bibles/filesets/ENGKJVN_ET/JHN/3' ) ) ) );
        $this->mock_api_response( 'languages', new Response( 200, [], json_encode( $this->fixture( 'languages' ) ) ) );
        $this->mock_api_response( 'languages/6414', new Response( 200, [], json_encode( $this->fixture( 'languages/6414' ) ) ) );
    }

    protected function tearDown(): void {
        global $wpdb;

        restoreAll();
        //phpcs:ignore
        $wpdb = $this->original_wpdb;

        static::$paratext_dirs = [];
        static::$paratext_data = [];
        $this->paratext_filter_added = false;

        remove_filter( namespace_string( ParatextBibles::FILTER_HOOK ), [ $this, 'apply_paratext' ], 10 );

        parent::tearDown();
    }

    public function as_user( $username = null, $password = null, $email = null ) {
        $user = wp_create_user(
            $username ?? $this->faker->userName,
            $password ?? $this->faker->password,
            $email ?? $this->faker->email
        );
        $this->acting_as( $user );
        return $user;
    }

    public function acting_as( $user_id ): void {
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
    }

    public function mock_api_response( $endpoint, Response $response ): void {
        add_filter(namespace_string( 'api_client' ), function ( $client, $requested, $params ) use ( $endpoint, $response ) {
            if ( $requested === $endpoint ) {
                return new Client([
                    'handler' => HandlerStack::create( new MockHandler( [ $response ] ) )
                ]);
            }
            return $client;
        }, 10, 3);
    }

    public function fixture( $path ) {
        $file = __DIR__ . '/fixtures/' . $path . '.php';

        if ( file_exists( $file ) ) {
            return require $file;
        }

        fwrite( STDERR, "Fixture not found: {$file}" . PHP_EOL );
        exit;
    }

    public function load_paratext_fixture( $bible ): void {
        $dir = __DIR__ . '/fixtures/paratext/' . $bible;

        if ( !isset( static::$paratext_data[$dir] ) ) {
            static::$paratext_data[$dir] = container()->get( ParatextBibles::class )->parse_dir( $dir );
        }

        if ( !in_array( $dir, static::$paratext_dirs, true ) ) {
            static::$paratext_dirs[] = $dir;
        }

        if ( !$this->paratext_filter_added ) {
            add_filter( namespace_string( ParatextBibles::FILTER_HOOK ), [ $this, 'apply_paratext' ], 10, 1 );
            $this->paratext_filter_added = true;
        }
    }

    public function apply_paratext( $paratext_bibles ) {
        foreach ( static::$paratext_dirs as $dir ) {
            $data = static::$paratext_data[$dir];
            $paratext_bibles[$data['id']] = $data;
        }
        return $paratext_bibles;
    }
}
