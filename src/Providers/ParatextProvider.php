<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\Settings\LocalBiblesSetting;
use CodeZone\Bible\Sources\Paratext\Paratext;
use CodeZone\Bible\Sources\Paratext\ParatextExtractor;
use CodeZone\Bible\Sources\Paratext\ParatextBibles;

/**
 *
 */
class ParatextProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * Registers Paratext services into the container.
     *
     * @return void
     */
    public function register(): void
    {
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
            ParatextBibles::class,
        ]);
    }

    /**
     * Boots or initializes the necessary components or services for the application.
     *
     * @return void This method does not return a value.
     */
    public function boot(): void
    {
        // Register the USXBiblesSetting service which is the main entry point for Paratext functionality
        $this->container->add(ParatextBibles::class, function () {
            return new ParatextBibles(
                $this->container->get( Paratext::class ),
                $this->container->get( LocalBiblesSetting::class ),
                $this->container->get( ParatextExtractor::class )
            );
        });
    }
}
