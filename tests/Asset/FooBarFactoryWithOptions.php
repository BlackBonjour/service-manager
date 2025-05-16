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
        /** @phpstan-ignore-next-line */
        return new FooBar($options['foo'], $options['bar']);
    }
}
