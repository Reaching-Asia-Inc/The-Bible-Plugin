<?php
/**
 * Plugin Name: The Bible Plugin
 * Plugin URI: https://github.com/Reaching-Asia-Inc/The-Bible-Plugin
 * Description: A bible plugin for WordPress.
 * Text Domain: bible-plugin
 * Domain Path: /languages
 * Version:  1.0.0-beta10
 * Author: Reaching Asia
 * Author URI: https://reachingasia.com
 * GitHub Plugin URI: https://github.com/Reaching-Asia-Inc/The-Bible-Plugin
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.6
 */

use CodeZone\Bible\Dotenv\Dotenv;
use CodeZone\Bible\Plugin;
use CodeZone\Bible\Providers\ConfigProvider;
use CodeZone\Bible\CodeZone\WPSupport\Container\ContainerFactory;
use CodeZone\Bible\Providers\PluginProvider;
use CodeZone\Bible\Services\ErrorHandler;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$boot_providers = [
    ConfigProvider::class,
    PluginProvider::class
];

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.env' ) ) {
    $dotenv = Dotenv::createImmutable( __DIR__ );
    $dotenv->load();
}

new ErrorHandler();

require_once plugin_dir_path( __FILE__ ) . 'includes/class-tgm-plugin-activation.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-scoped/scoper-autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor-scoped/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

$container = ContainerFactory::singleton();

require_once plugin_dir_path( __FILE__ ) . 'src/helpers.php';

$container->get( ErrorHandler::class );


// Add any services providers required to init the plugin

foreach ( $boot_providers as $provider ) {
    $container->addServiceProvider( $container->get( $provider ) );
}

// Init the plugin
$dt_plugin = $container->get( Plugin::class );
$dt_plugin->init();
