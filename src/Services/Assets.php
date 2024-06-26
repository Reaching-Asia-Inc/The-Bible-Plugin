<?php

namespace CodeZone\Bible\Services;

use CodeZone\Bible\Illuminate\Support\Str;
use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use function CodeZone\Bible\Kucrut\Vite\enqueue_asset;
use function CodeZone\Bible\plugin_path;
use function CodeZone\Bible\namespace_string;
use function CodeZone\Bible\route_url;
use const CodeZone\Bible\Kucrut\Vite\VITE_CLIENT_SCRIPT_HANDLE;

class Assets {
	private static $enqueued = false;
	private MediaTypes $media_types;

	public function __construct( MediaTypes $media_types ) {
		$this->media_types = $media_types;
	}

	public function translations() {
		return [
			// Reader
			"Books"                                                                        => _x( "Books", 'reader', 'bible-plugin' ),
			'Copy'                                                                         => _x( 'Copy', 'reader', 'bible-plugin' ),
			"Copied successfully."                                                         => _x( "Copied successfully.", 'reader', 'bible-plugin' ),
			'Language'                                                                     => _x( 'Language', 'reader', 'bible-plugin' ),
			"Languages"                                                                    => _x( "Languages", 'reader', 'bible-plugin' ),
			'Link'                                                                         => _x( 'Link', 'reader', 'bible-plugin' ),
			'Old Testament'                                                                => _x( 'Old Testament', 'reader', 'bible-plugin' ),
			'Media Types'                                                                  => _x( 'Media Types', 'reader', 'bible-plugin' ),
			'New Testament'                                                                => _x( 'New Testament', 'reader', 'bible-plugin' ),
			"Search"                                                                       => _x( "Search", 'reader', 'bible-plugin' ),
			'Selection'                                                                    => _x( 'Selection', 'reader', 'bible-plugin' ),
			'Translation'                                                                  => _x( 'Translation', 'reader', 'bible-plugin' ),
			"Loading"                                                                      => _x( "Loading", 'reader', 'bible-plugin' ),
			'Text'                                                                         => _x( 'Text', 'reader', 'bible-plugin' ),
			'Selected'                                                                     => _x( 'Selected', 'reader', 'bible-plugin' ),
            'Share'                                                                        => _x( 'Share', 'reader', 'bible-plugin' ),

			//Admin
			'Note that some bible versions do not support all media types.'                => __( 'Note that some bible versions do not support all media types.', 'bible-plugin' ),
			'Select the bible language you would like to make available.'                  => __( 'Select the bible language you would like to make available.', 'bible-plugin' ),
			'Select the bible version you would like to make available for this language.' => __( 'Select the bible version you would like to make available for this language.', 'bible-plugin' ),
			'Add Language'                                                                 => __( 'Add Language', 'bible-plugin' ),
			'Default Language?'                                                            => __( 'Default Language?', 'bible-plugin' ),
			'Make this the default language.'                                              => __( 'Make this the default language.', 'bible-plugin' ),
			'Bible Version'                                                                => __( 'Bible Version', 'bible-plugin' ),
		];
	}


	/**
	 * Register method to add necessary actions for enqueueing scripts and adding cloaked styles
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( self::$enqueued ) {
			return;
		}
		self::$enqueued = true;

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
			add_action( 'admin_head', [ $this, 'cloak_style' ] );
		} else {
			add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
			add_action( "wp_head", [ $this, 'cloak_style' ] );
		}
	}

	/**
	 * Reset asset queue
	 * @return void
	 */
	/**
	 * Reset asset queue
	 * @return void
	 */
	private function filter_asset_queue() {
		global $wp_scripts;
		global $wp_styles;

		$whitelist = apply_filters( namespace_string( 'allowed_scripts' ), [] );
		foreach ( $wp_scripts->registered as $script ) {
			if ( in_array( $script->handle, $whitelist ) ) {
				continue;
			}
			wp_dequeue_script( $script->handle );
		}

		$whitelist = apply_filters( namespace_string( 'allowed_styles' ), [] );
		foreach ( $wp_styles->registered as $style ) {
			if ( in_array( $script->handle, $whitelist ) ) {
				continue;
			}
			wp_dequeue_style( $style->handle );
		}
	}


	/**
	 * Enqueues scripts and styles for the frontend.
	 *
	 * This method enqueues the specified asset(s) for the frontend. It uses the "enqueue_asset" function to enqueue
	 * the asset(s) located in the provided plugin directory path with the given filename. The asset(s) can be JavaScript
	 * or CSS files. Optional parameters can be specified to customize the enqueue behavior.
	 *
	 * @return void
	 */
	public function wp_enqueue_scripts() {
        if( wp_script_is('bible-plugin', 'enqueued')) {
            return;
        }

		enqueue_asset(
			plugin_path( '/dist' ),
			'resources/js/plugin.js',
			[
				'handle'    => 'bible-plugin',
				'css-media' => 'all', // Optional.
				'css-only'  => false, // Optional.
				'in-footer' => true, // Optional.
                'strategy'  => 'async'
			]
		);

		$this->whitelist_vite();
		$this->filter_asset_queue();

		wp_localize_script( 'bible-plugin', '$tbp', [
			'apiUrl'       => route_url( 'api' ),
			'nonce'        => wp_create_nonce( 'bible_plugin_nonce' ),
			'translations' => $this->translations(),
			"mediaTypes"   => $this->media_types->all(),
		] );

		wp_enqueue_style( 'plyr', 'https://cdn.plyr.io/3.6.8/plyr.css' );
	}

	/**
	 * Enqueues the necessary assets for the admin area.
	 *
	 * This method is responsible for enqueuing the necessary JavaScript and CSS
	 * assets for the admin area. It should be called during the 'admin_enqueue_scripts'
	 * action hook.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		enqueue_asset(
			plugin_path( '/dist' ),
			'resources/js/admin.js',
			[
				'handle'    => 'bible-plugin-admin',
				'css-media' => 'all', // Optional.
				'css-only'  => false, // Optional. Set to true to only load style assets in production mode.
				'in-footer' => false, // Optional. Defaults to false.
			]
		);

		wp_localize_script( 'bible-plugin-admin', '$tbp', [
			'apiUrl'       => route_url( 'api' ),
			'nonce'        => wp_create_nonce( 'bible_plugin_nonce' ),
			'translations' => $this->translations(),
		] );
	}

	/**
	 * Outputs the CSS style for cloaking elements.
	 *
	 * This method outputs the necessary CSS style declaration for cloaking elements
	 * in the HTML markup. The style declaration hides the elements by setting the
	 * "display" property to "none". This method should be called within the HTML
	 * document where cloaking is required.
	 *
	 * @return void
	 */
	public function cloak_style(): void {
		?>
        <style>
            .tbp-cloak {
                visibility: hidden;
            }
        </style>
		<?php
	}

	/**
	 * Determines if the given asset handle is allowed.
	 *
	 * This method checks if the provided asset handle is contained in the list of allowed handles.
	 * Allows the Template script file and the Vite client script file for dev use.
	 *
	 * @param string $asset_handle The asset handle to check.
	 *
	 * @return bool True if the asset handle is allowed, false otherwise.
	 */
	private function is_vite_asset( $asset_handle ) {
		if ( Str::contains( $asset_handle, [
			'bible-plugin',
			VITE_CLIENT_SCRIPT_HANDLE
		] ) ) {
			return true;
		}

		return false;
	}

	private function whitelist_vite() {
		global $wp_scripts;
		global $wp_styles;

		$scripts = [];
		$styles  = [];

		foreach ( $wp_scripts->registered as $script ) {
			if ( $this->is_vite_asset( $script->handle ) ) {
				$scripts[] = $script->handle;
			}
		}

		// phpcs:ignore
		add_filter( namespace_string( 'allowed_scripts' ),
			function ( $allowed ) use ( $scripts ) {
				return array_merge( $allowed, $scripts );
			}
		);

		foreach ( $wp_styles->registered as $style ) {
			if ( $this->is_vite_asset( $style->handle ) ) {
				$styles[] = $style->handle;
			}
		}

		add_filter( namespace_string( 'allowed_styles' ),
			function ( $allowed ) use ( $styles ) {
				return array_merge( $allowed, $styles );
			}
		);
	}
}
