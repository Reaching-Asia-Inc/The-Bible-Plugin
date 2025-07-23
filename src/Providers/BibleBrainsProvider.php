<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface as Options;
use CodeZone\Bible\Guzzle\BibleBrainsMiddleware;
use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\GuzzleHttp\HandlerStack;
use CodeZone\Bible\Illuminate\Http\Client\Factory;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\Services\BibleBrainsKeys;
use CodeZone\Bible\Services\Cache;
use CodeZone\Bible\Services\Video;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsApiKeys as ApiKeysApi;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsBibles as BiblesApi;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsLanguages as LanguagesApi;
use CodeZone\Bible\Sources\BibleBrains\Normalizers\BibleBrainsVideoNormalizer;
use function CodeZone\Bible\container;

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
        $this->container->addShared('http.bibleBrains', function () {
            $stack = HandlerStack::create();
            $stack->push( container()->get( BibleBrainsMiddleware::class ) );

            return new Client([
                'handler' => $stack,
            ]);
        });
        $this->container->add(BibleBrainsKeys::class, function () {
            return new BibleBrainsKeys(
                $this->container->get( Options::class ),
                new Client(),
                $this->container->get( Cache::class )
            );
        });
        $this->container->add(BiblesApi::class, function () {
            return new BiblesApi();
        });
        $this->container->add(LanguagesApi::class, function () {
            return new LanguagesApi();
        });
        $this->container->add(Video::class, function () {
            return new Video(
                $this->container->get( BibleBrainsVideoNormalizer::class ),
                $this->container->get( 'http.bibleBrains' ),
            );
        });
    }

    /**
     * Check if the service provider provides a specific service.

     * @param string $id The identifier of the service to check for.
     * @return bool Returns true if the service ID is provided, otherwise false.
     */
    public function provides( string $id ): bool
    {
        return in_array($id, [
            'http.bibleBrains',
            BibleBrainsKeys::class,
            ApiKeysApi::class,
            BiblesApi::class,
            LanguagesApi::class,
            Video::class,
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
