<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\Illuminate\Http\Client\Factory;
use CodeZone\Bible\Services\BibleBrains\BiblePluginSiteGuzzleMiddleware;
use CodeZone\Bible\Services\BibleBrains\GuzzleMiddleware;
use CodeZone\Bible\GuzzleHttp\Client;
use function CodeZone\Bible\container;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\GuzzleHttp\HandlerStack;

class BibleBrainsProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * Registers the middleware for the plugin.
     *
     * This method adds a filter to register middleware for the plugin.
     * The middleware is added to the stack in the order it is defined above.
     *
     * @return void
     */
    public function register(): void
    {
        $this->container->add('http.bibleBrains', function () {
            $stack = HandlerStack::create();
            $stack->push(container()->get(GuzzleMiddleware::class));

            return new Client([
                'handler' => $stack,
            ]);
        });

        $this->container->add('http.biblePluginSite', function () {
            $stack = HandlerStack::create();
            $stack->push(container()->get(BiblePluginSiteGuzzleMiddleware::class));

            return new Client([
                'handler' => $stack,
                'verify' => false,
            ]);
        });
    }

    public function provides(string $id): bool
    {
        return in_array($id, [
            'http.bibleBrains',
            'http.biblePluginSite'
        ]);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}
