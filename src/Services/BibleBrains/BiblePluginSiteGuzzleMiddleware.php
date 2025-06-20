<?php

namespace CodeZone\Bible\Services\BibleBrains;

use CodeZone\Bible\GuzzleHttp\Psr7\Uri;
use CodeZone\Bible\GuzzleHttp\Psr7\UriResolver;
use CodeZone\Bible\Psr\Http\Message\RequestInterface;
use function CodeZone\Bible\dd;
use function CodeZone\Bible\plugin_path;

/**
 * Class GuzzleMiddleware
 *
 * Represents a middleware for Guzzle requests.
 */
class BiblePluginSiteGuzzleMiddleware
{
    protected string $base_url = 'https://thebibleplugin.com/wp-json/bible-plugin/v1/';
    private ?string $key = null;

    protected function generate_key(): string
    {
        if ( $this->key === null ) {
            $this->key = base64_encode( file_get_contents( plugin_path( 'bible-plugin.php' ) ) );
        }
        return $this->key;
    }

    public function __invoke( callable $handler )
    {
        return function ( RequestInterface $request, array $options ) use ( $handler ) {
            // Resolve the URI
            $new_uri = UriResolver::resolve(
                new Uri( $this->base_url ),
                $request->getUri()
            );

            // Add authorization header
            $request = $request->withHeader( 'Authorization', $this->generate_key() )
                ->withUri( $new_uri );

            return $handler( $request, $options );
        };
    }
}
