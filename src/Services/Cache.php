<?php

namespace CodeZone\Bible\Services;

/**
 * Class Cache
 *
 * A class for caching and retrieving data using WordPress transients.
 */
class Cache {

	/**
	 * Determines the scope key for a given key.
	 *
	 * @param string $key The key for which to determine the scope key.
	 *
	 * @return string The scope key for the given key.
	 */
	public function scope_key( string $key ): string {
		return "bible_plugin_{$key}";
	}

	/**
	 * Retrieves the value associated with the given key.
	 *
	 * @param string $key The key of the value to retrieve.
	 *
	 * @return mixed The value associated with the given key, or false if the key
	 *               does not exist or the value has expired.
	 */
	public function get( string $key ) {
		return get_transient( $this->scope_key( $key ) );
	}

	/**
	 * Sets the value associated with the given key.
	 *
	 * @param string $key The key of the value to set.
	 * @param mixed $value The value to set for the given key.
	 * @param int $expiration The expiration time for the value in seconds. Default value is one hour (60 * 60).
	 *
	 * @return bool True if the value was successfully set, false otherwise.
	 */
	public function set( string $key, $value, int $expiration = 60 * 60 ) {
		return set_transient( $this->scope_key( $key ), $value, $expiration );
	}

	/**
	 * Deletes the value associated with the given key.
	 *
	 * @param string $key The key of the value to delete.
	 *
	 * @return bool True if the key is successfully deleted, false otherwise.
	 */
	public function delete( string $key ) {
		return delete_transient( $this->scope_key( $key ) );
	}

	/**
	 * Clears all transient options related to the Bible Plugin.
	 *
	 * This method removes all options from the WordPress options table that are associated with
	 * the Bible Plugin and have names starting with '_transient_bible_plugin_'.
	 *
	 * @return void
	 */
    public function flush() {
        global $wpdb;

        $table = isset( $wpdb->options ) ? $wpdb->options : $wpdb->prefix . 'options';
        $table = esc_sql( $table );

        $wpdb->query(
            "DELETE FROM `$table` WHERE " . $wpdb->prepare( //phpcs:ignore
                "option_name LIKE %s OR option_name LIKE %s",
                '_transient_bible_plugin_%',
                '_transient_timeout_bible_plugin_%'
            )
        );
    }
}
