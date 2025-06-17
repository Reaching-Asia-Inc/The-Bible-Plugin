<?php

namespace CodeZone\Bible;

use CodeZone\Bible\League\Container\Container;
use CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface as Config;

/**
 * Class Plugin
 *
 * The Plugin class represents a WordPress plugin. It manages the initialization of the plugin, its activation and deactivation hooks, and other related functionality.
 */
class Plugin {
    public Container $container;
    public static $instance;
    public Config $config;

	/**
	 * Plugin constructor.
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container, Config $config ) {
        $this->config = $config;
        $this->container = $container;
	}

	/**
	 * Get the instance of the plugin
	 * @return void
	 */
	public function init() {
		static::$instance = $this;

        foreach ( $this->config->get( 'services.providers' ) as $provider ) {
            $this->container->addServiceProvider( $this->container->get( $provider ) );
        }
	}

	/**
     * Get the directory path of the plugin.
     *
     * This method returns the absolute directory path of the plugin, excluding the "/src" directory
     *
     * @return string The directory path of the plugin.
     */
    public static function dir_path() {
        return '/' . trim( str_replace( '/src', '', plugin_dir_path( __FILE__ ) ), '/' );
    }
}
