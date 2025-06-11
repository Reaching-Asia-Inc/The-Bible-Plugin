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
class Request implements RequestInterface
{
    private array $get;
    private array $post;
    private string $method;

    /**
     * Initialize request data from PHP superglobals
     */
    public function __construct()
    {
        $this->get = wp_unslash($_GET);
        $this->post = wp_unslash($_POST);
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Magic method to allow property access to act like method calls
     *
     * @param string $name The parameter name to retrieve
     * @return mixed The parameter value
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Get all parameters based on request method
     *
     * @return array All request parameters
     */
    public function all(): array
    {
        return $this->method === 'POST' ? $this->post : $this->get;
    }

    /**
     * Get parameter value checking request method
     *
     * @param string|null $key The parameter key to retrieve
     * @param mixed|null $default Default value if parameter not found
     * @return mixed The parameter value or default
     */
    public function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->all();
        }

        $urlParam = $this->get_url_param($key);
        if ($urlParam !== null) {
            return $urlParam;
        }

        return $this->method === 'POST'
            ? $this->get_post($key, $default)
            : $this->get_query($key, $default);
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
     * Get specific GET parameter
     *
     * @param string $key The parameter key
     * @param mixed|null $default Default value if not found
     * @return mixed Sanitized parameter value or default
     */
    public function get_query(string $key, $default = null)
    {
        // Try WordPress query var first
        $query_var = get_query_var($key, null);
        if ($query_var !== null && $query_var !== '') {
            return sanitize_text_field($query_var);
        }

        return sanitize_text_field($this->get[$key] ?? $default);
    }

    /**
     * Get specific POST parameter
     *
     * @param string $key The parameter key
     * @param mixed|null $default Default value if not found
     * @return mixed Sanitized parameter value or default
     */
    public function get_post(string $key, $default = null)
    {
        if (!$this->is_post()) {
            return $default;
        }

        return sanitize_text_field($this->post[$key] ?? $default);
    }

    /**
     * Get request method
     *
     * @return string The HTTP request method
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Check if request is POST
     *
     * @return bool True if POST request
     */
    public function is_post(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Check if request is GET
     *
     * @return bool True if GET request
     */
    public function is_get(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * Retrieve URL parameters from the request
     *
     * @return array URL parameters
     */
    public function get_url_params()
    {
        return [];
    }

    /**
     * Get URL route parameter value
     * For routes like '/bibles/(?P<id>[\w-]+)'
     *
     * @param string $key Parameter key
     * @param mixed|null $default Default value if parameter not found
     * @return mixed Sanitized route parameter value or default
     */
    public function get_url_param(string $key, $default = null)
    {
        // URL parameters in WP_REST_Request are also accessed via get_param
        return sanitize_text_field($this->get_url_params()[$key] ?? $default);
    }
}
