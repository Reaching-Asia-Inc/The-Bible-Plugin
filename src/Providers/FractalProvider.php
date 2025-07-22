<?php

namespace CodeZone\Bible\Providers;

use CodeZone\Bible\League\Container\ServiceProvider\AbstractServiceProvider;
use CodeZone\Bible\League\Fractal\Manager;
use CodeZone\Bible\League\Fractal\Serializer\ArraySerializer;

class FractalProvider extends AbstractServiceProvider
{

    public function provides( string $id ): bool
    {
        return $id === Manager::class;
    }

    public function register(): void
    {
        $this->container->add(
            Manager::class,
            function () {
                $manager = new Manager();
                $manager->setSerializer( new ArraySerializer() );
                return $manager;
            }
        );
    }
}
