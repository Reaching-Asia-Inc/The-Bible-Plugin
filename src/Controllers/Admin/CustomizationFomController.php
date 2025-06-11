<?php

namespace CodeZone\Bible\Controllers\Admin;

use CodeZone\Bible\Services\RequestInterface as Request;
use CodeZone\Bible\Services\Translations;
use function CodeZone\Bible\container;
use function CodeZone\Bible\transaction;
use function CodeZone\Bible\validate;
use function CodeZone\Bible\view;
use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\set_plugin_option;


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
    public function show(Request $request): string {
        $translations_service = container()->get(Translations::class);

        $tab = "customization";
        $nonce = wp_create_nonce('bible-brains');
        $color_scheme_options = [
            [
                'itemText' => __('Light', 'bible-plugin'),
                'value'    => 'light',
            ],
            [
                'itemText' => __('Dark', 'bible-plugin'),
                'value'    => 'dark',
            ]
        ];

        $translation_options = $translations_service->options();
        $translations = get_plugin_option('translations', [], true);

        //Make sure all translation keys are present and remove any keys that are not present in the translation options
        foreach ($translation_options as $option) {
            if (!array_key_exists($option['value'], $translations)) {
                $translations[$option['value']] = "";
            }
        }

        // Get all valid translation values
        $valid_translations = array_map(function($option) {
            return $option['value'];
        }, $translation_options);

        $translations = array_intersect_key($translations, array_flip($valid_translations));
        $fields = [
            'color_scheme' => get_plugin_option('color_scheme', null, true),
            'colors'       => get_plugin_option('colors', null, true),
            'translations' => $translations
        ];

        return view("settings/customization-form",
            compact('tab', 'nonce', 'color_scheme_options', 'fields')
        );
    }

    /**
     * Submit the general settings admin tab form
     *
     * @param Request $request The request object
     * @return array Response data
     */
    public function submit(Request $request): array {
        $errors = [];

        // Validate required fields
        $required_fields = ['color_scheme', 'colors', 'translations'];
        foreach ($required_fields as $field) {
            if (!$request->has($field)) {
                $errors[$field] = __('This field is required.', 'bible-plugin');
            }
        }

        if (!empty($errors)) {
            wp_send_json_error([
                'error'  => __('Please complete the required fields.', 'bible-plugin'),
                'errors' => $errors,
            ], 400);
        }

        $result = transaction(function () use ($request) {
            set_plugin_option('color_scheme', $request->color_scheme);
            set_plugin_option('colors', $request->colors);
            set_plugin_option('translations', $request->translations);
        });

        if (!$result === true) {
            wp_send_json_error([
                'error' => __('Form could not be submitted.', 'bible-plugin'),
            ], 400);
        }

        return [
            'success' => true,
        ];
    }
}
