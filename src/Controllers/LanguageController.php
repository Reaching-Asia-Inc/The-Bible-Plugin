<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\Requests\RequestInterface as Request;
use CodeZone\Bible\Resources\Language;
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
        $languages = container()->get( Language::class );
        $language = $languages->find( $request->get( 'id' ) );
        if ( !$language ) {
            return [
                'code' => 404,
                'error' => 'Language not found.'
            ];
        }
        return $language;
    }

    /**
     * Get languages formatted as select options.
     *
     * @param Request $request The request object
     * @return array BibleBrainsLanguages as select options
     */
    public function options( Request $request ): array
    {
        $languages = container()->get( Language::class );
        $response = $this->index( $request );
        return $languages->as_options( $response ?? [] );
    }


    /**
     * Get list of languages, optionally filtered by search.
     *
     * @param Request $request The request object
     * @return array List of languages with pagination
     */
    public function index( Request $request ): array
    {
        $languages = container()->get( Language::class );

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
