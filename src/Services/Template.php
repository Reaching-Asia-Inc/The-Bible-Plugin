<?php

namespace CodeZone\Bible\Services;

use CodeZone\Bible\League\Plates\Extension\Asset;
use function CodeZone\Bible\container;
use function CodeZone\Bible\Kucrut\Vite\enqueue_asset;
use function CodeZone\Bible\plugin_path;
use function CodeZone\Bible\view;

class Template {
	public function __construct( protected Assets $assets ) {
	}

	/**
	 * Allow access to blank template
	 * @return bool
	 */
	public function blank_access(): bool {
		return true;
	}

	/**
	 * Start with a blank template
	 * @return void
	 */
	public function template_redirect(): void {
		$path = get_theme_file_path( 'template-blank.php' );
		include $path;
		die();
	}

	/**
	 * Render the header
	 * @return void
	 */
	public function header() {
		wp_head();
	}

	/**
	 * Render the template
	 *
	 * @param $template
	 * @param $data
	 *
	 * @return mixed
	 */
	public function render( $template, $data ) {
		add_action( 'template_redirect', [ $this, 'template_redirect' ] );
		add_filter( 'dt_blank_access', [ $this, 'blank_access' ] );
		add_action( 'dt_blank_head', [ $this, 'header' ] );
		add_action( 'dt_blank_footer', [ $this, 'footer' ] );
		$this->assets->enqueue();

		return view()->render( $template, $data );
	}

	/**
	 * Render the footer
	 * @return void
	 */
	public function footer() {
		wp_footer();
	}
}
