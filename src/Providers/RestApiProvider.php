<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\Services\RestApi;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class RestApiProvider
 *
 * This service provider is responsible for registering and bootstrapping
 * the REST API functionality for the Bible plugin.
 */
class RestApiProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * Boots the application services by registering and retrieving a shared instance of the RestApi class.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->container->addShared( RestApi::class );
        $this->container->get( RestApi::class );
    }

    /**
     * Register any bindings the provider needs.
     *
     * Currently no additional bindings are required since the REST API
     * service is registered in the boot method.
     *
     * @return void
     */
    public function register(): void
    {
        // No bindings required here currently
    }

    /**
     * Determine if this provider provides a specific service.
     *
     * @param string $id The service identifier to check for
     * @return bool Returns true if this provider provides the service, false otherwise
     */
    public function provides( string $id ): bool
    {
        return $id === RestApi::class;
    }
}
