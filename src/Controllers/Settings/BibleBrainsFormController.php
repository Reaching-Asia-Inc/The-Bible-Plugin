<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\RequestInterface as Request;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\BibleBrains\BibleBrainsKeys;
use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use Exception;
use function CodeZone\Bible\admin_path;
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
        $mediaTypeService = container()->get(MediaTypes::class);

        // Key validation
        if (!$keys->random()) {
            wp_redirect(admin_path("admin.php?page=bible-plugin&tab=advanced"));
            exit;
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
}
