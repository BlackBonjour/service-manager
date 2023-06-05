<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

use BlackBonjour\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  18.09.2019
 */
class FooBarFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, string $service, ?array $options = null): FooBar
    {
        return new FooBar('foo', 'bar');
    }
}
