<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager;

use Psr\Container\ContainerInterface;

interface FactoryInterface
{
    /**
     * Creates a new service.
     *
     * @param ServiceManagerInterface       $container A container implementing PSR-11.
     * @param string                        $service   Name of the service to create a new instance of.
     * @param array<string|int, mixed>|null $options   Some options that can be passed to build the service.
     */
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null): mixed;
}
