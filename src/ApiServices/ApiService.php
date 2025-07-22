<?php

namespace CodeZone\Bible\ApiServices;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\GuzzleHttp\Exception\GuzzleException;
use CodeZone\Bible\Services\Cache;
use function CodeZone\Bible\container;
use function CodeZone\Bible\namespace_string;

/**
 * Abstract base class for making HTTP requests to the ApiServices API.
 *
 * Provides common functionality for making API requests, handling responses,
 * caching, and transforming data into options format.
 */
abstract class ApiService {
    /**
     * HTTP client instance for making API requests.
     *
     * @var Client
     */
    protected ?Client $http = null;

    /**
     * Default options to be merged with request parameters.
     *
     * @var array
     */
    protected array $default_options = [];

    /**
     * Constructor.
     *
     * @param Client|null $http Optional HTTP client instance
     */
    public function __construct( ?Client $http = null ) {
        $this->http = $http;
    }

    /**
     * Initialize the API service with an HTTP client.
     *
     * @param Client|null $http Optional HTTP client instance
     */
    public function init( ?Client $http = null ): void {
        $this->http = $http;
    }

    /**
     * Retrieve the HTTP client instance.
     *
     * @return mixed The HTTP client instance
     */
    public function get_http() {
        return $this->http;
    }

    /**
     * Fetches the Guzzle Client
     *
     * @param string $endpoint The API endpoint to request
     * @param array $params Additional parameters for the request
     * @return mixed The response from the API client request
     */
    public function client( string $endpoint = '', array $params = [] ) {
        return apply_filters(
            namespace_string( 'api_client' ), $this->get_http(),
            $endpoint,
            $params
        );
    }

    /**
     * Make a GET request to the API endpoint.
     *
     * @param string $endpoint API endpoint path
     * @param array $params Query parameters
     * @return array          API response data
     * @throws BiblePluginException When request fails or response is invalid.
     */
    public function get( string $endpoint = '', array $params = [] ): array {
        $should_cache = $params['cache'] ?? true;
        $cache = container()->get( Cache::class );
        $client = $this->client( $endpoint, $params );

        if ( $should_cache ) {
            $cache_key = $endpoint . '?' . http_build_query( $params );
            $cached = $cache->get( $cache_key );

            if ( $cached ) {
                if (isset($cached['data'])) {
                    return $cached['data'];
                }

                return $cached;
            }
        }


        try {
            $response = $client->request( 'GET', $endpoint, [ 'query' => $params ] );
            $result = json_decode( $response->getBody()->getContents(), true );

            if ( !is_array( $result ) ) {
                throw new BiblePluginException( 'Invalid response format.' );
            }

            if ( !empty( $result['error'] ) ) {
                throw new BiblePluginException( $result['error']['message'] ?? $result['error'] );
            }

            if ( $should_cache ) {
                $cache->set( $cache_key, $result );
            }

            if (isset($result['data'])) {
                return $result['data'];
            }

            return $result;
        } catch ( GuzzleException $e ) {
            throw new BiblePluginException( 'HTTP request failed: ' . esc_html( $e->getMessage() ) );
        }
    }

    /**
     * Search records by name.
     *
     * @param string $name Search query
     * @param array $params Additional parameters
     * @return array        Search results
     */
    public function search( $name, array $params = [] ): array {
        $params = array_merge( $this->default_options, $params );
        return $this->get( $this->endpoint . '/search/' . $name, $params );
    }

    /**
     * Fetch all records (single page).
     *
     * @param array $params Query parameters
     * @return array       API response data
     */
    public function all( array $params = [] ): array {
        $params = array_merge( $this->default_options, $params );
        return $this->get( $this->endpoint, $params );
    }

    /**
     * Find multiple records by their IDs.
     *
     * @param array $ids Array of record IDs
     * @return array    Array of found records
     */
    public function find_many( array $ids ): array {
        $data = [];

        foreach ( $ids as $id ) {
            if ( !$id ) { continue;
            }

            try {
                $result = $this->get( $this->endpoint . '/' . $id );
                if ( !empty( $result ) ) {
                    $data[] = $result;
                }
            // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
            } catch ( BiblePluginException $e ) {
                // Ignore and continue
            }
        }

        return $data;
    }

    /**
     * Find a record by ID or multiple records by IDs.
     *
     * @param string|array $id Record ID or array of IDs
     * @param array $params Additional parameters
     * @return array              Found record(s)
     */
    public function find( $id, array $params = [] ): array {
        if ( is_array( $id ) ) {
            return $this->find_many( $id );
        }

        return $this->get( $this->endpoint . '/' . $id, $params );
    }
}
