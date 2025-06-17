<?php

namespace CodeZone\Bible\Services\BibleBrains\Api;

use CodeZone\Bible\GuzzleHttp\Client;
use function CodeZone\Bible\container;

class ApiKeys extends ApiService
{
    protected string $endpoint = 'keys';
}
