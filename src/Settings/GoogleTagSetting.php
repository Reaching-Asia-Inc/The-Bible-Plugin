<?php

namespace CodeZone\Bible\Settings;

use function CodeZone\Bible\set_plugin_option;
use function CodeZone\Bible\get_plugin_option;

/**
 * Class GoogleTagSetting
 *
 * Manages the Google Tag ID setting for the plugin.
 */
class GoogleTagSetting
{
    const OPTION_KEY = 'google_tag_id';

    /**
     * Save the Google Tag ID option.
     *
     * @param string $value The Tag ID to save.
     * @return bool True on success, false on failure.
     */
    public function save( string $value ): bool {
        return set_plugin_option( self::OPTION_KEY, $value );
    }

    /**
     * Get the Google Tag ID option.
     *
     * @return string|null The Tag ID.
     */
    public function get(): ?string {
        return get_plugin_option( self::OPTION_KEY );
    }
}
