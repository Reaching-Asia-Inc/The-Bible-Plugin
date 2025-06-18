<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\Services\BibleBrains\Scripture;
use CodeZone\Bible\Services\BibleBrains\Video;
use CodeZone\Bible\Services\RequestInterface as Request;
use Exception;
use function CodeZone\Bible\container;
use function CodeZone\Bible\validate;

/**
 * Index method for ScriptureController
 *
 * @param Request $request The request object
 * @param Response $response The response object
 * @param Scripture $scripture The Scripture object
 *
 * @return Response The response object containing the result or error
 *
 * @throws \Exception If an exception occurs while fetching the Scripture by reference
 */
class ScriptureController
{

    /**
     * Get scripture by reference.
     *
     * @param Request $request The request object
     * @return array Scripture data or error response
     */
    public function index(Request $request): array
    {
        try {
           $errors = validate($request, [
               'reference' => 'required|string',
               'video' => 'boolean'
           ]);

           if ($errors !== true) {
               wp_send_json_error([
                   'message'  => __('Invalid request.', 'bible-plugin'),
                   'data' => $errors,
               ], 400);
               exit;
           }

            $scripture = container()->get(Scripture::class);
            $content = $scripture->by_reference($request->reference);
            if ($request->video) {
                $video = container()->get(Video::class);
                $content = $video->hydrate_content($content);
            }
            return $content;

        } catch (Exception $e) {
            return [
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
    }

}
