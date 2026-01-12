<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\Services\Analytics;
use CodeZone\Bible\Settings\GoogleTagSetting;

/**
 * Class AnalyticsProvider
 *
 * This provider handles the registration and booting of the Analytics service.
 */
class AnalyticsProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * Services provided by this provider.
     *
     * @param string $id
     * @return bool
     */
    public function provides( string $id ): bool
    {
        return in_array( $id, [
            Analytics::class,
            GoogleTagSetting::class
        ] );
    }

    /**
     * Boot the service.
     */
    public function boot(): void
    {
        // Force the service to be instantiated so it can register its wp_head hook
        $this->getContainer()->get( Analytics::class );
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $container = $this->getContainer();

        $container->addShared( GoogleTagSetting::class );

        $container->addShared( Analytics::class, function () use ( $container ) {
            return new Analytics( $container->get( GoogleTagSetting::class ) );
        } );
    }
}
