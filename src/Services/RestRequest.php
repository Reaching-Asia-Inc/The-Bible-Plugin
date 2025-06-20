<?php

namespace CodeZone\Bible\Services;

use WP_REST_Request;

/**
 * Class RestRequest
 * A wrapper class that provides utility methods for interacting with a WP_REST_Request instance.
 * Implements the RequestInterface.
 */
class RestRequest extends BaseRequest implements RequestInterface
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
    public function __construct( WP_REST_Request $request )
    {
        $this->request = $request;
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
     * Get all URL parameters
     *
     * @return array URL parameters array
     */
    public function all_url_params(): array
    {
        return $this->request->get_url_params();
    }

    public function all_post(): array
    {
        if ( wp_is_json_request() ) {
            return $this->request->get_json_params();
        }
        return $this->request->get_body_params();
    }

    public function all_get(): array
    {
        return $this->request->get_query_params();
    }
}
