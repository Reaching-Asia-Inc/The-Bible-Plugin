<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Requests\RequestInterface as Request;
use CodeZone\Bible\Services\BibleBrainsKeys;
use CodeZone\Bible\Services\MediaTypes;
use Exception;
use function CodeZone\Bible\admin_path;
use function CodeZone\Bible\container;
use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\set_plugin_option;
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

        // Key validation
        if ( !$keys->random() ) {
            wp_redirect( admin_path( "admin.php?page=bible-plugin&tab=advanced" ) );
            exit;
        }

        // Data preparation
        $fields = [
            'bible_brains_key' => $keys->field_value(),
            'bible_brains_key_instructions' => $keys->field_instructions(),
            'bible_brains_key_readonly' => $keys->has_override(),
            'languages' => is_array( get_plugin_option( 'languages' ) ) ? get_plugin_option( 'languages' ) : [],
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
            $mediaCsv = (string) ( $lang['media_types'] ?? '' );
            $mediaParts = array_values(array_unique(array_filter(array_map( 'trim', explode( ',', $mediaCsv ) ), static function ( $v ) {
                return $v !== '';
            })));

            $isDefault = !empty( $lang['is_default'] );
            $defaultBible = isset( $lang['default_bible'] ) && is_string( $lang['default_bible'] ) ? $lang['default_bible'] : '';

            if ( !isset( $merged[$key] ) ) {
                $merged[$key] = [
                    'value' => $lang['value'] ?? null,
                    'itemText' => $lang['itemText'] ?? null,
                    'bibles' => $bibles,
                    'media_types' => implode( ',', $mediaParts ),
                    'is_default' => $isDefault ? 1 : 0,
                    'default_bible' => $defaultBible,
                    // keep any identifiers if present; hydrate will fill if missing
                    'id' => $lang['id'] ?? null,
                    'code' => $lang['code'] ?? null,
                    'source' => $lang['source'] ?? null,
                ];
            } else {
                // Merge into existing
                $existing = $merged[$key];

                // Merge bibles
                $mergedBibles = array_values( array_unique( array_merge( $existing['bibles'], $bibles ) ) );

                // Merge media types (CSV union)
                $existingMedia = array_filter( array_map( 'trim', explode( ',', (string) $existing['media_types'] ) ) );
                $mergedMedia = array_values( array_unique( array_merge( $existingMedia, $mediaParts ) ) );

                // Merge defaults
                $mergedDefault = $existing['is_default'] || $isDefault ? 1 : 0;
                $mergedDefaultBible = $existing['default_bible'] ?: $defaultBible;

                $merged[$key] = array_merge($existing, [
                    'bibles' => $mergedBibles,
                    'media_types' => implode( ',', $mergedMedia ),
                    'is_default' => $mergedDefault,
                    'default_bible' => $mergedDefaultBible,
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

        $languagesToSave = array_values( $merged );

        $result = transaction(function () use ( $languagesToSave ) {
            return set_plugin_option( 'languages', $languagesToSave );
        });

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
