<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface;
use CodeZone\Bible\League\Container\Exception\NotFoundException;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\Plugin;
use CodeZone\Bible\Psr\Container\ContainerExceptionInterface;

class PluginProvider extends AbstractServiceProvider {

    /**
     * Determines if the given identifier is provided.
     *
     * @param string $id The identifier to check against the list of supported items.
     * @return bool Returns true if the identifier is supported, otherwise false.
     */
    public function provides( string $id ): bool
    {
        return in_array( $id, [
            Plugin::class
        ]);
    }


    /**
     * Registers the plugin class as a singleton.
     *
     * @return void
     */
    public function register(): void {
        $this->getContainer()->addShared( Plugin::class, function () {
            return new Plugin(
                $this->container,
                $this->container->get( ConfigInterface::class )
            );
        } );
    }
}
