<?php
/**
 * ColorsSetting Class
 */

namespace CodeZone\Bible\Settings;

use function CodeZone\Bible\config;
use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\set_plugin_option;

/**
 * Class ColorsSetting
 *
 * Manages the colors setting for the plugin.
 */
class ColorsSetting
{
    const OPTION_KEY = 'colors';

    /**
     * Retrieve the colors option.
     *
     * @return array|null The colors data, or null if not set.
     */
    public function get(): ?array {
        return $this->format( get_plugin_option( self::OPTION_KEY, null, true ) );
    }

    /**
     * Save the colors option.
     *
     * @param array $value The colors data to save.
     * @return bool True on success, false on failure.
     */
    public function save( array $value ): bool {
        return set_plugin_option( self::OPTION_KEY, $this->format( $value ) );
    }

    /**
     * Formats and ensures the structure of the colors array.
     *
     * @param mixed $colors The input array containing color configurations.
     * @return array The formatted colors array with all required keys and defaults applied.
     */
    public function format( $colors ): array {
        if ( !is_array( $colors ) ) {
            $colors = [];
        }

        if ( isset( $colors[0] ) && empty( $colors['accent'] ) ) {
            $colors['accent'] = $colors[0];
        }

        if ( isset( $colors[1] ) && empty( $colors['accent_steps'] ) ) {
            $colors['accent_steps'] = $colors[1];
        }

        if ( empty( $colors['accent_steps'] ) ) {
            $colors['accent_steps'] = config( 'options.defaults.colors.accent_steps' );
        }

        if ( empty( $colors['accent'] ) ) {
            $colors['accent'] = config( 'options.defaults.colors.accent' );
        }

        return [
            'accent'         => $colors['accent'],
            'accent_steps'   => $colors['accent_steps'],
        ];
    }
}
