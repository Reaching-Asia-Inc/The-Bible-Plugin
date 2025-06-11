<?php

namespace CodeZone\Bible\Services;

/**
 * Interface for handling HTTP requests and accessing request parameters.
 */
interface RequestInterface
{
    /**
     * Get a request parameter value by key. If no key is provided, returns all parameters.
     *
     * @param string|null $key Parameter key to retrieve
     * @param mixed|null $default Default value if parameter not found
     * @return mixed              Parameter value or default if not found
     */
    public function get(string $key = null, $default = null);

    /**
     * Check if a specified key exists.
     *
     * @param string $key The key to check for existence.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool;


    /**
     * Determine if a given key exists and is not null
     *
     * @param string $key The key to check for existence
     * @return bool True if the key exists and is not null, otherwise false
     */
    public function is_string(string $key): bool;

    /**
     * Get a query string parameter value by key.
     *
     * @param string $key Parameter key to retrieve
     * @param mixed|null $default Default value if parameter not found
     * @return mixed             Query parameter value or default if not found
     */
    public function get_query(string $key, $default = null);

    /**
     * Get a POST parameter value by key.
     *
     * @param string $key Parameter key to retrieve
     * @param mixed|null $default Default value if parameter not found
     * @return mixed             POST parameter value or default if not found
     */
    public function get_post(string $key, $default = null);

    /**
     * Get all request parameters as an array.
     *
     * @return array All request parameters
     */
    public function all(): array;

    /**
     * Get the HTTP request method.
     *
     * @return string HTTP method (GET, POST etc)
     */
    public function method(): string;

    /**
     * Check if request method is POST.
     *
     * @return bool True if POST request, false otherwise
     */
    public function is_post(): bool;

    /**
     * Check if request method is GET.
     *
     * @return bool True if GET request, false otherwise
     */
    public function is_get(): bool;

    /**
     * Get a URL parameter value by key.
     *
     * @param string $key Parameter key to retrieve
     * @param mixed|null $default Default value if parameter not found
     * @return mixed             URL parameter value or default if not found
     */
    public function get_url_param(string $key, $default = null);
}
