<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Requests\RequestInterface as Request;
use CodeZone\Bible\Services\BibleBrainsKeys;
use CodeZone\Bible\Settings\BibleBrainsKeySetting;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsBibles;
use function CodeZone\Bible\container;
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
            'bible_brains_key' => 'string',
        ]);


        if ( $validation !== true ) {
            return new \WP_Error(
                'validation_error',
                __( 'Please check your input.', 'bible-plugin' ),
                $validation
            );
        }

        $bible_brains_key = $request->get( 'bible_brains_key' );

        $bibles = container()->get( BibleBrainsBibles::class );
        $keys = container()->get( BibleBrainsKeys::class );

        // If a key is provided, validate it.
        if ( !empty( $bible_brains_key ) ) {
            $key = $keys->has_override() ? $keys->get_override()[0] : $bible_brains_key;

            try {
                $bibles->find( 'ENGESV', [ 'key' => $key, 'cache' => false ] );
            } catch ( BiblePluginException $e ) {
                return new \WP_Error(
                    'validation_error',
                    __( 'Failed to validate key.', 'bible-plugin' )
                );
            }
        }

        $result = transaction(function () use ( $bible_brains_key, $keys ) {
            if ( !empty( $bible_brains_key ) && !$keys->has_override() ) {
                $bible_brains_key_setting = container()->get( BibleBrainsKeySetting::class );
                $bible_brains_key_setting->save( $bible_brains_key );
            }
        });

        if ( $result !== true ) {
            return new \WP_Error(
                500,
                __( 'Form could not be submitted.', 'bible-plugin' )
            );
        }

        return [
            'success' => true
        ];
    }

    /**
     * Remove the stored Bible Brains API key (deletes the option).
     *
     * @param Request $request
     * @return array|\WP_Error
     */
    public function delete( Request $request ) {
        $keys = container()->get( BibleBrainsKeys::class );

        // If key is provided via override, treat as read-only and block deletion
        if ( $keys->has_override() ) {
            return new \WP_Error(
                'read_only',
                __( 'Key is managed by an override and cannot be removed here.', 'bible-plugin' )
            );
        }

        $result = transaction(function () {
            $setting = container()->get( BibleBrainsKeySetting::class );
            return $setting->delete();
        });

        if ( $result !== true ) {
            return new \WP_Error(
                500,
                __( 'Unable to remove key.', 'bible-plugin' )
            );
        }

        return [ 'success' => true ];
    }
}
