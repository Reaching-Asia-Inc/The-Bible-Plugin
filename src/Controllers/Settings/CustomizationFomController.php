<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Requests\RequestInterface as Request;
use CodeZone\Bible\Services\Translations;
use CodeZone\Bible\Settings\ColorSchemeSetting;
use CodeZone\Bible\Settings\ColorsSetting;
use CodeZone\Bible\Settings\TranslationsSetting;
use function CodeZone\Bible\config;
use function CodeZone\Bible\container;
use function CodeZone\Bible\transaction;
use function CodeZone\Bible\validate;
use function CodeZone\Bible\view;


/**
 * Controller handling customization form operations.
 * Manages display and submission of customization settings.
 */
class CustomizationFomController {
    /**
     * Display the customization settings page.
     *
     * @param Request $request The HTTP request object.
     * @return string The view containing the customization settings page.
     */
    public function show( Request $request ): string {
        $translations_service = container()->get( Translations::class );
        $color_scheme_setting = container()->get( ColorSchemeSetting::class );
        $colors_setting = container()->get( ColorsSetting::class );
        $translations_setting = container()->get( TranslationsSetting::class );

        $tab = "customization";
        $nonce = wp_create_nonce( 'bible-brains' );
        $color_scheme_options = [
            [
                'itemText' => __( 'Light', 'bible-plugin' ),
                'value'    => 'light',
            ],
            [
                'itemText' => __( 'Dark', 'bible-plugin' ),
                'value'    => 'dark',
            ]
        ];

        $translation_options = $translations_service->options();
        $fields = [
            'color_scheme' => $color_scheme_setting->get(),
            'colors'       => $colors_setting->get(),
            'translations' => $translations_setting->get()
        ];

        return view("settings/customization-form",
            compact( 'tab', 'nonce', 'color_scheme_options', 'fields' )
        );
    }

    /**
     * Submit the general settings admin tab form
     *
     * @param Request $request The request object
     * @return array Response data
     */
    public function submit( Request $request ): array {
        // Validate required fields
        $errors = validate($request, [
            'color_scheme' => 'required|string',
            'colors'       => 'required|array',
            'translations' => 'required|array',
        ]);

        if ( $errors !== true ) {
            return [
                'code' => 400,
                'errors' => $errors,
                'message' => __( 'Please complete the required fields.', 'bible-plugin' ),
            ];
        }

        $color_scheme_setting = container()->get( ColorSchemeSetting::class );
        $colors_setting = container()->get( ColorsSetting::class );
        $translations_setting = container()->get( TranslationsSetting::class );

        $result = transaction(function () use ( $request, $color_scheme_setting, $colors_setting, $translations_setting ) {
            $color_scheme_setting->save( $request->color_scheme );
            $colors_setting->save( $request->colors );
            $translations_setting->save( $request->translations );
        });

        if ( $result !== true ) {
            return [
                'code' => 500,
                'errors' => [
                    'general' => __( 'Form could not be submitted.', 'bible-plugin' ),
                ],
                'message' => __( 'Form could not be submitted.', 'bible-plugin' ),
            ];
        }

        return [
            'success' => true,
        ];
    }
}
