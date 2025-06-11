<?php

namespace CodeZone\Bible\Services;

use WP_REST_Request;

/**
 * Class RestRequest
 * A wrapper class that provides utility methods for interacting with a WP_REST_Request instance.
 * Implements the RequestInterface.
 */
class RestRequest implements RequestInterface
{
    /**
     * The WordPress REST request instance
     *
     * @var WP_REST_Request
     */
    private WP_REST_Request $request;

    /**
     * Constructor
     *
     * @param WP_REST_Request $request The WordPress REST request instance
     */
    public function __construct(WP_REST_Request $request)
    {
        $this->request = $request;
    }

    /**
     * Magic getter method to access request parameters
     * First checks URL parameters then falls back to regular parameters
     *
     * @param string $name The parameter name
     * @return mixed The parameter value
     */
    public function __get(string $name)
    {
        return $this->get_url_param($name) ?? $this->get($name);
    }

    /**
     * Get all request parameters merged with URL parameters
     *
     * @return array All request parameters
     */
    public function all(): array
    {
        return array_merge(
            $this->request->get_params(),
            $this->get_url_params()
        );
    }

    /**
     * Get a request parameter value
     * Checks URL parameters first then falls back to regular parameters
     *
     * @param string|null $key The parameter key
     * @param mixed $default Default value if parameter not found
     * @return mixed The parameter value
     */
    public function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->all();
        }

        $route_param = $this->get_url_param($key);

        if ($route_param) {
            return $route_param;
        }

        return sanitize_text_field($this->request->get_param($key) ?? $default);
    }

    /**
     * Determine if a given key exists and is not null
     *
     * @param string $key The key to check for existence
     * @return bool True if the key exists and is not null, otherwise false
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Determine if a given key exists and is not null
     *
     * @param string $key The key to check for existence
     * @return bool True if the key exists and is not null, otherwise false
     */
    public function is_string(string $key): bool
    {
        if (!$this->has($key)) {
            return false;
        }

        return is_string($this->get($key));
    }

    /**
     * Get a query string parameter value
     *
     * @param string $key The parameter key
     * @param mixed $default Default value if parameter not found
     * @return mixed The sanitized parameter value
     */
    public function get_query(string $key, $default = null)
    {
        return sanitize_text_field($this->request->get_query_params()[$key] ?? $default);
    }

    /**
     * Get a POST parameter value
     *
     * @param string $key The parameter key
     * @param mixed $default Default value if parameter not found
     * @return mixed The sanitized parameter value
     */
    public function get_post(string $key, $default = null)
    {
        return sanitize_text_field($this->request->get_body_params()[$key] ?? $default);
    }

    /**
     * Get the request HTTP method
     *
     * @return string The HTTP method (GET, POST, etc)
     */
    public function method(): string
    {
        return $this->request->get_method();
    }

    /**
     * Check if request method is POST
     *
     * @return bool True if POST request
     */
    public function is_post(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Check if request method is GET
     *
     * @return bool True if GET request
     */
    public function is_get(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Get all URL parameters
     *
     * @return array URL parameters array
     */
    public function get_url_params(): array
    {
        return $this->request->get_url_params();
    }

    /**
     * Get a URL parameter value
     *
     * @param string $key The parameter key
     * @param mixed $default Default value if parameter not found
     * @return mixed The sanitized parameter value
     */
    public function get_url_param(string $key, $default = null)
    {
        return sanitize_text_field($this->get_url_params()[$key] ?? $default);
    }
}
