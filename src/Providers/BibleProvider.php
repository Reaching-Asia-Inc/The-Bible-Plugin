<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\CodeZone\WPSupport\Options\Options;
use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\Resources\Bible;
use CodeZone\Bible\Resources\Book;
use CodeZone\Bible\Resources\Language;
use CodeZone\Bible\Services\FileSets;
use CodeZone\Bible\Services\MediaTypes;
use CodeZone\Bible\Services\References;
use CodeZone\Bible\Services\Scripture;
use CodeZone\Bible\Services\Video;
use CodeZone\Bible\Settings\LanguagesSetting;
use CodeZone\Bible\Sources\BibleBrains\Normalizers\BibleBrainsVideoNormalizer;

class BibleProvider extends AbstractServiceProvider
{
    public function provides( string $id ): bool
    {
        return in_array( $id, [ Video::class, Scripture::class ] );
    }

    public function register(): void
    {
        $this->container->add(Scripture::class, function () {
            return new Scripture(
                $this->container->get( Bible::class ),
                $this->container->get( Book::class ),
                $this->container->get( FileSets::class ),
                $this->container->get( References::class ),
                $this->container->get( MediaTypes::class ),
                $this->container->get( Language::class ),
                $this->container->get( LanguagesSetting::class ),
                $this->container->get( Options::class )
            );
        });

        $this->container->add(Video::class, function () {
            return new Video(
                $this->container->get( BibleBrainsVideoNormalizer::class ),
                $this->container->get( 'http.bibleBrains' ),
            );
        });
    }
}
