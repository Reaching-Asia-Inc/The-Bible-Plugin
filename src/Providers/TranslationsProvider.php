<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\Gettext\Loader\PoLoader;
use CodeZone\Bible\Gettext\Translations as GetText;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\Services\Translations;
use function CodeZone\Bible\languages_path;

class TranslationsProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
    public function provides(string $id): bool
    {
        return in_array($id, [
            GetText::class,
            Translations::class
        ]);
    }

	/**
	 * Do any setup needed before the theme is ready.
	 */
	public function register(): void {

	}

	/**
	 * Do any setup after services have been registered and the theme is ready
	 */
	public function boot(): void {
		load_plugin_textdomain( 'bible-plugin', false, 'bible-plugin/languages' );

        $this->container->addShared( GetText::class, function ( $app ) {
            return $app->make( PoLoader::class )->loadFile( languages_path( 'bible-plugin-es_MX.po' ) );
        } );

        $this->container->addShared( Translations::class, function () {
            return new Translations();
        } );

        $this->container->get( Translations::class );
	}
}
