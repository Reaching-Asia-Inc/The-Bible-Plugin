<?php
/**
 * BibleBrainsKeySetting Class
 */

namespace CodeZone\Bible\Settings;

use function CodeZone\Bible\set_plugin_option;

/**
 * Class BibleBrainsKeySetting
 *
 * Manages the Bible Brains API key setting for the plugin.
 */
class BibleBrainsKeySetting
{
    const OPTION_KEY = 'bible_brains_key';

    /**
     * Save the Bible Brains API key option.
     *
     * @param string $value The API key to save.
     * @return bool True on success, false on failure.
     */
    public function save( string $value ): bool {
        return set_plugin_option( self::OPTION_KEY, $value );
    }
}
