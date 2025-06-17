<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\Services\RequestInterface as Request;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use function CodeZone\Bible\container;
use function CodeZone\Bible\validate;

/**
 * Class BibleController
 *
 * The BibleController class is responsible for handling requests related to Bible operations.
 *
 * @package YourNamespace\YourPackageName
 */
class BibleController {
    /**
     * Retrieve a bible.
     *
     * @param Request $request The request object.
     * @return array Bible data
     */
    public function show(Request $request): array {
        $bibles = container()->get(Bibles::class);
        $errors = validate($request, [
            'id' => 'required'
        ]);
        if ($errors !== true) {
            wp_send_json_error([
                'message'  => __('Please complete the required fields.', 'bible-plugin'),
                'data' => $errors,
            ], 400);
            exit;
        }
        return $bibles->find($request->get('id'));
    }


    /**
     * Get bibles data as options.
     *
     * @param Request $request The request object.
     * @return array Data transformed into options format
     */
    public function options(Request $request): array {
        $response = $this->index( $request );
        $bibles = container()->get(Bibles::class);

        return [
            'data' => $bibles->as_options( $response['data'] ?? [] )
        ];
    }


    /**
     * Filters the given array of bibles based on a search term.
     *
     * @param array $bibles An array containing bible data, including a 'data' key holding the list to filter.
     * @param string|null $search A string representing the search term to filter the bible names.
     * @return array The filtered array of bibles containing only items where the name matches the search term.
     */
    protected function filter(array $bibles, ?string $search): array
    {
        if (empty($search)) {
            return $bibles;
        }

        $bibles['data'] = array_filter(
            $bibles['data'] ?? [],
            fn($bible) => stripos($bible['name'] ?? '', $search) !== false
        );

        return $bibles;
    }


    /**
     * Handle the index route for bibles.
     *
     * @param Request $request The request data
     * @return array Response data containing filtered bible data
     */
    public function index(Request $request): array
    {
        $bibles = container()->get(Bibles::class);
        $params = [
            'page' => $request->get('paged', 1),
            'limit' => $request->get('limit', 50)
        ];

        $language_code = $request->get('language_code', '');
        if ($language_code) {
            $language_codes = explode(',', $language_code);
            $result = $bibles->for_languages($language_codes, ['limit' => 150]);
        } else {
            $result = $bibles->all($params);
        }

        return $this->filter($result, $request->get('search', ''));
    }

}
