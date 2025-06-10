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

    public function views_path() {
        return views_path();
    }

    /**
     * Provide the services that this provider is responsible for.
     *
     * @param string $id The ID to zcheck.
     * @return bool Returns true if the given ID is provided, false otherwise.
     */
    public function provides( string $id ): bool
    {
        return in_array($id, [
            Engine::class
        ]);
    }

    /**
     * Register the view engine singleton and any extensions
     *
     * @return void
     */
    public function register(): void {
        $this->getContainer()->addShared( Engine::class, function () {
            return new Engine( $this->views_path() );
        } );
        $this->getContainer()->get( Engine::class );
    }
}
