<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\Services\BibleBrains\Scripture;
use CodeZone\Bible\Services\RequestInterface as Request;
use function CodeZone\Bible\container;
use Exception;

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
            if (!$request->has('reference') || !$request->is_string('reference')) {
                return [
                    'status' => 400,
                    'errors' => [
                        'reference' => __('Reference must be a valid string', 'bible-plugin')
                    ]
                ];
            }

            $scripture = container()->get(Scripture::class);
            return $scripture->by_reference($request->get('reference'));

        } catch (Exception $e) {
            return [
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
    }

}
