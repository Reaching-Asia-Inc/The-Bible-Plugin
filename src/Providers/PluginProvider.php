<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface;
use CodeZone\Bible\League\Container\Exception\NotFoundException;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\Plugin;
use CodeZone\Bible\Psr\Container\ContainerExceptionInterface;

class PluginProvider extends AbstractServiceProvider {

    /**
     * Provide the services that this provider is responsible for.
     *
     * @param string $id The ID to check.
     * @return bool Returns true if the given ID is provided, false otherwise.
     */
    public function provides( string $id ): bool
    {
        return in_array( $id, [
            Plugin::class
        ]);
    }


    /**
     * Register the plugin and its service providers.
     *
     * @return void
     * @throws NotFoundException|ContainerExceptionInterface
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
