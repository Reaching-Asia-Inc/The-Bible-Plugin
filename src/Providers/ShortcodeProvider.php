<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\ShortCodes\Bible;
use CodeZone\Bible\ShortCodes\Scripture;

/**
 * Class ShortcodeProvider
 *
 * @package Your\Namespace
 */
class ShortcodeProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    protected $shortcodes = [
        Bible::class,
        Scripture::class
    ];

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
        foreach ($this->shortcodes as $shortcode) {
            $this->container->add($shortcode);
            $this->container->get($shortcode);
        }
    }


    public function provides(string $id): bool
    {
        return in_array($id, $this->shortcodes);
    }
}
