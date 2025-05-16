<?php

declare(strict_types=1);

namespace BlackBonjour\ServiceManager\AbstractFactory;

use BlackBonjour\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

interface AbstractFactoryInterface extends FactoryInterface
{
    /**
     * Checks if the given service can be created by this abstract factory.
     */
    public function canCreate(ContainerInterface $container, string $service): bool;
}
