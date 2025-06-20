<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\Gettext\Loader\PoLoader;
use CodeZone\Bible\Gettext\Translations as GetText;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\Services\Translations;
use function CodeZone\Bible\languages_path;

class TranslationsProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
    /**
     * Checks if the given service identifier is provided.
     *
     * @param string $id The service identifier to check.
     * @return bool Returns true if the service identifier is provided, false otherwise.
     */
    public function provides( string $id ): bool
    {
        return in_array($id, [
            GetText::class,
            Translations::class,
        ]);
    }


	/**
	 * Do any setup needed before the theme is ready.
	 */
	public function register(): void {
	}

    /**
     * Bootstrap the plugin translations and GetText service.
     *
     * @return void
     */
	public function boot(): void {

        $this->container->addShared( GetText::class, function () {
            return $this->container->get( PoLoader::class )->loadFile( languages_path( 'bible-plugin-es_MX.po' ) );
        } );

        $this->container->addShared( Translations::class, function () {
            return new Translations();
        } );

        $this->container->get( Translations::class );
	}
}
