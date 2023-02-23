<?php

declare(strict_types=1);

namespace BlackBonjourTest\ServiceManager\Asset;

use BlackBonjour\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Erick Dyck <info@erickdyck.de>
 * @since  18.09.2019
 */
class FooBarFactoryWithOptions implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, string $service, array|null $options = null)
    {
        return new FooBar($options['foo'], $options['bar']);
    }
}
