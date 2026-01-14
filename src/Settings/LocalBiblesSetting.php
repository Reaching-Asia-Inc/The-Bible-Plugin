<?php

namespace CodeZone\Bible\Settings;

use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\set_plugin_option;

class LocalBiblesSetting
{
    const OPTION_KEY = 'local_bibles';

    /**
     * Retrieve the custom Bibles option.
     *
     * @return array|null The custom Bibles data as an array, or null if not available.
     */
    public function all(): ?array {
        return get_plugin_option( self::OPTION_KEY, [] );
    }

    /**
     * Add a new Paratext Bible.
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
     * Update an existing Paratext Bible.
     *
     * @param string $key The Bible key.
     * @param array $data The updated Bible data.
     * @return bool True on success, false on failure.
     */
    public function update( string $key, array $data ): bool
    {
        $bibles = $this->all();
        $key = $this->find_key( $key );

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
     * Delete a Paratext Bible.
     *
     * @param string $id The Bible key.
     * @return bool True on success, false on failure.
     */
    public function delete( string $id ): bool
    {
        $bibles = $this->all();
        $key = $this->find_key( $id );

        // Check if the Bible exists
        if ( !isset( $bibles[$key] ) ) {
            return false;
        }

        // Remove the Bible
        unset( $bibles[$key] );

        // Save the updated list
        return $this->save( $bibles );
    }

    public function find( string $id ): ?array
    {
        return array_find( $this->all(), fn( $item ) => $item['id'] === $id );
    }

    public function find_key( string $id ): ?string
    {
        return array_find_key( $this->all(), fn( $item ) => $item['id'] === $id );
    }

    /**
     * Set the option data
     */
    public function save( array $data ) {
        return set_plugin_option( self::OPTION_KEY, $data );
    }
}
