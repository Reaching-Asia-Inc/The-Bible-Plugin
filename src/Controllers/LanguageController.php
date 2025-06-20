<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\Services\BibleBrains\Api\Languages;
use CodeZone\Bible\Services\RequestInterface as Request;
use function CodeZone\Bible\container;

/**
 * Class LanguageController
 *
 * The LanguageController class is responsible for handling language-related requests
 */
class LanguageController
{
    /**
     * Get a specific language by ID.
     *
     * @param Request $request The request object
     * @return array Language data
     */
    public function show( Request $request ): array
    {
        $languages = container()->get( Languages::class );
        return $languages->find( $request->get( 'id' ) );
    }

    /**
     * Get languages formatted as select options.
     *
     * @param Request $request The request object
     * @return array Languages as select options
     */
    public function options( Request $request ): array
    {
        $languages = container()->get( Languages::class );
        $response = $this->index( $request );
        return [
            'data' => $languages->as_options( $response['data'] ?? [] )
        ];
    }


    /**
     * Get list of languages, optionally filtered by search.
     *
     * @param Request $request The request object
     * @return array List of languages with pagination
     */
    public function index( Request $request ): array
    {
        $languages = container()->get( Languages::class );

        $search = $request->get( 'search' );
        if ( $search ) {
            return $languages->search( $search );
        }

        return $languages->all([
            'page' => $request->get( 'paged', 1 ),
            'limit' => $request->get( 'limit', 50 )
        ]);
    }
}
