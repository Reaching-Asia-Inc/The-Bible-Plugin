<?php

namespace CodeZone\Bible\Guzzle;

use CodeZone\Bible\GuzzleHttp\Psr7\Uri;
use CodeZone\Bible\GuzzleHttp\Psr7\UriResolver;
use CodeZone\Bible\Psr\Http\Message\RequestInterface;
use CodeZone\Bible\Services\BibleBrainsKeys;

/**
 * Class BibleBrainsMiddleware
 *
 * Represents a middleware for Guzzle requests to the ApiServices API.
 * This middleware handles request URL manipulation and API key management.
 *
 * @package CodeZone\Bible\Services\ApiServices\BibleBrains
 */
class BibleBrainsMiddleware
{
    /**
     * Base URL for the ApiServices API.
     *
     * @var string
     */
    protected string $base_url = 'https://4.dbt.io/api/';

    /**
     * Current API key being used.
     *
     * @var string|null
     */
    protected ?string $key = null;

    /**
     * BibleBrainsApiKeys service for managing API keys.
     *
     * @var BibleBrainsKeys
     */
    protected BibleBrainsKeys $keys;

    /**
     * Constructor.
     *
     * @param BibleBrainsKeys $keys Service for managing API keys
     */
    public function __construct( BibleBrainsKeys $keys )
    {
        $this->keys = $keys;
    }

    /**
     * Gets an API key, either using existing one or fetching a new random key.
     *
     * @return string The API key
     */
    protected function get_key(): string
    {
        if ( $this->key === null ) {
            $this->key = $this->keys->random();
        }
        return $this->key;
    }

    /**
     * Invokes the middleware.
     *
     * @param callable $handler Next handler in middleware chain
     * @return callable Returns a function that processes the request
     */
    public function __invoke( callable $handler )
    {
        return function ( RequestInterface $request, array $options ) use ( $handler ) {
            $new_uri = UriResolver::resolve( new Uri( $this->base_url ), $request->getUri() );

            parse_str( $new_uri->getQuery(), $query );

            // Add the 'key' query parameter
            if ( empty( $query['key'] ) ) {
                $new_uri = Uri::withQueryValue( $new_uri, 'key', $this->get_key() );
            }
            if ( empty( $query['v'] ) ) {
                $new_uri = Uri::withQueryValue( $new_uri, 'v', '4' );
            }

            $request = $request->withUri( $new_uri );

            return $handler( $request, $options );
        };
    }
}
