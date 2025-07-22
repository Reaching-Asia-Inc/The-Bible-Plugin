<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\ApiServices\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Requests\RequestInterface as Request;
use CodeZone\Bible\Resources\Bibles;
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
    public function show( Request $request ): array {
        $bibles = container()->get( BibleBrainsBibles::class );
        $errors = validate($request, [
            'id' => 'required'
        ]);
        if ( $errors !== true ) {
            return [
                'code' => 400,
                'message'  => __( 'Please complete the required fields.', 'bible-plugin' ),
                'errors' => $errors,
            ];
        }
        return $bibles->find( $request->get( 'id' ) );
    }


    /**
     * Get bibles data as options.
     *
     * @param Request $request The request object.
     * @return array Data transformed into options format
     */
    public function options( Request $request ): array {
        $response = $this->index( $request );
        $bibles = container()->get( Bibles::class );
        return $bibles->as_options( $response ?? [] );
    }


    /**
     * Filters the given array of bibles based on a search term.
     *
     * @param array $bibles An array containing bible data, including a 'data' key holding the list to filter.
     * @param string|null $search A string representing the search term to filter the bible names.
     * @return array The filtered array of bibles containing only items where the name matches the search term.
     */
    protected function filter( array $bibles, ?string $search ): array
    {
        if ( empty( $search ) ) {
            return $bibles;
        }

        $bibles = array_filter(
            $bibles,
            fn( $bible ) => stripos( $bible['name'] ?? '', $search ) !== false
        );

        return $bibles;
    }


    /**
     * Handle the index route for bibles.
     *
     * @param Request $request The request data
     * @return array Response data containing filtered bible data
     */
    public function index( Request $request ): array
    {
        $bibles = container()->get( Bibles::class );

        $result = array_filter($bibles->all( [
            'page' => $request->get( 'paged', 1 ),
            'limit' => $request->get( 'limit', 50 ),
            'language_id' => $request->get( 'language' ),
        ] ));

        return $this->filter( $result, $request->get( 'search', '' ) );
    }
}
