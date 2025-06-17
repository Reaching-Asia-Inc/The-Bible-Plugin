<?php

namespace CodeZone\Bible\Controllers\Admin;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\RequestInterface as Request;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\BibleBrains\BibleBrainsKeys;
use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use Exception;
use function CodeZone\Bible\container;
use function CodeZone\Bible\transaction;
use function CodeZone\Bible\validate;
use function CodeZone\Bible\view;
use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\set_plugin_option;

/**
 * Class BibleBrainsController
 *
 * This class is responsible for handling the BibleBrains settings and API authorization.
 */
class BibleBrainsFormController {
    /**
     * Show the settings page.
     *
     * @param Request $request The request object.
     * @return mixed The rendered view or validation form
     */
    public function show(Request $request) {
        $tab = "bible";

        // Service initialization
        $keys = container()->get(BibleBrainsKeys::class);
        $bibleService = container()->get(Bibles::class);
        $mediaTypeService = container()->get(MediaTypes::class);

        // Key validation
        if (!$keys->random()) {
            return $this->validation_form($request);
        }

        // Bible service validation
        try {
            $bibleService->find('ENGESV');
        } catch (BibleBrainsException $e) {
            return $this->validation_form($request, $e->getMessage());
        }

        // Data preparation
        $fields = [
            'bible_brains_key' => $keys->field_value(),
            'bible_brains_key_instructions' => $keys->field_instructions(),
            'bible_brains_key_readonly' => $keys->has_override(),
            'languages' => is_array(get_plugin_option('languages')) ? get_plugin_option('languages') : [],
        ];

        // Render view
        return view("settings/bible-brains-form", [
            'tab' => $tab,
            'fields' => $fields,
            'media_type_options' => $mediaTypeService->options(),
        ]);
    }

    /**
     * Display the validation form.
     *
     * @param Request $request The request object
     * @param string|null $error Optional error message
     * @return string The rendered view
     */
    private function validation_form(Request $request, string $error = null) {
        $tab = "bible";
        $keys = container()->get(BibleBrainsKeys::class);

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
            'nonce' => wp_create_nonce('bible-plugin'),
            'error' => $error ?? "",
        ]);
    }


    /**
	 * Submit the request and return either success or error message.
	 *
	 * @param Request $request The request object.
	 * @param Response $response The response object.
	 *
	 * @return mixed Returns success with the key if random number is 1, otherwise returns error message.
	 * @throws Exception
	 */
    public function submit(Request $request) {
        $validation = validate($request, [
            'languages' => 'required|array'
        ]);

        if ($validation !== true) {
            return new \WP_Error(
                'validation_error',
                __('Please complete the required fields.', 'bible-plugin'),
                $validation,
            );
        }

        $languages = $request->get('languages', []);
        $result = transaction(function () use ($languages) {
            set_plugin_option('languages', $languages);
        });

        if ($result !== true) {
            return new \WP_Error(
                'submission_error',
                __('Form could not be submitted.', 'bible-plugin')
            );
        }

        return [
            'success' => true
        ];
    }



    /**
     * Authorize the API key
     *
     * @param Request $request The request object
     * @return array|\WP_Error Response data
     */
    public function validate(Request $request) {
        $validation = validate($request, [
            'bible_brains_key' => 'required'
        ]);


        if ($validation !== true) {
            return new \WP_Error(
                'validation_error',
                __('Please enter a key.', 'bible-plugin'),
                $validation
            );
        }

        $bible_brains_key = $request->get('bible_brains_key');
        $bibles = container()->get(Bibles::class);
        $keys = container()->get(BibleBrainsKeys::class);
        $key = $keys->has_override() ? $keys->get_override()[0] : $bible_brains_key;

        try {
            $bibles->find('ENGESV', ['key' => $key, 'cache' => false]);
        } catch (BibleBrainsException $e) {
            return new \WP_Error(
                'validation_error',
                __('Failed to validate key.', 'bible-plugin')
            );
        }

        if (!$keys->has_override()) {
            $result = transaction(function () use ($bible_brains_key) {
                set_plugin_option('bible_brains_key', $bible_brains_key);
            });

            if ($result !== true) {
                return new \WP_Error(
                    500,
                    __('Form could not be submitted.', 'bible-plugin')
                );
            }
        }

        return [
            'success' => true
        ];
    }

}
