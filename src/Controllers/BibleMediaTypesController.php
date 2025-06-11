<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\RequestInterface as Request;
use function CodeZone\Bible\container;

/**
 * Index action for the controller.
 *
 * @param Request $request The HTTP request object.
 * @param Response $response The HTTP response object.
 * @param Bibles $bibles The Bibles service object.
 *
 * @return Response The HTTP response object.
 */
class BibleMediaTypesController {
    /**
     * Handle the media types index route.
     *
     * @param Request $request The request data
     * @return array Media types data
     */
    public function index(Request $request): array
    {
        $bibles = container()->get(Bibles::class);
        return $bibles->media_types()->json();
    }

    /**
     * Get media types as options.
     *
     * @param Request $request The request data
     * @return array Media type options
     */
    public function options(Request $request): array {
        $bibles = container()->get(Bibles::class);
        return $bibles->media_type_options();
    }
}
