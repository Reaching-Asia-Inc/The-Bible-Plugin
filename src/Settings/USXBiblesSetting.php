<?php

namespace CodeZone\Bible\Settings;

use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\set_plugin_option;

class USXBiblesSetting
{
    const OPTION_KEY = 'usx_bibles';

    /**
     * Retrieve the custom Bibles option.
     *
     * @return array|null The custom Bibles data as an array, or null if not available.
     */
    public function all(): ?array {
        return get_plugin_option( self::OPTION_KEY, [] );
    }

    /**
     * Add a new USX Bible.
     *
     * @param string $key The Bible key.
     * @param array $data The Bible data.
     * @return bool True on success, false on failure.
     */
    public function add( $key, array $data ): bool
    {
        $bibles = $this->all();

        // Check if the Bible already exists
        if ( isset( $bibles[$key] ) ) {
            return false;
        }

        // Add the Bible
        $bibles[$key] = $data;

        // Save the updated list
        return $this->save( $bibles );
    }

    /**
     * Update an existing USX Bible.
     *
     * @param string $key The Bible key.
     * @param array $data The updated Bible data.
     * @return bool True on success, false on failure.
     */
    public function update( string $key, array $data ): bool
    {
        $bibles = $this->all();

        // Check if the Bible exists
        if ( !isset( $bibles[$key] ) ) {
            return false;
        }

        // Update the Bible
        $bibles[$key] = array_merge( $bibles[$key], $data );

        // Save the updated list
        return $this->save( $bibles );
    }

    /**
     * Delete a USX Bible.
     *
     * @param string $key The Bible key.
     * @return bool True on success, false on failure.
     */
    public function delete( string $key ): bool
    {
        $bibles = $this->all();

        // Check if the Bible exists
        if ( !isset( $bibles[$key] ) ) {
            return false;
        }

        // Remove the Bible
        unset( $bibles[$key] );

        // Save the updated list
        return $this->save( $bibles );
    }

    /**
     * Set the option data
     */
    public function save( array $data ) {
        return set_plugin_option( self::OPTION_KEY, $data );
    }
}
