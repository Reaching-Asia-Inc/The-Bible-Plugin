<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Plates\Engine;
use function CodeZone\Bible\views_path;

/**
 * Class ViewProvider
 *
 * This class is a service provider responsible for registering the view engine singleton and any extensions.
 *
 * @see https://platesphp.com/
 */
class ViewProvider extends AbstractServiceProvider {
    /**
     * Checks if the given ID is provided by the provider.
     *
     * @param string $id The identifier to check.
     * @return bool Returns true if the ID is provided, false otherwise.
     */
    public function provides( string $id ): bool
    {
        return in_array($id, [
            Engine::class
        ]);
    }

    /**
     * Registers the Engine class with the container as a shared instance.
     *
     * @return void
     */
    public function register(): void {
        $this->getContainer()->addShared( Engine::class, function () {
            return new Engine( views_path() );
        } );
        $this->getContainer()->get( Engine::class );
    }
}
