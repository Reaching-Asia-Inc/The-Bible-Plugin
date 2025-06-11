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
     * Registers HTTP clients and their corresponding Guzzle middleware into the container.
     * This method configures and adds clients for 'http.bibleBrains' and 'http.biblePluginSite'
     * with appropriate handler stacks and verification settings.
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

    /**
     * Check if the service provider provides a specific service.

     * @param string $id The identifier of the service to check for.
     * @return bool Returns true if the service ID is provided, otherwise false.
     */
    public function provides(string $id): bool
    {
        return in_array($id, [
            'http.bibleBrains',
            'http.biblePluginSite'
        ]);
    }

    /**
     * Boots or initializes the necessary components or services for the application.
     * @return void This method does not return a value.
     */
    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}
