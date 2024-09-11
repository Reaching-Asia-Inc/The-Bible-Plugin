<?php

namespace CodeZone\Bible\Controllers;

use CodeZone\Bible\Illuminate\Http\Request;
use CodeZone\Bible\Illuminate\Http\Response;
use CodeZone\Bible\Services\BibleBrains\Language;
use CodeZone\Bible\Services\BibleBrains\Scripture;
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
     * Handles the index request.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param Scripture $scripture The Scripture service object.
     *
     * @return Response The HTTP response.
     */
    public function index( Request $request, Response $response, Scripture $scripture, Language $language )
    {
        try {
            $errors = validate($request->all(), [
                'reference' => 'required|string'
            ]);

            if ( $errors ) {
                return $response->setStatusCode( 400 )->setContent( $errors );
            }

            $result = $scripture->by_reference( $request->get( 'reference' ) );

            return $response->setContent( $result );
        } catch ( \Exception $e ) {
            return $response->setStatusCode( 500 )->setContent([
                'error' => $e->getMessage()
            ]);
        }
    }
}
