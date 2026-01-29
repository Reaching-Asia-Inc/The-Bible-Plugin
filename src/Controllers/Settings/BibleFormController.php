<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Requests\RequestInterface as Request;
use CodeZone\Bible\Services\BibleBrainsKeys;
use CodeZone\Bible\Services\MediaTypes;
use CodeZone\Bible\Settings\LanguagesSetting;
use Exception;
use function CodeZone\Bible\container;
use function CodeZone\Bible\transaction;
use function CodeZone\Bible\validate;
use function CodeZone\Bible\view;

/**
 * Class BibleBrainsController
 *
 * This class is responsible for handling the ApiServices settings and API authorization.
 */
class BibleFormController {
    /**
     * Show the settings page.
     *
     * @param Request $request The request object.
     * @return mixed The rendered view or validation form
     */
    public function show( Request $request ) {
        $tab = "bible";

        // Service initialization
        $keys = container()->get( BibleBrainsKeys::class );
        $media_type_service = container()->get( MediaTypes::class );
        $languages_setting = container()->get( LanguagesSetting::class );

        // Key validation
        if ( !$keys->random() ) {
            wp_redirect( admin_url( "admin.php?page=bible-plugin&tab=advanced" ) );
            exit;
        }

        // Data preparation
        $fields = [
            'bible_brains_key' => $keys->field_value(),
            'bible_brains_key_instructions' => $keys->field_instructions(),
            'bible_brains_key_readonly' => $keys->has_override(),
            'languages' => $languages_setting->all() ?: [],
        ];

        // Render view
        return view("settings/bible-brains-form", [
            'tab' => $tab,
            'fields' => $fields,
            'media_type_options' => $media_type_service->options(),
        ]);
    }

    /**
	 * Submit the request and return either success or error message.
	 *
	 * @param Request $request The request object.
	 *
	 * @return mixed Returns success with the key if random number is 1, otherwise returns error message.
	 * @throws Exception
	 */
    public function submit( Request $request ) {
        $validation = validate($request, [
            'languages' => 'required|array'
        ]);

        if ( $validation !== true ) {
            return new \WP_Error(
                'validation_error',
                __( 'Please complete the required fields.', 'bible-plugin' ),
                $validation,
            );
        }

        $languages = $request->get( 'languages', [] );

        // Normalize and merge duplicate languages (by 'value'), combining bible versions
        $merged = [];
        $languages = is_array( $languages ) ? $languages : [];
        foreach ( $languages as $lang ) {
            if ( !is_array( $lang ) ) {
                continue;
            }
            $key = strtolower( trim( (string) ( $lang['value'] ?? '' ) ) );
            if ( $key === '' ) {
                continue;
            }

            // Normalize bibles from legacy 'bible' or CSV/string to array
            $bibles = [];
            if ( isset( $lang['bibles'] ) ) {
                if ( is_string( $lang['bibles'] ) ) {
                    $bibles = array_filter( array_map( 'trim', explode( ',', $lang['bibles'] ) ) );
                } elseif ( is_array( $lang['bibles'] ) ) {
                    $bibles = $lang['bibles'];
                }
            } elseif ( !empty( $lang['bible'] ) ) {
                $bibles = [ (string) $lang['bible'] ];
            }

            $bibles = array_values(array_unique(array_filter($bibles, static function ( $v ) {
                return is_string( $v ) && $v !== '';
            })));

            // Normalize media_types as CSV union
            $media_csv = (string) ( $lang['media_types'] ?? '' );
            $media_parts = array_values(array_unique(array_filter(array_map( 'trim', explode( ',', $media_csv ) ), static function ( $v ) {
                return $v !== '';
            })));

            $is_default = !empty( $lang['is_default'] );
            $default_bible = isset( $lang['default_bible'] ) && is_string( $lang['default_bible'] ) ? $lang['default_bible'] : '';

            if ( !isset( $merged[$key] ) ) {
                $merged[$key] = [
                    'value' => $lang['value'] ?? null,
                    'itemText' => $lang['itemText'] ?? null,
                    'bibles' => $bibles,
                    'media_types' => implode( ',', $media_parts ),
                    'is_default' => $is_default ? 1 : 0,
                    'default_bible' => $default_bible,
                    // keep any identifiers if present; hydrate will fill if missing
                    'id' => $lang['id'] ?? null,
                    'code' => $lang['code'] ?? null,
                    'source' => $lang['source'] ?? null,
                ];
            } else {
                // Merge into existing
                $existing = $merged[$key];

                // Merge bibles
                $merged_bibles = array_values( array_unique( array_merge( $existing['bibles'], $bibles ) ) );

                // Merge media types (CSV union)
                $existing_media = array_filter( array_map( 'trim', explode( ',', (string) $existing['media_types'] ) ) );
                $merged_media = array_values( array_unique( array_merge( $existing_media, $media_parts ) ) );

                // Merge defaults
                $merged_default = $existing['is_default'] || $is_default ? 1 : 0;
                $merged_default_bible = $existing['default_bible'] ?: $default_bible;

                $merged[$key] = array_merge($existing, [
                    'bibles' => $merged_bibles,
                    'media_types' => implode( ',', $merged_media ),
                    'is_default' => $merged_default,
                    'default_bible' => $merged_default_bible,
                    'itemText' => $existing['itemText'] ?: ( $lang['itemText'] ?? null ),
                    'id' => $existing['id'] ?: ( $lang['id'] ?? null ),
                    'code' => $existing['code'] ?: ( $lang['code'] ?? null ),
                    'source' => $existing['source'] ?: ( $lang['source'] ?? null ),
                ]);
            }
        }

        // Finalize default_bible validity and fallbacks
        foreach ( $merged as &$lang ) {
            // Ensure default_bible is within bibles if set
            if ( !empty( $lang['default_bible'] ) && is_string( $lang['default_bible'] ) ) {
                if ( !in_array( $lang['default_bible'], $lang['bibles'], true ) && !empty( $lang['bibles'] ) ) {
                    // If not present, add to list at front to keep it consistent
                    array_unshift( $lang['bibles'], $lang['default_bible'] );
                    $lang['bibles'] = array_values( array_unique( $lang['bibles'] ) );
                }
            }
            // If language is default but default_bible missing, derive from first bible if available
            if ( !empty( $lang['is_default'] ) && empty( $lang['default_bible'] ) && !empty( $lang['bibles'] ) ) {
                $lang['default_bible'] = $lang['bibles'][0];
            }
            // Persist bibles as array for storage; formatter will keep it normalized
            if ( !is_array( $lang['bibles'] ) ) {
                $lang['bibles'] = [];
            }
        }
        unset( $lang );

        $languages_to_save = array_values( $merged );
        $languages_setting = container()->get( LanguagesSetting::class );

        $result = transaction( function () use ( $languages_to_save, $languages_setting ) {
            return $languages_setting->save( $languages_to_save );
        } );

        if ( $result !== true ) {
            return new \WP_Error(
                'submission_error',
                __( 'Form could not be submitted.', 'bible-plugin' )
            );
        }

        return [
            'success' => true
        ];
    }
}
