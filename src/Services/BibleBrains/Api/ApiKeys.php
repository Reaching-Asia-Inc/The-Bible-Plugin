<?php

namespace CodeZone\Bible\Services\BibleBrains\Api;

use CodeZone\Bible\GuzzleHttp\Client;
use function CodeZone\Bible\container;

class ApiKeys extends ApiService
{
    protected string $endpoint = 'keys';

    /**
     * Initializes the HTTP client using the scoped container service.
     *
     * @param Client|null $http Optional Guzzle client override.
     * @return void
     */
    public function init(Client $http = null): void
    {
        $this->http = $http ?? container()->get('http.biblePluginSite');
    }
}
