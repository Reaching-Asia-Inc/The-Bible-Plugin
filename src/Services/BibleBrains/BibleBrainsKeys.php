<?php

namespace CodeZone\Bible\Services\BibleBrains;

use CodeZone\Bible\GuzzleHttp\ClientInterface;
use CodeZone\Bible\Services\BibleBrains\Api\ApiKeys;
use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface as Options;
use CodeZone\Bible\Services\Cache;
use function CodeZone\Bible\namespace_string;
use function CodeZone\Bible\plugin_path;

/**
 * Bible Brains API Keys Service
 */
class BibleBrainsKeys {
    protected Options $options;
    const OPTION_KEY = 'bible_brains_key';
    protected ClientInterface $client;
    protected Cache $cache;
    protected $key = null;

    /**
     * Constructor method for initializing the class instance
     *
     * @param Options $options The options object used for configuring the class
     * @param ClientInterface $client The API keys object used for accessing the endpoints
     * @param Cache $cache The cache service.
     */
    public function __construct( Options $options, ClientInterface $client, Cache $cache )
    {
        $this->options = $options;
        $this->client = $client;
        $this->cache = $cache;
    }

    protected function generate_key(): string
    {
        if ( $this->key === null ) {
            $this->key = base64_encode( file_get_contents( plugin_path( 'bible-plugin.php' ) ) );
        }
        return $this->key;
    }

    /**
     * Fetches data from a remote endpoint.
     *
     * @return array The data fetched from the remote endpoint.
     */
    public function fetch_remote(): array {
        $endpoint = 'https://thebibleplugin.com/wp-json/bible-plugin/v1/keys';
        $key = namespace_string( $endpoint );
        $cached = $this->cache->get( $key );
        if ( $cached ) {
            return $cached;
        }

        $response = $this->client->get( $endpoint, [
            'headers' => [
               'Authorization' => $this->generate_key()
            ]
        ] );
        $result = json_decode( $response->getBody()->getContents(), true );


        if ( !is_array( $result ) ) {
            throw new \Exception( 'Invalid response format.' );
        }

        if ( ! count( $result ) ) {
            return $result;
        }

        $this->cache->set( $key, $result );

        return $result;
    }

    /**
     * Has remote keys.
     */
    public function has_remote_keys(): bool
    {
        return !empty( $this->fetch_remote() );
    }

    /**
     * Returns all keys.
     *
     * @param bool $override Whether to override or not.
     *
     * @return array An array of items.
     */
    public function all( $override = true ) {

        // Check if the override constant is set and return the override if it is
        if ( $override && $this->has_override() ) {
            return $this->get_override();
        }

        // Check if the keys are set as an option is set and return the option if it is
        if ( $this->has_option() ) {
            $option = $this->get_option();

            if ( $option ) {
                return [ $this->get_option() ];
            }
        }

        // Fetch the remote keys if no other options are set
        try {
            return $this->fetch_remote();
        } catch ( \Exception $e ) {
            return [];
        }
    }

    /**
     * Returns a random key.
     *
     * @param bool $override Whether to override or not.
     *
     * @return mixed A random key.
     */
    public function random( $override = true ) {
        $keys = $this->all( $override );
        if ( !$keys ) {
            return null;
        }
        return $keys[ array_rand( $keys ) ];
    }

    /**
     * Check if the option exists.
     *
     * @return bool Whether the option exists or not.
     */
    public function has_option() {
        return $this->options->get( self::OPTION_KEY, false ) !== false;
    }

    /**
     * Get the value of the option.
     *
     * @return mixed The value of the option.
     */
    public function get_option() {
        return $this->options->get( self::OPTION_KEY );
    }

    /**
     * Get the override keys.
     *
     * @return array The override keys as an array.
     */
    public function get_override() {
        return array_filter( explode(
            ',',
            defined( 'TBP_BIBLE_BRAINS_KEYS' ) ? TBP_BIBLE_BRAINS_KEYS : ''
        ) );
    }

    /**
     * Check if there is an override.
     *
     * @return bool Whether there is an override or not.
     */
    public function has_override(): bool {
        return ! empty( $this->get_override() );
    }

    /**
     * Check if the field is readonly.
     *
     * @return bool Whether the field is readonly or not.
     */
    public function is_field_readonly() {
        return $this->has_override();
    }

    /**
     * Get the instructions for the field.
     *
     * If the field is readonly, returns the message indicating it is readonly.
     * Otherwise, returns an empty string.
     *
     * @return string The instructions for the field.
     */
    public function field_instructions() {
        return $this->is_field_readonly() ? __( 'This field is read-only because the key is set via the TBP_BIBLE_BRAINS_KEYS constant in your wp-config.php file.', 'tbp' ) : '';
    }

    /**
     * Get the field value.
     *
     * If there is an override, return a masked value.
     * Otherwise, return the option value.
     *
     * @return string The field value.
     */
    public function field_value() {
        if ( $this->has_override() ) {
           return "********-****-****-****-************";
        } else {
            return $this->get_option();
        }
    }
}
