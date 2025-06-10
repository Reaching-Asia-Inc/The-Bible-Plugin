<?php

namespace CodeZone\Bible;

use CodeZone\Bible\CodeZone\Router\Middleware\Stack;
use CodeZone\Bible\Illuminate\Container\Container;
use CodeZone\Bible\Providers\PluginProvider;
use CodeZone\Bible\CodeZone\WPSupport\Config;

/**
 * This is the entry-object for the plugin.
 * Handle any setup and bootstrapping here.
 */
class Plugin {
	public const QUERY_VAR = 'bible-plugin';

	/**
	 * The route for the plugin's home page
	 * @var string
	 */
	public static $home_route = 'the-bible-plugin';

	/**
	 * The instance of the plugin
	 * @var Plugin
	 */
	public static Plugin $instance;

	/**
	 * The container
	 * @see https://laravel.com/docs/10.x/container
	 * @var Container
	 */
	public Container $container;

	/**
	 * The service provider
	 * @see https://laravel.com/docs/10.x/providers
	 * @var PluginProvider
	 */
	public PluginProvider $provider;

	/**
	 * Plugin constructor.
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container, Config $config ) {
		$this->container  = $container;
		self::$home_route = apply_filters( namespace_string( 'route' ), self::$home_route );
		$this->provider   = $container->make( PluginProvider::class );
	}

	/**
	 * Get the instance of the plugin
	 * @return void
	 */
	public function init() {
		static::$instance = $this;
		$this->provider->register();

		register_activation_hook( plugin_path( 'bible-plugin.php' ), [ $this, 'activation_hook' ] );
		register_deactivation_hook( plugin_path( 'bible-plugin.php' ), [ $this, 'deactivation_hook' ] );
		add_action( 'wp_loaded', [ $this, 'wp_loaded' ], 20 );
		add_action( 'init', [ $this, 'rewrite_rules' ] );
		add_action( 'query_vars', [ $this, 'query_vars' ] );
		add_action( 'template_redirect', [ $this, 'template_redirect' ] );
	}

	/**
	 * Activate the plugin.
	 *
	 * This method is a hook that is triggered when the plugin is activated.
	 * It calls the `rewrite_rules()` method to add or modify rewrite rules
	 * and then flushes the rewrite rules to update them.
	 */
	public function activation_hook() {
		$this->rewrite_rules();
		flush_rewrite_rules();
	}

	/**
	 * Flush rewrite rules after deactivating the plugin.
	 *
	 * @return void
	 */
	public function deactivation_hook() {
		flush_rewrite_rules();
	}

	/**
	 * Runs after wp_loaded
	 * @return void
	 */
	public function wp_loaded(): void {
		$this->provider->boot();
	}

	/**
	 * Rewrite rules method.
	 *
	 * This method is responsible for adding any custom rewrite rules to the plugin.
	 * We'll use this method to add a custom rewrite rule for the all routes prefixed
	 * with the plugin's home route. Subsequent routes will be handled by the plugin's
	 * router.
	 *
	 * @return void
	 */
	public function rewrite_rules(): void {
		add_rewrite_rule(
			'^' . self::$home_route . '/?$',
			'index.php?' . self::QUERY_VAR . '=/', 'top'
		);
		add_rewrite_rule(
			'^' . self::$home_route . '/(.+)/?',
			'index.php?' . self::QUERY_VAR . '=$matches[1]', 'top'
		);
	}

	/**
	 * Add query vars
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function query_vars( array $vars ): array {
		$vars[] = self::QUERY_VAR;

		return $vars;
	}

	/**
	 * Perform template redirect
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function template_redirect(): void {
		if ( ! get_query_var( self::QUERY_VAR ) ) {
			return;
		}

		$response = apply_filters( namespace_string( 'middleware' ), $this->container->make( Stack::class ) )
			->run();

		if ( ! $response ) {
			wp_die( esc_attr( __( "The page could not be found.", 'dt-plugin' ) ), 404 );
		}

		if ( ! $response->isSuccessful() ) {
			wp_die( esc_attr( $response->statusText() ), esc_attr( $response->getStatusCode() ) );
		}

		$path = get_theme_file_path( 'template-blank.php' );
		include $path;

		die();
	}
}
