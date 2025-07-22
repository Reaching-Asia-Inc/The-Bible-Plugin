<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\Normalizers\BibleBrains\BibleBrainsVideoNormalizer;
use CodeZone\Bible\Services\Scripture;
use CodeZone\Bible\Services\Video;

class ResourceProvider extends AbstractServiceProvider
{

    public function provides( string $id ): bool
    {
        return in_array( $id, [ Video::class ], true );
    }

    public function register(): void
    {

    }
}
