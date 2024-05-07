<?php

namespace CodeZone\Bible\ShortCodes;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\Assets;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\BibleBrains\FileSets;
use CodeZone\Bible\Services\BibleBrains\Scripture as ScriptureService;
use function CodeZone\Bible\container;
use function CodeZone\Bible\view;
use function CodeZone\Bible\cast_bool_values;

/**
 * Class Bible
 *
 * This class represents a Bible shortcode handler.
 */
class Bible {
	/**
	 * Constructs a new instance of the class.
	 *
	 * Adds a shortcode callback to handle the 'tbp-bible' shortcode.
	 *
	 * @return void
	 */
	public function __construct( private Assets $assets ) {
		add_shortcode( 'tbp-bible', [ $this, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueues the scripts and styles for the shortcode.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! has_shortcode( get_the_content(), 'tbp-bible' ) ) {
			return;
		}

		$this->assets->wp_enqueue_scripts();
	}

	/**
	 * Renders the Bible shortcode.
	 *
	 * @param array $attributes The attributes for the Bible shortcode.
	 *
	 * @return string The rendered Bible shortcode view.
	 */
	public function render( $attributes ) {
		if ( ! $attributes ) {
			$attributes = [];
		}

		$attributes = shortcode_atts( [], cast_bool_values( $attributes ?? [] ) );

		return view( 'shortcodes/bible', [
			'attributes' => $attributes
		] );
	}
}
