<?php
/**
 * ColorSchemeSetting Class
 */

namespace CodeZone\Bible\Settings;

use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\set_plugin_option;

/**
 * Class ColorSchemeSetting
 *
 * Manages the color scheme setting for the plugin.
 */
class ColorSchemeSetting
{
    const OPTION_KEY = 'color_scheme';

    /**
     * Retrieve the color scheme option.
     *
     * @return string|null The color scheme value, or null if not set.
     */
    public function get(): ?string {
        return get_plugin_option( self::OPTION_KEY, null, true );
    }

    /**
     * Save the color scheme option.
     *
     * @param string $value The color scheme value to save.
     * @return bool True on success, false on failure.
     */
    public function save( string $value ): bool {
        return set_plugin_option( self::OPTION_KEY, $value );
    }
}
