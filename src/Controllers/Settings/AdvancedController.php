<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Requests\RequestInterface as Request;
use CodeZone\Bible\Services\BibleBrainsKeys;
use CodeZone\Bible\Settings\BibleBrainsKeySetting;
use CodeZone\Bible\Settings\GoogleTagSetting;
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
        $google_tag_setting = container()->get( GoogleTagSetting::class );

        // Data preparation
        $fields = [
            'bible_brains_key' => $keys->field_value(),
            'bible_brains_key_instructions' => $keys->field_instructions(),
            'bible_brains_key_readonly' => $keys->has_override(),
            'google_tag_id' => $google_tag_setting->get(),
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
            'google_tag_id' => 'nullable|string'
        ]);


        if ( $validation !== true ) {
            return new \WP_Error(
                'validation_error',
                __( 'Please check your input.', 'bible-plugin' ),
                $validation
            );
        }

        $bible_brains_key = $request->get( 'bible_brains_key' );
        $google_tag_id = $request->get( 'google_tag_id' );

        $bibles = container()->get( BibleBrainsBibles::class );
        $keys = container()->get( BibleBrainsKeys::class );
        $google_tag_setting = container()->get( GoogleTagSetting::class );

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

        $result = transaction(function () use ( $bible_brains_key, $google_tag_id, $keys, $google_tag_setting ) {
            if ( !empty( $bible_brains_key ) && !$keys->has_override() ) {
                $bible_brains_key_setting = container()->get( BibleBrainsKeySetting::class );
                $bible_brains_key_setting->save( $bible_brains_key );
            }
            $google_tag_setting->save( (string) $google_tag_id );
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
}
