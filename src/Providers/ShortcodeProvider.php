<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\Services\Assets;
use CodeZone\Bible\Services\BibleBrains\Language;
use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use CodeZone\Bible\Services\Request;
use CodeZone\Bible\ShortCodes\Bible;
use CodeZone\Bible\ShortCodes\Scripture;

/**
 * Class ShortcodeProvider
 *
 * @package Your\Namespace
 */
class ShortcodeProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * Do any setup needed before the theme is ready.
     */
    public function register(): void
    {
    }

    /**
     * Do any setup after services have been registered and the theme is ready
     */
    public function boot(): void
    {

        $this->container->addShared(Bible::class, function () {
            return new Bible(
                $this->container->get( Assets::class ),
                $this->container->get( Request::class )
            );
        });
        $this->container->get( Bible::class );

        $this->container->addShared(Scripture::class, function () {
            return new Scripture(
                $this->container->get( \CodeZone\Bible\Services\BibleBrains\Scripture::class ),
                $this->container->get( Assets::class ),
                $this->container->get( MediaTypes::class ),
                $this->container->get( Language::class )
            );
        });
        $this->container->get( Scripture::class );
    }


    /**
     * Checks if the given identifier is provided by the current list of shortcodes.
     *
     * @param string $id The identifier to check.
     * @return bool Returns true if the identifier is provided, otherwise false.
     */
    public function provides( string $id ): bool
    {
        return in_array($id, [
            Bible::class,
            Scripture::class
        ]);
    }
}
