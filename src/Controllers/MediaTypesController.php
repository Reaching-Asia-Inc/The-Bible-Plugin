<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\BibleBrains\MediaTypes;
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
class MediaTypesController {
    /**
     * Handle the media types index route.
     *
     * @param Request $request The request data
     * @return array Media types data
     */
    public function index( Request $request ): array
    {
        $media_types = container()->get( MediaTypes::class );
        return $media_types->all();
    }

    /**
     * Get media types as options.
     *
     * @param Request $request The request data
     * @return array Media type options
     */
    public function options( Request $request ): array {
        $media_types = container()->get( MediaTypes::class );
        return $media_types->options();
    }
}
