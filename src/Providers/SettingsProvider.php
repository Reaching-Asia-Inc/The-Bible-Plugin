<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\CodeZone\WPSupport\Options\Options;
use CodeZone\Bible\League\Container\Exception\NotFoundException;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\Psr\Container\ContainerExceptionInterface;
use CodeZone\Bible\Services\Locale;
use CodeZone\Bible\Settings\LanguagesSetting;
use CodeZone\Bible\Settings\Settings;
use function CodeZone\Bible\config;

/**
 * Class SettingsProvider
 *
 * This class is responsible for providing admin services and loading necessary plugins using the TGM Plugin Activation library.
 */
class SettingsProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
    public function __construct()
    {
        add_action( 'wp_loaded', [ $this, 'wp_loaded' ] );
    }

    /**
     * Provide the services that this provider is responsible for.
     *
     * @param string $id The ID to check.
     * @return bool Returns true if the given ID is provided, false otherwise.
     */
    public function provides( string $id ): bool
    {
        return in_array( $id, [
            Settings::class
        ] );
    }

    /**
     * Eager load the admin service
     *
     * @return void
     * @throws NotFoundException|ContainerExceptionInterface
     */
    public function boot(): void
    {
        $this->getContainer()->addShared( Settings::class, function () {
            return new Settings();
        } );
        $this->getContainer()->get( Settings::class );
    }

    /**
     * Register any services provided.
     *
     * This method is responsible for registering any services. It will be called
     * when the service is requested from the container.
     */
    public function register(): void
    {
        $this->getContainer()->add( LanguagesSetting::class, function() {
            return new LanguagesSetting(
                $this->getContainer()->get( Locale::class ),
                $this->getContainer()->get( Options::class ),
            );
        });
    }

    /**
     * Loads the necessary plugins using the TGM Plugin Activation library.
     *
     * This function should be called on the `wp_loaded` action hook
     * to ensure that all required plugins are properly loaded.
     *
     * @return void
     */
    public function wp_loaded(): void
    {
        tgmpa( config()->get( 'services.tgmpa.plugins' ), config()->get( 'services.tgmpa.config' ) );
    }
}
