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
class Request extends BaseRequest implements RequestInterface
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
     * Retrieve all POST data
     *
     * @return array An associative array containing all POST data
     */
    public function all_post(): array
    {
        return $this->post;
    }

    /**
     * Retrieve all GET parameters
     *
     * @return array The array of GET parameters
     */
    public function all_get(): array
    {
        return $this->get;
    }

    /**
     * Retrieve URL parameters from the request
     *
     * @return array URL parameters
     */
    public function all_url_params()
    {
        return [];
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
}
