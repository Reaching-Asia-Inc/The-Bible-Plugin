<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\BibleBrains\BibleBrainsKeys;
use CodeZone\Bible\Services\RequestInterface as Request;
use function CodeZone\Bible\container;
use function CodeZone\Bible\set_plugin_option;
use function CodeZone\Bible\transaction;
use function CodeZone\Bible\validate;
use function CodeZone\Bible\view;

class AdvancedController
{
    /**
     * Display the validation form.
     *
     * @param Request $request The request object
     * @param string|null $error Optional error message
     * @return string The rendered view
     */
    public function show( Request $request, ?string $error = null ) {
        $tab = "advanced";
        $keys = container()->get( BibleBrainsKeys::class );

        // Data preparation
        $fields = [
            'bible_brains_key' => $keys->field_value(),
            'bible_brains_key_instructions' => $keys->field_instructions(),
            'bible_brains_key_readonly' => $keys->has_override(),
        ];

        // Render view
        return view("settings/bible-brains-key-form", [
            'tab' => $tab,
            'fields' => $fields,
            'nonce' => wp_create_nonce( 'bible-plugin' ),
            'error' => $error ?? "",
        ]);
    }

    /**
     * Authorize the API key
     *
     * @param Request $request The request object
     * @return array|\WP_Error Response data
     */
    public function submit( Request $request ) {
        $validation = validate($request, [
            'bible_brains_key' => 'required|string'
        ]);


        if ( $validation !== true ) {
            return new \WP_Error(
                'validation_error',
                __( 'Please enter a key.', 'bible-plugin' ),
                $validation
            );
        }

        $bible_brains_key = $request->get( 'bible_brains_key' );
        $bibles = container()->get( Bibles::class );
        $keys = container()->get( BibleBrainsKeys::class );
        $key = $keys->has_override() ? $keys->get_override()[0] : $bible_brains_key;

        try {
            $bibles->find( 'ENGESV', [ 'key' => $key, 'cache' => false ] );
        } catch ( BibleBrainsException $e ) {
            return new \WP_Error(
                'validation_error',
                __( 'Failed to validate key.', 'bible-plugin' )
            );
        }

        if ( !$keys->has_override() ) {
            $result = transaction(function () use ( $bible_brains_key ) {
                set_plugin_option( 'bible_brains_key', $bible_brains_key );
            });

            if ( $result !== true ) {
                return new \WP_Error(
                    500,
                    __( 'Form could not be submitted.', 'bible-plugin' )
                );
            }
        }

        return [
            'success' => true
        ];
    }
}
