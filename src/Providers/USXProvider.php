<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\Sources\USX\USX;
use CodeZone\Bible\Sources\USX\USXBibles;

/**
 * Provider for USX services.
 *
 * This provider registers services related to parsing and retrieving data from USX files.
 * It bootstraps the USXBibles class which is responsible for managing USX Bible translations.
 */
class USXProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * Registers USX services into the container.
     *
     * @return void
     */
    public function register(): void
    {
        // Register the USX service as a shared service (not directly exposed)
        $this->container->addShared(USX::class, function () {
            return new USX();
        });

        // Register the USXBibles service which is the main entry point for USX functionality
        $this->container->add(USXBibles::class, function () {
            return new USXBibles( $this->container->get( USX::class ) );
        });
    }

    /**
     * Check if the service provider provides a specific service.
     *
     * @param string $id The identifier of the service to check for.
     * @return bool Returns true if the service ID is provided, otherwise false.
     */
    public function provides( string $id ): bool
    {
        return in_array($id, [
            'usx.service',
            USXBibles::class,
        ]);
    }

    /**
     * Boots or initializes the necessary components or services for the application.
     *
     * @return void This method does not return a value.
     */
    public function boot(): void
    {
        // No boot actions needed for now
    }
}
