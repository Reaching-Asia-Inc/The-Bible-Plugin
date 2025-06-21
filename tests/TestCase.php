<?php

namespace Tests;

use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\GuzzleHttp\Handler\MockHandler;
use CodeZone\Bible\GuzzleHttp\HandlerStack;
use CodeZone\Bible\GuzzleHttp\Psr7\Response;
use Faker;
use WP_UnitTestCase;
use function CodeZone\Bible\namespace_string;
use function Patchwork\restoreAll;

abstract class TestCase extends WP_UnitTestCase {
    protected $original_wpdb;

    protected function setup(): void {
        global $wpdb;

        parent::setup();
        $this->original_wpdb = $wpdb;
        $this->mock_api_response( 'keys', new Response( 200, [], json_encode( $this->fixture( 'keys' ) ) ) );
        $this->mock_api_response( 'bibles', new Response( 200, [], json_encode( $this->fixture( 'bibles' ) ) ) );
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
        $wpdb = $this->original_wpdb; // phpcs:ignore
        parent::tearDown();
    }

	/**
	 * Set up the test case by starting a transaction and calling the parent's setUp method.
	 *
	 * This method is called before each test method.
	 * It starts a transaction using the global $wpdb object and then calls the parent's setUp method.
	 *
	 * @return void
	 */
	protected Faker\Generator $faker;

	public function __construct( ?string $name = null, array $data = [], $data_name = '' ) {
		$this->faker = Faker\Factory::create();
		parent::__construct( $name, $data, $data_name );
	}

	/**
	 * Logs in as a new user and returns the user object.
	 *
	 * This method creates a new user using the given username, password, and email using the `wp_create_user` function.
	 * It then logs in as the newly created user using the `acting_as` method.
	 * Finally, it returns the user object of the newly created user.
	 *
	 * @return WP_User The user object of the newly created user.
	 */
	public function as_user( $username = null, $password = null, $email = null ) {
		$user = wp_create_user( $username ?? $this->faker->userName, $password ?? $this->faker->password, $email ?? $this->faker->email );
		$this->acting_as( $user );

		return $user;
	}

	/**
	 * Sets the current user and authenticates the user session as the specified user.
	 *
	 * @param int $user_id The ID of the user to act as.
	 *
	 * @return void
	 */
	public function acting_as( $user_id ) {
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );
	}

    /**
     * Mocks an API response for a specific endpoint by overriding the HTTP client.
     *
     * This function hooks into the plugin's `api_response` filter and returns a mock
     * Guzzle client with the provided response when the specified endpoint is requested.
     * Useful in tests to simulate remote API calls without making real HTTP requests.
     *
     * @param string   $endpoint The API endpoint (e.g., 'languages/6414') to intercept.
     * @param Response $response A Guzzle PSR-7 Response object to return as the mock response.
     *
     * @return void
     */
    public function mock_api_response( $endpoint, Response $response ) {
        add_filter(namespace_string( 'api_response' ), function ( $client, $requested, $params ) use ( $endpoint, $response ) {
            if ( $requested === $endpoint ) {
                return new Client([
                    'handler' => HandlerStack::create( new MockHandler( [ $response ] ) )
                ]);
            }
            return $client;
        }, 10, 3);
    }

    /**
     * Loads a test fixture file from the `fixtures/` directory.
     *
     * Fixtures are simple PHP files that return an array or object used as test data.
     * If the fixture does not exist, the function outputs an error message and exits.
     *
     * @param string $path The name of the fixture file (without `.php` or directory prefix).
     *
     * @return mixed The value returned by the fixture file.
     */
    public function fixture( $path ) {
        $file = __DIR__ . '/fixtures/' . $path . '.php';

        if ( file_exists( $file ) ) {
            return require $file;
        }

        fwrite( STDERR, "Fixture not found: {$file}" . PHP_EOL );
        exit;
    }
}
