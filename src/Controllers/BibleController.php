<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\Services\RequestInterface as Request;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use function CodeZone\Bible\container;

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
        return $bibles->find($request->get('id'));
    }


    /**
     * Get bibles data as options.
     *
     * @param Request $request The request object.
     * @return array Data transformed into options format
     */
    public function options(Request $request): array {
        $bibles = container()->get(Bibles::class);
        $result = $bibles->all(['query' => $request->get('query')]);

        return [
            'data' => $bibles->as_options($result['data'] ?? [])
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
     * @param array $request The request data
     * @return array Response data containing filtered bible data
     */
    public function index(array $request): array
    {
        $bibles = container()->get(Bibles::class);
        $params = [
            'page' => $request['paged'] ?? 1,
            'limit' => $request['limit'] ?? 50
        ];

        $language_code = $request['language_code'] ?? '';
        if ($language_code) {
            $language_codes = explode(',', $language_code);
            $result = $bibles->for_languages($language_codes, ['limit' => 150]);
        } else {
            $result = $bibles->all($params);
        }

        return $this->filter($result, $request['search'] ?? '');
    }

}
