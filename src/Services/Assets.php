<?php

namespace CodeZone\Bible\Services;

use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use function CodeZone\Bible\api_url;
use function CodeZone\Bible\config;
use function CodeZone\Bible\Kucrut\Vite\enqueue_asset;
use function CodeZone\Bible\namespace_string;
use function CodeZone\Bible\plugin_path;

class Assets {
	private static $enqueued = false;
	private MediaTypes $media_types;

	public function __construct( MediaTypes $media_types ) {
		$this->media_types = $media_types;
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
	 * Enqueues scripts and styles for the frontend.
	 *
	 * This method enqueues the specified asset(s) for the frontend. It uses the "enqueue_asset" function to enqueue
	 * the asset(s) located in the provided plugin directory path with the given filename. The asset(s) can be JavaScript
	 * or CSS files. Optional parameters can be specified to customize the enqueue behavior.
	 *
	 * @return void
	 */
	public function wp_enqueue_scripts() {
        if ( wp_script_is( 'bible-plugin', 'enqueued' ) ) {
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

		wp_localize_script( 'bible-plugin', config( 'assets.javascript_global_scope' ), apply_filters( namespace_string( 'javascript_globals' ), [] ) );

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

		wp_localize_script( 'bible-plugin-admin', config( 'assets.javascript_global_scope' ), apply_filters( namespace_string( 'javascript_globals' ), [] ) );
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
}
