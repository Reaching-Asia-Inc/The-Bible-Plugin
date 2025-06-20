<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface as Options;
use CodeZone\Bible\Illuminate\Http\Client\Factory;
use CodeZone\Bible\Services\BibleBrains\Api\ApiKeys as ApiKeysApi;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles as BiblesApi;
use CodeZone\Bible\Services\BibleBrains\Api\Languages as LanguagesApi;
use CodeZone\Bible\Services\BibleBrains\BibleBrainsKeys;
use CodeZone\Bible\Services\BibleBrains\BiblePluginSiteGuzzleMiddleware;
use CodeZone\Bible\Services\BibleBrains\Books;
use CodeZone\Bible\Services\BibleBrains\FileSets;
use CodeZone\Bible\Services\BibleBrains\GuzzleMiddleware;
use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\Services\BibleBrains\Language;
use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use CodeZone\Bible\Services\BibleBrains\Reference;
use CodeZone\Bible\Services\BibleBrains\Scripture;
use CodeZone\Bible\Services\BibleBrains\Video;
use CodeZone\Bible\Services\Translations;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Container\ServiceProvider\BootableServiceProviderInterface;
use CodeZone\Bible\GuzzleHttp\HandlerStack;
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
        $this->container->add('http.bibleBrains', function () {
            $stack = HandlerStack::create();
            $stack->push( container()->get( GuzzleMiddleware::class ) );

            return new Client([
                'handler' => $stack,
            ]);
        });

        $this->container->add('http.biblePluginSite', function () {
            $stack = HandlerStack::create();
            $stack->push( container()->get( BiblePluginSiteGuzzleMiddleware::class ) );

            return new Client([
                'handler' => $stack,
                'verify' => false,
            ]);
        });

        $this->container->add(ApiKeysApi::class, function () {
            return new ApiKeysApi(
                $this->container->get( 'http.biblePluginSite' ),
            );
        });
        $this->container->add(BibleBrainsKeys::class, function () {
            return new BibleBrainsKeys(
                $this->container->get( Options::class ),
                $this->container->get( ApiKeysApi::class ),
            );
        });
        $this->container->add(BiblesApi::class, function () {
            return new BiblesApi(
                $this->container->get( 'http.bibleBrains' ),
            );
        });
        $this->container->add(LanguagesApi::class, function () {
            return new LanguagesApi(
                $this->container->get( 'http.bibleBrains' ),
            );
        });
        $this->container->add(Language::class, function () {
            return new Language(
                $this->container->get( Options::class ),
                $this->container->get( LanguagesApi::class ),
                $this->container->get( Translations::class )
            );
        });
        $this->container->add(Scripture::class, function () {
            return new Scripture(
                $this->container->get( BiblesApi::class ),
                $this->container->get( Books::class ),
                $this->container->get( FileSets::class ),
                $this->container->get( Reference::class ),
                $this->container->get( MediaTypes::class ),
                $this->container->get( Language::class ),
                $this->container->get( Options::class )
            );
        });
        $this->container->add(Video::class, function () {
            return new Video(
                $this->container->get( BiblesApi::class ),
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
            'http.biblePluginSite',
            BibleBrainsKeys::class,
            ApiKeysApi::class,
            BiblesApi::class,
            LanguagesApi::class,
            Language::class,
            Scripture::class,
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
