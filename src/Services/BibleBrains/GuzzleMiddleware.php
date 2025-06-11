<?php

namespace CodeZone\Bible\Services\BibleBrains;

use CodeZone\Bible\GuzzleHttp\Psr7\Uri;
use CodeZone\Bible\GuzzleHttp\Psr7\UriResolver;
use CodeZone\Bible\Psr\Http\Message\RequestInterface;

/**
 * Class GuzzleMiddleware
 *
 * Represents a middleware for Guzzle requests.
 */
class GuzzleMiddleware {
	protected string $base_url = 'https://4.dbt.io/api/';
	protected string $key;


	/***
	 * @return void
	 */
	public function __construct(BibleBrainsKeys $keys) {
		$this->key = $keys->random();
	}


	/**
	 * Invoke the middleware handler.
	 *
	 * @param callable $handler The next middleware handler.
	 *
	 * @return callable Returns the modified handler.
	 */
	public function __invoke( callable $handler ) {
		return function ( RequestInterface $request, array $options ) use ( $handler ) {
			$new_uri = UriResolver::resolve( new Uri( $this->base_url ), $request->getUri() );

			parse_str( $new_uri->getQuery(), $query );

			// Add the 'key' query parameter
			if ( empty( $query['key'] ) ) {
				$new_uri = Uri::withQueryValue( $new_uri, 'key', $this->key );
			}
			if ( empty( $query['v'] ) ) {
				$new_uri = Uri::withQueryValue( $new_uri, 'v', '4' );
			}

			// Update the request with the modified URI
			$request = $request->withUri( $new_uri );

			// Call the next middleware handler
			return $handler( $request, $options );
		};
	}
}
