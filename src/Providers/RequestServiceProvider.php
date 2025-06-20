<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\Services\Request;
use CodeZone\Bible\Services\RequestInterface;
use CodeZone\Bible\Services\Validator;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Class RequestServiceProvider
 *
 * This service provider is responsible for registering request-related services
 * such as the RequestValidator.
 */
class RequestServiceProvider extends AbstractServiceProvider
{
    /**
     * Register any bindings the provider needs.
     *
     * @return void
     */
    public function register(): void
    {
        $this->container->addShared( Validator::class );
        $this->container->add(RequestInterface::class, function () {
            return new Request();
        });
    }

    /**
     * Determine if this provider provides a specific service.
     *
     * @param string $id The service identifier to check for
     * @return bool Returns true if this provider provides the service, false otherwise
     */
    public function provides( string $id ): bool
    {
        return in_array( $id, [ Validator::class, RequestInterface::class ] );
    }
}
