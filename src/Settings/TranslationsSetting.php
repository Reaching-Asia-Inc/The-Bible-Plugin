<?php
/**
 * TranslationsSetting Class
 */

namespace CodeZone\Bible\Settings;

use CodeZone\Bible\Services\Translations;
use function CodeZone\Bible\container;
use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\set_plugin_option;

/**
 * Class TranslationsSetting
 *
 * Manages the translations setting for the plugin.
 */
class TranslationsSetting
{
    const OPTION_KEY = 'translations';

    /**
     * Retrieve the translations option.
     *
     * @return array The translations data.
     */
    public function get(): array {
        $translations = get_plugin_option( self::OPTION_KEY, [], true );
        return $this->format( $translations );
    }

    /**
     * Save the translations option.
     *
     * @param array $value The translations data to save.
     * @return bool True on success, false on failure.
     */
    public function save( array $value ): bool {
        return set_plugin_option( self::OPTION_KEY, $value );
    }

    /**
     * Formats the translations by ensuring all valid keys are present and invalid ones are removed.
     *
     * @param array $translations The input translations array.
     * @return array The formatted translations array.
     */
    public function format( array $translations ): array {
        $translations_service = container()->get( Translations::class );
        $translation_options = $translations_service->options();

        // Make sure all translation keys are present
        foreach ( $translation_options as $option ) {
            if ( !array_key_exists( $option['value'], $translations ) ) {
                $translations[$option['value']] = "";
            }
        }

        // Get all valid translation values
        $valid_translations = array_map(function ( $option ) {
            return $option['value'];
        }, $translation_options);

        // Remove any keys that are not present in the translation options
        return array_intersect_key( $translations, array_flip( $valid_translations ) );
    }
}
