<?php

namespace CodeZone\Bible\Sources\BibleBrains;

use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\Sources\ApiSource;
use function CodeZone\Bible\container;

/**
 * Abstract base class for making HTTP requests to the ApiServices API.
 *
 * Provides common functionality for making API requests, handling responses,
 * caching, and transforming data into options format.
 */
abstract class BibleBrainsSource extends ApiSource {
    /**
     * HTTP client instance for making API requests.
     *
     * @var Client
     */
    protected ?Client $http = null;

    /**
     * Retrieves the HTTP client instance.
     *
     * If the HTTP client instance is already set, it returns the existing instance.
     * Otherwise, it fetches and returns the HTTP client from the container under the key 'http.bibleBrains'.
     *
     * @return mixed The HTTP client instance.
     */
    public function get_http() {
        return $this->http ?? container()->get( 'http.bibleBrains' );
    }
}
