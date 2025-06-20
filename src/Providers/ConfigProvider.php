<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\CodeZone\WPSupport\Config\Config;
use CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface;
use CodeZone\Bible\League\Container\Exception\NotFoundException;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\Psr\Container\ContainerExceptionInterface;
use function CodeZone\Bible\config;
use function CodeZone\Bible\plugin_path;
use function CodeZone\Bible\set_config;

class ConfigProvider extends AbstractServiceProvider {
    public function __construct()
    {
        add_action( 'init', [ $this, 'init' ] );
    }

    public function init() {
        $this->hydrate_closure( 'assets.javascript_globals' );
    }

    public function hydrate_closure( $key, $default = [] ) {
        $config = $this->getContainer()->get( ConfigInterface::class );
        $closure = $config->get($key, function () use ( $default ) {
            return $default;
        });
        if ( !is_callable( $closure ) ) {
            return $default;
        }
        $result = $closure();
        $config->set( $key, $result );
    }

    /**
     * Determines whether the given identifier is provided by this service.
     *
     * @param string $id The identifier to check.
     * @return bool Returns true if the identifier is provided, otherwise false.
     */
    public function provides( string $id ): bool
    {
        return in_array( $id, [
            ConfigInterface::class,
        ]);
    }

    /**
     * Registers the necessary dependencies and loads configuration files.
     *
     * @return void
     */
    public function register(): void
    {
        $this->getContainer()->addShared(ConfigInterface::class, function () {
            return new Config();
        });

        $config = $this->getContainer()->get( ConfigInterface::class );
        foreach ( glob( plugin_path( 'config/*.php' ) ) as $filename )
        {
            require_once $filename;
        }
    }
}
