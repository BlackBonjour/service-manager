<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

use BlackBonjour\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

final class FooBarFactoryWithOptions implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null): FooBar
    {
        return new FooBar($options['foo'], $options['bar']);
    }
}
