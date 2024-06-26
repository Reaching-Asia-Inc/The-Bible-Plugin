<?php

namespace CodeZone\Bible\Services;

use CodeZone\Bible\Illuminate\Support\Arr;
use CodeZone\Bible\Illuminate\Support\Str;
use function CodeZone\Bible\set_option;
use function CodeZone\Bible\set_plugin_option;

/**
 * Class Options
 *
 * This class provides methods for retrieving options from the database.
 * ApiKeys are scoped to the plugin to avoid conflicts with other plugins.
 * Default values may be provided for each option to avoid duplication.
 */
class Options {
	/**
	 * Get the default values for the method.
	 *
	 * This method returns an associative array with the default values for various keys.
	 *
	 * @return array An associative array with the default values.
	 */
	private function defaults(): array {
		return [
			'bible_brains_key' => '',
			'languages'        => [
				[
					'bibles'      => 'ENGESV',
					'media_types' => 'text,audio-video',
					'itemText'    => 'English',
					'value'       => 'eng'
				]
			],
			'language'         => '6414',
			'color_scheme'     => 'light',
			'translations'     => [],
			'colors'           => [
				'accent'       => '#29ac9d',
				'accent_steps' => [
					100  => 'rgb(10, 41, 38)',
					200  => 'rgb(14, 60, 55)',
					300  => 'rgb(19, 79, 72)',
					400  => 'rgb(23, 97, 89)',
					500  => 'rgb(28, 116, 106)',
					600  => 'rgb(32, 135, 123)',
					700  => 'rgb(37, 153, 140)',
					800  => 'rgb(41, 172, 157)',
					900  => 'rgb(49, 204, 187)',
					1000 => 'rgb(80, 213, 198)',
					1100 => 'rgb(113, 221, 209)',
					1200 => 'rgb(145, 229, 219)',
					1300 => 'rgb(178, 237, 230)',
					1400 => 'rgb(210, 244, 240)',
					1500 => 'rgb(243, 252, 251)'
				]
			]
		];
	}

	public function get_default( string $key ) {
		return Arr::get( $this->defaults(), $key );
	}

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
	 * Retrieves the value of the specified option.
	 *
	 * @param string $key The key of the option to retrieve.
	 * @param mixed|null $default The default value to return if the option is not found. Default is null.
	 *
	 * @return mixed The value of the option if found, otherwise returns the default value.
	 */
	public function get( string $key, $default = null, $required = false ) {
		if ( $default === null ) {
			$default = $this->get_default( $key );
		}

		$key = $this->scope_key( $key );


		$result = get_option( $key, $default );


		if ( $required && ! $result ) {
			set_plugin_option( $key, $default );

			return $default;
		}

		return $result;
	}

	/**
	 * Sets the value of the specified option.
	 *
	 * @param string $key The key of the option to set.
	 * @param mixed $value The value to set for the option.
	 *
	 * @return bool Returns true if the option was set successfully, otherwise returns false.
	 */
	public function set( string $key, $value ): bool {
		$key = $this->scope_key( $key );

		return set_option( $key, $value );
	}

    /**
     * Delete the value of the specified option.
     */
    public function delete( string $key ): bool
    {
        $key = $this->scope_key($key);

        return delete_option($key);
    }
}
