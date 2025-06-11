<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\CodeZone\WPSupport\Options\Options;
use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface;
use function CodeZone\Bible\config;

/**
 * Class OptionsServiceProvider
 *
 * This class is a service provider responsible for registering the Options class
 * into the container and providing the default options for the application.
 *
 * @package YourPackage
 */
class OptionsProvider extends AbstractServiceProvider {
    /**
     * Determines if the given identifier is provided.
     *
     * @param string $id The identifier to check.
     * @return bool Returns true if the identifier is provided, false otherwise.
     */
    public function provides( string $id ): bool
    {
        return in_array( $id, [
            OptionsInterface::class,
        ] );
    }


    /**
     * Registers the options service..
     *
     * @return void
     */
    public function register(): void
    {
        $this->container->add( OptionsInterface::class, function () {
            return new Options(
                config()->get( 'options.defaults' ),
                config()->get( 'options.prefix' )
            );
        } );
    }
}
