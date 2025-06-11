<?php

namespace CodeZone\Bible\Services\BibleBrains;

use CodeZone\Bible\GuzzleHttp\Psr7\Uri;
use CodeZone\Bible\GuzzleHttp\Psr7\UriResolver;
use CodeZone\Bible\Psr\Http\Message\RequestInterface;
use function CodeZone\Bible\plugin_path;

/**
 * Class GuzzleMiddleware
 *
 * Represents a middleware for Guzzle requests.
 */
class BiblePluginSiteGuzzleMiddleware {
    protected string $base_url = 'https://thebibleplugin.com/wp-json/bible-plugin/v1/';

    public function generate_key() {
        return base64_encode( file_get_contents( plugin_path( 'bible-plugin.php' ) ) );
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
            $request = $request->withHeader( 'Authorization', $this->generate_key() );
            // Update the request with the modified URI
            $request = $request->withUri( $new_uri );

            return $handler( $request, $options );
        };
    }
}
