<?php

namespace CodeZone\Bible\Services\BibleBrains\Api;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\Cache;
use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\GuzzleHttp\Exception\GuzzleException;
use function CodeZone\Bible\container;

/**
 * Abstract base class for making HTTP requests to the BibleBrains API.
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
    protected Client $http;

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
        $this->init( $http );
    }

    /**
     * Initialize the API service with an HTTP client.
     *
     * @param Client|null $http Optional HTTP client instance
     */
    public function init( ?Client $http = null ): void {
        $this->http = $http ?? container()->get( 'http.bibleBrains' );
    }

    /**
     * Make a GET request to the API endpoint.
     *
     * @param string $endpoint API endpoint path
     * @param array $params Query parameters
     * @return array          API response data
     * @throws BibleBrainsException When request fails or response is invalid.
     */
    public function get( string $endpoint = '', array $params = [] ): array {
        $should_cache = $params['cache'] ?? true;
        $cache = container()->get( Cache::class );

        if ( $should_cache ) {
            $cache_key = $endpoint . '?' . http_build_query( $params );
            $cached = $cache->get( $cache_key );
;

            if ( $cached ) {
                return $cached;
            }
        }


        try {
            $response = $this->http->request( 'GET', $endpoint, [ 'query' => $params ] );
            $result = json_decode( $response->getBody()->getContents(), true );

            if ( !is_array( $result ) ) {
                throw new BibleBrainsException( 'Invalid response format.' );
            }

            if ( !empty( $result['error'] ) ) {
                throw new BibleBrainsException( $result['error']['message'] ?? $result['error'] );
            }

            if ( $should_cache ) {
                $cache->set( $cache_key, $result );
            }

            return $result;
        } catch ( GuzzleException $e ) {
            throw new BibleBrainsException( 'HTTP request failed: ' . esc_html( $e->getMessage() ) );
        }
    }

    /**
     * Transforms an array of records into a format suitable for use as options.
     *
     * @param array $records List of records to transform
     * @return array          Transformed array of options
     */
    public function as_options( array $records ): array {
        return array_values(array_filter(
            array_map( [ $this, 'map_option' ], $records ),
            fn( $option ) => !empty( $option['value'] ) && !empty( $option['itemText'] )
        ));
    }

    /**
     * Map a single record to an option format.
     *
     * @param array $record Record to transform
     * @return array       Option with value and itemText
     */
    public function map_option( array $record ): array {
        return [
            'value'    => (string) ( $record['id'] ?? '' ),
            'itemText' => (string) ( $record['name'] ?? '' ),
        ];
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
     * Fetch all pages of data from the API.
     *
     * @param array $params Query parameters
     * @return array       Combined data from all pages
     */
    public function all_pages( array $params = [] ): array {
        $page = 1;
        $params = array_merge( $this->default_options, $params );
        $all_data = [];

        do {
            $params['page'] = $page;
            $response = $this->get( $this->endpoint, $params );
            $data = $response['data'] ?? [];

            foreach ( $data as $item ) {
                $all_data[] = $item;
            }

            $total_pages = $response['meta']['pagination']['total_pages'] ?? 1;
            $page++;
        } while ( $page <= $total_pages );

        return $all_data;
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
                if ( !empty( $result['data'] ) ) {
                    $data[] = $result['data'];
                }
            // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
            } catch ( BibleBrainsException $e ) {
                // Ignore and continue
            }
        }

        return [ 'data' => $data ];
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
