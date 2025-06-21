<?php

namespace CodeZone\Bible\Services;

/**
 * WordPress Request handling class
 *
 * Wraps and sanitizes access to request parameters including:
 * - GET parameters ($_GET)
 * - POST parameters ($_POST)
 * - URL/route parameters
 */
abstract class BaseRequest implements RequestInterface
{
    /**
     * Magic method to allow property access to act like method calls
     *
     * @param string $name The parameter name to retrieve
     * @return mixed The parameter value
     */
    public function __get( string $name )
    {
        return $this->get( $name );
    }

    /**
     * Get all parameters based on request method
     *
     * @return array All request parameters
     */
    public function all(): array
    {
        return array_merge(
            $this->method() === 'POST' ? $this->all_post() : $this->all_get(),
            $this->all_url_params()
        );
    }

    /**
     * Get parameter value checking request method
     *
     * @param string|null $key The parameter key to retrieve
     * @param mixed|null $default Default value if parameter not found
     * @return mixed The parameter value or default
     */
    public function get( ?string $key = null, $default = null )
    {
        if ( $key === null ) {
            return $this->all();
        }

        $url_param = $this->get_url_param( $key );
        if ( $url_param !== null ) {
            return $url_param;
        }

        return $this->cast(
            strtoupper( $this->method() ) === 'POST'
                ? $this->get_post( $key, $default )
                : $this->get_query( $key, $default )
        );
    }

    /**
     * Casts the given value to a sanitized string if it is of type string.
     *
     * @param mixed $value The value to be cast and sanitized
     * @return string|null The sanitized string if the input is a string, otherwise null
     */
    protected function cast( $value ) {
        if ( $value === "true" ) {
            return true;
        }

        if ( $value === "false" ) {
            return false;
        }

        return $value;
    }

    /**
     * Determine if a given key exists and is not null
     *
     * @param string $key The key to check for existence
     * @return bool True if the key exists and is not null, otherwise false
     */
    public function has( string $key ): bool
    {
        return $this->get( $key ) !== null;
    }

    /**
     * Determine if a given key exists and is not null
     *
     * @param string $key The key to check for existence
     * @return bool True if the key exists and is not null, otherwise false
     */
    public function is_string( string $key ): bool
    {
        if ( !$this->has( $key ) ) {
            return false;
        }

        return is_string( $this->get( $key ) );
    }

    /**
     * Get specific GET parameter
     *
     * @param string $key The parameter key
     * @param mixed|null $default Default value if not found
     * @return mixed Sanitized parameter value or default
     */
    public function get_query( string $key, $default = null )
    {
        // Try WordPress query var first
        $query_var = get_query_var( $key, null );
        if ( $query_var !== null && $query_var !== '' ) {
            return $this->sanitize( $query_var );
        }

        return $this->sanitize( $this->all_get()[$key] ?? $default );
    }

    /**
     * Get specific POST parameter
     *
     * @param string $key The parameter key
     * @param mixed|null $default Default value if not found
     * @return mixed Sanitized parameter value or default
     */
    public function get_post( string $key, $default = null )
    {
        if ( !$this->is_post() ) {
            return $default;
        }

        return $this->sanitize( $this->all_post()[$key] ?? $default );
    }

    /**
     * Sanitize a given value by processing it based on its type.
     *
     * @param mixed $value The value to be sanitized
     * @return mixed The sanitized value
     */
    public function sanitize( $value ) {
        if ( is_string( $value ) ) {
            return sanitize_text_field( $value );
        }

        if ( is_array( $value ) ) {
            return array_map( [ $this, 'sanitize' ], $value );
        }

        if ( is_numeric( $value ) ) {
            return is_float( $value ) ? (float) $value : (int) $value;
        }

        if ( is_bool( $value ) ) {
            return (bool) $value;
        }

        if ( is_null( $value ) ) {
            return null;
        }

        // For any other type, convert to string and sanitize
        if ( is_object( $value ) || is_resource( $value ) ) {
            return sanitize_text_field( (string) $value );
        }

        return $value;
    }

    /**
     * Check if request is POST
     *
     * @return bool True if POST request
     */
    public function is_post(): bool
    {
        return strtoupper( $this->method() ) === 'POST';
    }

    /**
     * Check if request is GET
     *
     * @return bool True if GET request
     */
    public function is_get(): bool
    {
        return strtoupper( $this->method() ) === 'GET';
    }


    /**
     * Get URL route parameter value
     * For routes like '/bibles/(?P<id>[\w-]+)'
     *
     * @param string $key Parameter key
     * @param mixed|null $default Default value if parameter not found
     * @return mixed Sanitized route parameter value or default
     */
    public function get_url_param( string $key, $default = null )
    {
        // URL parameters in WP_REST_Request are also accessed via get_param
        return $this->sanitize( $this->all_url_params()[$key] ?? $default );
    }
}
